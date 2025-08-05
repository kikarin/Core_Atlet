<?php

namespace App\Http\Controllers;

use App\Http\Requests\TurnamenRequest;
use App\Repositories\TurnamenRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\TenagaPendukung;

class TurnamenController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    private $request;

    public function __construct(Request $request, TurnamenRepository $repository)
    {
        $this->repository = $repository;
        $this->request    = TurnamenRequest::createFromBase($request);
        $this->initialize();
        $this->route                          = 'turnamen';
        $this->commonData['kode_first_menu']  = 'TURNAMEN';
        $this->commonData['kode_second_menu'] = 'TURNAMEN';
    }

    public static function middleware(): array
    {
        $className  = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));

        return [
            new Middleware("can:$permission Add", only: ['create', 'store']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);

        return response()->json([
            'data' => $data['turnamens'],
            'meta' => [
                'total'        => $data['total'],
                'current_page' => $data['currentPage'],
                'per_page'     => $data['perPage'],
                'search'       => $data['search'],
                'sort'         => $data['sort'],
                'order'        => $data['order'],
            ],
        ]);
    }

    public function index()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customIndex($data);

        return inertia('modules/turnamen/Index', $data);
    }

    public function store(TurnamenRequest $request)
    {
        $data     = $this->repository->validateRequest($request);
        $turnamen = $this->repository->create($data);

        // Sync peserta jika ada
        if ($request->has('peserta_data')) {
            $this->repository->syncPeserta($turnamen->id, $request->peserta_data);
        }

        return redirect()->route('turnamen.index')->with('success', 'Data turnamen berhasil ditambahkan!');
    }

    public function update(TurnamenRequest $request, $id)
    {
        $data = $this->repository->validateRequest($request);
        $this->repository->update($id, $data);

        // Sync peserta jika ada
        if ($request->has('peserta_data')) {
            $this->repository->syncPeserta($id, $request->peserta_data);
        }

        return redirect()->route('turnamen.index')->with('success', 'Data turnamen berhasil diperbarui!');
    }

    public function show($id)
    {
        $item = $this->repository->getDetailWithUserTrack($id);
        if (!$item) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        $itemArray = $item->toArray();

        return Inertia::render('modules/turnamen/Show', [
            'item' => $itemArray,
        ]);
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        return redirect()->route('turnamen.index')->with('success', 'Data turnamen berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric|exists:turnamen,id',
        ]);
        $this->repository->delete_selected($request->ids);

        return response()->json(['message' => 'Data turnamen berhasil dihapus!']);
    }

    public function create()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'item' => null,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data);
        if (! is_array($data)) {
            return $data;
        }

        return inertia('modules/turnamen/Create', $data);
    }

    public function edit($id = '')
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getDetailWithUserTrack($id);
        if (!$item) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        $data = $this->commonData + [
            'item' => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data, $item);
        if (! is_array($data)) {
            return $data;
        }

        return inertia('modules/turnamen/Edit', $data);
    }

    public function apiPesertaByCaborKategori(Request $request)
    {
        $caborKategoriId = $request->get('cabor_kategori_id');
        $jenisPeserta    = $request->get('jenis_peserta', 'atlet');

        if (!$caborKategoriId) {
            return response()->json(['data' => [], 'meta' => ['total' => 0]]);
        }

        $perPage = (int) $request->get('per_page', 10);
        $page    = (int) $request->get('page', 1);
        $search  = $request->get('search', '');

        // Use raw SQL queries like RencanaLatihanPesertaController
        if ($jenisPeserta === 'atlet') {
            $query = Atlet::query()
                ->leftJoin('cabor_kategori_atlet', function ($join) use ($caborKategoriId) {
                    $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                        ->where('cabor_kategori_atlet.cabor_kategori_id', $caborKategoriId)
                        ->where('cabor_kategori_atlet.is_active', 1)
                        ->whereNull('cabor_kategori_atlet.deleted_at');
                })
                ->leftJoin('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
                ->select(
                    'atlets.id',
                    'atlets.nama',
                    'atlets.foto',
                    'atlets.jenis_kelamin',
                    'atlets.tempat_lahir',
                    'atlets.tanggal_lahir',
                    'atlets.tanggal_bergabung',
                    'atlets.no_hp',
                    'cabor_kategori_atlet.is_active as kategori_is_active',
                    'mst_posisi_atlet.nama as posisi_atlet_nama'
                )
                ->whereNotNull('cabor_kategori_atlet.id');
        } elseif ($jenisPeserta === 'pelatih') {
            $query = Pelatih::query()
                ->leftJoin('cabor_kategori_pelatih', function ($join) use ($caborKategoriId) {
                    $join->on('pelatihs.id', '=', 'cabor_kategori_pelatih.pelatih_id')
                        ->where('cabor_kategori_pelatih.cabor_kategori_id', $caborKategoriId)
                        ->where('cabor_kategori_pelatih.is_active', 1)
                        ->whereNull('cabor_kategori_pelatih.deleted_at');
                })
                ->leftJoin('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
                ->select(
                    'pelatihs.id',
                    'pelatihs.nama',
                    'pelatihs.foto',
                    'pelatihs.jenis_kelamin',
                    'pelatihs.tempat_lahir',
                    'pelatihs.tanggal_lahir',
                    'pelatihs.tanggal_bergabung',
                    'pelatihs.no_hp',
                    'cabor_kategori_pelatih.is_active as kategori_is_active',
                    'mst_jenis_pelatih.nama as jenis_pelatih_nama'
                )
                ->whereNotNull('cabor_kategori_pelatih.id');
        } elseif ($jenisPeserta === 'tenaga-pendukung') {
            $query = TenagaPendukung::query()
                ->leftJoin('cabor_kategori_tenaga_pendukung', function ($join) use ($caborKategoriId) {
                    $join->on('tenaga_pendukungs.id', '=', 'cabor_kategori_tenaga_pendukung.tenaga_pendukung_id')
                        ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $caborKategoriId)
                        ->where('cabor_kategori_tenaga_pendukung.is_active', 1)
                        ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at');
                })
                ->leftJoin('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
                ->select(
                    'tenaga_pendukungs.id',
                    'tenaga_pendukungs.nama',
                    'tenaga_pendukungs.foto',
                    'tenaga_pendukungs.jenis_kelamin',
                    'tenaga_pendukungs.tempat_lahir',
                    'tenaga_pendukungs.tanggal_lahir',
                    'tenaga_pendukungs.tanggal_bergabung',
                    'tenaga_pendukungs.no_hp',
                    'cabor_kategori_tenaga_pendukung.is_active as kategori_is_active',
                    'mst_jenis_tenaga_pendukung.nama as jenis_tenaga_pendukung_nama'
                )
                ->whereNotNull('cabor_kategori_tenaga_pendukung.id');
        } else {
            return response()->json(['data' => [], 'meta' => ['total' => 0]]);
        }

        if ($search) {
            $tableName = $jenisPeserta === 'atlet' ? 'atlets' : ($jenisPeserta === 'pelatih' ? 'pelatihs' : 'tenaga_pendukungs');
            $query->where("$tableName.nama", 'like', "%$search%");
        }

        $result = $query->paginate($perPage)->appends($request->all());

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'total'        => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page'     => $result->perPage(),
                'search'       => $search,
                'sort'         => $request->input('sort', ''),
                'order'        => $request->input('order', 'asc'),
            ],
        ]);
    }

    // Method untuk menampilkan halaman peserta turnamen
    public function pesertaIndex($turnamenId, Request $request)
    {
        $turnamen = $this->repository->getDetailWithUserTrack($turnamenId);
        if (!$turnamen) {
            return redirect()->back()->with('error', 'Turnamen tidak ditemukan');
        }

        $jenisPeserta = $request->get('jenis_peserta', 'atlet');

        $data = $this->commonData + [
            'turnamen'      => $turnamen,
            'turnamen_id'   => $turnamenId,
            'jenis_peserta' => $jenisPeserta,
        ];

        return inertia('modules/turnamen/peserta/Index', $data);
    }

    // API untuk mendapatkan peserta turnamen
    public function apiPesertaTurnamen($turnamenId, Request $request)
    {
        $turnamen = $this->repository->getDetailWithUserTrack($turnamenId);
        if (!$turnamen) {
            return response()->json(['data' => [], 'meta' => ['total' => 0]]);
        }

        $jenisPeserta = $request->get('jenis_peserta', 'atlet');
        $perPage      = (int) $request->get('per_page', 10);
        $page         = (int) $request->get('page', 1);
        $search       = $request->get('search', '');

        // Use raw SQL queries to get correct data from pivot tables
        if ($jenisPeserta === 'atlet') {
            $query = Atlet::query()
                ->join('turnamen_peserta', function ($join) use ($turnamenId) {
                    $join->on('atlets.id', '=', 'turnamen_peserta.peserta_id')
                        ->where('turnamen_peserta.turnamen_id', $turnamenId)
                        ->where('turnamen_peserta.peserta_type', 'App\\Models\\Atlet');
                })
                ->leftJoin('cabor_kategori_atlet', function ($join) use ($turnamen) {
                    $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                        ->where('cabor_kategori_atlet.cabor_kategori_id', $turnamen->cabor_kategori_id)
                        ->whereNull('cabor_kategori_atlet.deleted_at');
                })
                ->leftJoin('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
                ->select(
                    'atlets.id',
                    'atlets.nama',
                    'atlets.foto',
                    'atlets.jenis_kelamin',
                    'atlets.tempat_lahir',
                    'atlets.tanggal_lahir',
                    'atlets.tanggal_bergabung',
                    'atlets.no_hp',
                    'mst_posisi_atlet.nama as posisi_atlet_nama'
                );
        } elseif ($jenisPeserta === 'pelatih') {
            $query = Pelatih::query()
                ->join('turnamen_peserta', function ($join) use ($turnamenId) {
                    $join->on('pelatihs.id', '=', 'turnamen_peserta.peserta_id')
                        ->where('turnamen_peserta.turnamen_id', $turnamenId)
                        ->where('turnamen_peserta.peserta_type', 'App\\Models\\Pelatih');
                })
                ->leftJoin('cabor_kategori_pelatih', function ($join) use ($turnamen) {
                    $join->on('pelatihs.id', '=', 'cabor_kategori_pelatih.pelatih_id')
                        ->where('cabor_kategori_pelatih.cabor_kategori_id', $turnamen->cabor_kategori_id)
                        ->whereNull('cabor_kategori_pelatih.deleted_at');
                })
                ->leftJoin('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
                ->select(
                    'pelatihs.id',
                    'pelatihs.nama',
                    'pelatihs.foto',
                    'pelatihs.jenis_kelamin',
                    'pelatihs.tempat_lahir',
                    'pelatihs.tanggal_lahir',
                    'pelatihs.tanggal_bergabung',
                    'pelatihs.no_hp',
                    'mst_jenis_pelatih.nama as jenis_pelatih_nama'
                );
        } elseif ($jenisPeserta === 'tenaga-pendukung') {
            $query = TenagaPendukung::query()
                ->join('turnamen_peserta', function ($join) use ($turnamenId) {
                    $join->on('tenaga_pendukungs.id', '=', 'turnamen_peserta.peserta_id')
                        ->where('turnamen_peserta.turnamen_id', $turnamenId)
                        ->where('turnamen_peserta.peserta_type', 'App\\Models\\TenagaPendukung');
                })
                ->leftJoin('cabor_kategori_tenaga_pendukung', function ($join) use ($turnamen) {
                    $join->on('tenaga_pendukungs.id', '=', 'cabor_kategori_tenaga_pendukung.tenaga_pendukung_id')
                        ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $turnamen->cabor_kategori_id)
                        ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at');
                })
                ->leftJoin('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
                ->select(
                    'tenaga_pendukungs.id',
                    'tenaga_pendukungs.nama',
                    'tenaga_pendukungs.foto',
                    'tenaga_pendukungs.jenis_kelamin',
                    'tenaga_pendukungs.tempat_lahir',
                    'tenaga_pendukungs.tanggal_lahir',
                    'tenaga_pendukungs.tanggal_bergabung',
                    'tenaga_pendukungs.no_hp',
                    'mst_jenis_tenaga_pendukung.nama as jenis_tenaga_pendukung_nama'
                );
        } else {
            return response()->json(['data' => [], 'meta' => ['total' => 0]]);
        }

        if ($search) {
            $tableName = $jenisPeserta === 'atlet' ? 'atlets' : ($jenisPeserta === 'pelatih' ? 'pelatihs' : 'tenaga_pendukungs');
            $query->where("$tableName.nama", 'like', "%$search%");
        }

        $result = $query->paginate($perPage)->appends($request->all());

        return response()->json([
            'data' => $result->items(),
            'meta' => [
                'total'        => $result->total(),
                'current_page' => $result->currentPage(),
                'per_page'     => $result->perPage(),
                'search'       => $search,
                'sort'         => $request->input('sort', ''),
                'order'        => $request->input('order', 'asc'),
            ],
        ]);
    }

    // Method untuk menghapus peserta dari turnamen
    public function destroyPeserta($turnamenId, $jenisPeserta, $pesertaId)
    {
        $turnamen = $this->repository->getDetailWithUserTrack($turnamenId);
        if (!$turnamen) {
            return response()->json(['message' => 'Turnamen tidak ditemukan'], 404);
        }

        switch ($jenisPeserta) {
            case 'atlet':
                $turnamen->peserta()->detach($pesertaId);
                break;
            case 'pelatih':
                $turnamen->pelatihPeserta()->detach($pesertaId);
                break;
            case 'tenaga-pendukung':
                $turnamen->tenagaPendukungPeserta()->detach($pesertaId);
                break;
            default:
                return response()->json(['message' => 'Jenis peserta tidak valid'], 400);
        }

        return response()->json(['message' => 'Peserta berhasil dihapus dari turnamen']);
    }

    // Method untuk menghapus multiple peserta dari turnamen
    public function destroySelectedPeserta($turnamenId, $jenisPeserta, Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric',
        ]);

        $turnamen = $this->repository->getDetailWithUserTrack($turnamenId);
        if (!$turnamen) {
            return response()->json(['message' => 'Turnamen tidak ditemukan'], 404);
        }

        switch ($jenisPeserta) {
            case 'atlet':
                $turnamen->peserta()->detach($request->ids);
                break;
            case 'pelatih':
                $turnamen->pelatihPeserta()->detach($request->ids);
                break;
            case 'tenaga-pendukung':
                $turnamen->tenagaPendukungPeserta()->detach($request->ids);
                break;
            default:
                return response()->json(['message' => 'Jenis peserta tidak valid'], 400);
        }

        return response()->json(['message' => 'Peserta berhasil dihapus dari turnamen']);
    }
}
