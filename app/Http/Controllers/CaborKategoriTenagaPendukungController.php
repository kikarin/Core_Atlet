<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaborKategoriTenagaPendukungRequest;
use App\Repositories\CaborKategoriTenagaPendukungRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use App\Models\CaborKategori;

class CaborKategoriTenagaPendukungController extends Controller implements HasMiddleware
{
    use BaseTrait;
    private $repository;
    private $request;

    public function __construct(Request $request, CaborKategoriTenagaPendukungRepository $repository)
    {
        $this->repository = $repository;
        $this->request    = $request;
        $this->initialize();
        $this->route                          = 'cabor-kategori-tenaga-pendukung';
        $this->commonData['kode_first_menu']  = 'CABOR';
        $this->commonData['kode_second_menu'] = $this->kode_menu;
    }

    public static function middleware(): array
    {
        $className  = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));
        return [
            new Middleware("can:$permission Add", only: ['create', 'store', 'storeMultiple']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function index()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'titlePage' => 'Cabor Kategori Tenaga Pendukung',
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        return inertia('modules/cabor-kategori-tenaga-pendukung/Index', $data);
    }

    public function create()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'titlePage' => 'Tambah Cabor Kategori Tenaga Pendukung',
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data);
        return inertia('modules/cabor-kategori-tenaga-pendukung/Create', $data);
    }

    public function store(CaborKategoriTenagaPendukungRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        $this->repository->batchInsert($data);
        return redirect()->route('cabor-kategori-tenaga-pendukung.index')->with('success', 'Tenaga Pendukung berhasil ditambahkan ke kategori!');
    }

    public function show($id)
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = $this->commonData + [
            'titlePage' => 'Detail Cabor Kategori Tenaga Pendukung',
            'item'      => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customShow($data, $item);
        return inertia('modules/cabor-kategori-tenaga-pendukung/Show', $data);
    }

    public function edit($id)
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = $this->commonData + [
            'titlePage' => 'Edit Cabor Kategori Tenaga Pendukung',
            'item'      => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data, $item);
        return inertia('modules/cabor-kategori-tenaga-pendukung/Edit', $data);
    }

    public function update(CaborKategoriTenagaPendukungRequest $request, $id)
    {
        $data = $this->repository->validateRequest($request);
        $this->repository->update($id, $data);
        return redirect()->route('cabor-kategori-tenaga-pendukung.index')->with('success', 'Cabor Kategori Tenaga Pendukung berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Data berhasil dihapus']);
        }
        return redirect()->route('cabor-kategori-tenaga-pendukung.index')->with('success', 'Cabor Kategori Tenaga Pendukung berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih data yang akan dihapus!');
        }
        $this->repository->delete_selected($ids);
        return redirect()->route('cabor-kategori-tenaga-pendukung.index')->with('success', 'Data berhasil dihapus!');
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);
        return response()->json([
            'data' => $data['records'],
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

    // Untuk halaman daftar tenaga pendukung per kategori
    public function tenagaPendukungByKategori($caborKategoriId)
    {
        $this->repository->customProperty(__FUNCTION__, ['cabor_kategori_id' => $caborKategoriId]);
        $caborKategori = app(CaborKategori::class)->with('cabor')->find($caborKategoriId);
        if (!$caborKategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
        }
        $data = $this->commonData + [
            'titlePage'     => 'Daftar Tenaga Pendukung - ' . $caborKategori->nama,
            'caborKategori' => $caborKategori,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        return inertia('modules/cabor-kategori-tenaga-pendukung/TenagaPendukungByKategori', $data);
    }

    // Untuk halaman tambah multiple tenaga pendukung
    public function createMultiple($caborKategoriId)
    {
        $this->repository->customProperty(__FUNCTION__, ['cabor_kategori_id' => $caborKategoriId]);
        $caborKategori = app(CaborKategori::class)->with('cabor')->find($caborKategoriId);
        if (!$caborKategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
        }
        $data = $this->commonData + [
            'titlePage'     => 'Tambah Multiple Tenaga Pendukung - ' . $caborKategori->nama,
            'caborKategori' => $caborKategori,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        return inertia('modules/cabor-kategori-tenaga-pendukung/CreateMultiple', $data);
    }

    // Untuk store multiple tenaga pendukung
    public function storeMultiple(Request $request, $caborKategoriId)
    {
        try {
            Log::info('storeMultiple called', [
                'caborKategoriId' => $caborKategoriId,
                'request_data'    => $request->all(),
            ]);

            $caborKategori = app(CaborKategori::class)->find($caborKategoriId);
            if (!$caborKategori) {
                return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
            }

            // Merge ke request sebelum validasi
            $request->merge([
                'cabor_kategori_id' => $caborKategoriId,
                'cabor_id'          => $caborKategori->cabor_id,
            ]);

            $validatedData = $this->repository->validateRequest($request);

            // Pastikan is_active selalu integer (1/0)
            $validatedData['is_active'] = (int) $request->input('is_active', 1);
            Log::info('Validated data', $validatedData);

            $this->repository->batchInsert($validatedData);

            Log::info('Batch insert successful');

            return redirect()->route('cabor-kategori-tenaga-pendukung.tenaga-pendukung-by-kategori', $caborKategoriId)
                ->with('success', 'Tenaga Pendukung berhasil ditambahkan ke kategori!');
        } catch (\Exception $e) {
            Log::error('Error in storeMultiple', [
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return redirect()->back()->with('error', 'Gagal menambahkan tenaga pendukung: ' . $e->getMessage());
        }
    }
}
