<?php

namespace App\Repositories;

use App\Http\Requests\TurnamenRequest;
use App\Models\Turnamen;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TurnamenRepository
{
    use RepositoryTrait;

    protected $model;

    protected $request;

    public function __construct(Turnamen $model)
    {
        $this->model   = $model;
        $this->request = TurnamenRequest::createFromBase(request());
        $this->with    = ['created_by_user', 'updated_by_user', 'caborKategori', 'tingkat', 'juara'];
    }

    public function customIndex($data)
    {
        $query = $this->model->select('id', 'nama', 'cabor_kategori_id', 'tanggal_mulai', 'tanggal_selesai', 'tingkat_id', 'lokasi', 'juara_id', 'hasil', 'evaluasi');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                  ->orWhere('lokasi', 'like', '%'.$search.'%')
                  ->orWhere('hasil', 'like', '%'.$search.'%');
            });
        }

        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'created_at', 'updated_at'];
            if (in_array($sortField, $validColumns)) {
                $query->orderBy($sortField, $order);
            } else {
                $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

                $auth = Auth::user();
        if ($auth->current_role_id == 35) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriAtlet", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("atlet_id", $auth->atlet->id);
                });
            });
        }

        $auth = Auth::user();
        if ($auth->current_role_id == 36) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriPelatih", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("pelatih_id", $auth->pelatih->id);
                });
            });
        }

        $auth = Auth::user();
        if ($auth->current_role_id == 37) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("tenagaPendukung", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("tenaga_pendukung_id", $auth->tenagaPendukung->id);
                });
            });
        }


        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);

        if ($perPage === -1) {
            $allData         = $query->get();
            $transformedData = $allData->map(function ($item) {
                $pesertaCounts = $this->getPesertaCount($item->id);
                return [
                    'id'                  => $item->id,
                    'nama'                => $item->nama,
                    'cabor_kategori_id'   => $item->cabor_kategori_id,
                    'cabor_kategori_nama' => $item->caborKategori ? $item->caborKategori->cabor->nama . ' - ' . $item->caborKategori->nama : '-',
                    'tanggal_mulai'       => $item->tanggal_mulai,
                    'tanggal_selesai'     => $item->tanggal_selesai,
                    'tingkat_id'          => $item->tingkat_id,
                    'tingkat_nama'        => $item->tingkat ? $item->tingkat->nama : '-',
                    'lokasi'              => $item->lokasi,
                    'juara_id'            => $item->juara_id,
                    'juara_nama'          => $item->juara ? $item->juara->nama : '-',
                    'hasil'               => $item->hasil,
                    'evaluasi'            => $item->evaluasi,
                    'peserta_counts'      => $pesertaCounts,
                ];
            });
            $data += [
                'turnamens'     => $transformedData,
                'total'         => $transformedData->count(),
                'currentPage'   => 1,
                'perPage'       => -1,
                'search'        => request('search', ''),
                'sort'          => request('sort', ''),
                'order'         => request('order', 'asc'),
            ];

            return $data;
        }

        $pageForPaginate = $page < 1 ? 1 : $page;
        $items           = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();

        $transformedData = collect($items->items())->map(function ($item) {
            $pesertaCounts = $this->getPesertaCount($item->id);
            return [
                'id'                  => $item->id,
                'nama'                => $item->nama,
                'cabor_kategori_id'   => $item->cabor_kategori_id,
                'cabor_kategori_nama' => $item->caborKategori ? $item->caborKategori->cabor->nama . ' - ' . $item->caborKategori->nama : '-',
                'tanggal_mulai'       => $item->tanggal_mulai,
                'tanggal_selesai'     => $item->tanggal_selesai,
                'tingkat_id'          => $item->tingkat_id,
                'tingkat_nama'        => $item->tingkat ? $item->tingkat->nama : '-',
                'lokasi'              => $item->lokasi,
                'juara_id'            => $item->juara_id,
                'juara_nama'          => $item->juara ? $item->juara->nama : '-',
                'hasil'               => $item->hasil,
                'evaluasi'            => $item->evaluasi,
                'peserta_counts'      => $pesertaCounts,
            ];
        });

        $data += [
            'turnamens'     => $transformedData,
            'total'         => $items->total(),
            'currentPage'   => $items->currentPage(),
            'perPage'       => $items->perPage(),
            'search'        => request('search', ''),
            'sort'          => request('sort', ''),
            'order'         => request('order', 'asc'),
        ];

        return $data;
    }

    public function customDataCreateUpdate($data, $record = null)
    {
        $userId = Auth::id();

        if (is_null($record)) {
            $data['created_by'] = $userId;
        }
        $data['updated_by'] = $userId;

        return $data;
    }

    public function delete_selected(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getDetailWithUserTrack($id)
    {
        return $this->model
            ->with(['created_by_user', 'updated_by_user', 'caborKategori.cabor', 'tingkat', 'juara'])
            ->where('id', $id)
            ->first();
    }

    public function handleShow($id)
    {
        $item = $this->getDetailWithUserTrack($id);

        if (! $item) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $itemArray = $item->toArray();

        return Inertia::render('modules/turnamen/Show', [
            'item' => $itemArray,
        ]);
    }

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function syncPeserta($turnamenId, $pesertaData)
    {
        $turnamen = $this->model->find($turnamenId);

        if (!$turnamen) {
            return false;
        }

        if (isset($pesertaData['atlet_ids'])) {
            $turnamen->peserta()->sync($pesertaData['atlet_ids']);
        }

        if (isset($pesertaData['pelatih_ids'])) {
            $turnamen->pelatihPeserta()->sync($pesertaData['pelatih_ids']);
        }

        if (isset($pesertaData['tenaga_pendukung_ids'])) {
            $turnamen->tenagaPendukungPeserta()->sync($pesertaData['tenaga_pendukung_ids']);
        }

        return true;
    }

    public function getPesertaCount($turnamenId)
    {
        $turnamen = $this->model->with(['peserta', 'pelatihPeserta', 'tenagaPendukungPeserta'])->find($turnamenId);

        if (!$turnamen) {
            return ['atlet' => 0, 'pelatih' => 0, 'tenaga_pendukung' => 0];
        }

        return [
            'atlet'            => $turnamen->peserta->count(),
            'pelatih'          => $turnamen->pelatihPeserta->count(),
            'tenaga_pendukung' => $turnamen->tenagaPendukungPeserta->count(),
        ];
    }

    /**
     * Get data for mobile API with search and filters
     */
    public function getForMobile($request)
    {
        $query = $this->model->with(['caborKategori.cabor', 'tingkat', 'juara']);

        // Apply filters
        $this->applyMobileFilters($query, $request);

        // Apply role-based filtering
        $this->applyRoleFilter($query);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('hasil', 'like', "%{$search}%")
                  ->orWhereHas('caborKategori.cabor', function ($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tingkat', function ($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $query->orderBy('tanggal_mulai', 'desc');

        // Pagination
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        if ($perPage === -1) {
            $all = $query->get();
            $transformed = $all->map(function ($item) {
                return $this->transformTurnamenForMobile($item);
            });

            return [
                'data' => $transformed,
                'total' => $transformed->count(),
                'currentPage' => 1,
                'perPage' => -1,
                'search' => $request->get('search', ''),
                'filters' => [
                    'cabor_id' => $request->get('cabor_id'),
                    'start_date' => $request->get('start_date'),
                    'end_date' => $request->get('end_date'),
                ],
            ];
        }

        $items = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return $this->transformTurnamenForMobile($item);
        });

        return [
            'data' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => $request->get('search', ''),
            'filters' => [
                'cabor_id' => $request->get('cabor_id'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ],
        ];
    }

    /**
     * Apply mobile-specific filters
     */
    protected function applyMobileFilters($query, $request)
    {
        // Filter by cabor_id
        if ($request->filled('cabor_id') && $request->cabor_id !== 'all') {
            $query->whereHas('caborKategori', function ($q) use ($request) {
                $q->where('cabor_id', $request->cabor_id);
            });
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('tanggal_mulai', [$request->start_date, $request->end_date])
                  ->orWhereBetween('tanggal_selesai', [$request->start_date, $request->end_date])
                  ->orWhere(function ($subQ) use ($request) {
                      $subQ->where('tanggal_mulai', '<=', $request->start_date)
                           ->where('tanggal_selesai', '>=', $request->end_date);
                  });
            });
        }
    }

    /**
     * Apply role-based filtering
     */
    protected function applyRoleFilter($query)
    {
        $auth = Auth::user();
        
        if ($auth->current_role_id == 35) { // Atlet
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriAtlet", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("atlet_id", $auth->atlet->id);
                });
            });
        } elseif ($auth->current_role_id == 36) { // Pelatih
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriPelatih", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("pelatih_id", $auth->pelatih->id);
                });
            });
        } elseif ($auth->current_role_id == 37) { // Tenaga Pendukung
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("tenagaPendukung", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("tenaga_pendukung_id", $auth->tenagaPendukung->id);
                });
            });
        }
    }

    /**
     * Transform turnamen data for mobile
     */
    protected function transformTurnamenForMobile($item)
    {
        $pesertaCounts = $this->getPesertaCount($item->id);
        $totalPeserta = $pesertaCounts['atlet'] + $pesertaCounts['pelatih'] + $pesertaCounts['tenaga_pendukung'];

        return [
            'id' => $item->id,
            'nama' => $item->nama,
            'cabor' => $item->caborKategori->cabor->nama ?? '-',
            'kategori' => $item->caborKategori->nama ?? '-',
            'periode' => $this->formatPeriodeForMobile($item->tanggal_mulai, $item->tanggal_selesai),
            'tingkat' => $item->tingkat->nama ?? '-',
            'lokasi' => $item->lokasi,
            'juara' => $item->juara->nama ?? '-',
            'hasil' => $item->hasil ?? '-',
            'jumlahPeserta' => $totalPeserta,
        ];
    }

    /**
     * Format periode for mobile
     */
    protected function formatPeriodeForMobile($startDate, $endDate): string
    {
        if (!$startDate || !$endDate) {
            return '-';
        }

        return $startDate . ' s/d ' . $endDate;
    }

    /**
     * Get cabor list for filter
     */
    public function getCaborList()
    {
        return $this->model->with('caborKategori.cabor')
            ->get()
            ->pluck('caborKategori.cabor.nama')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get peserta for mobile
     */
    public function getPesertaForMobile($turnamenId, $request)
    {
        $turnamen = $this->model->findOrFail($turnamenId);
        $caborKategoriId = $turnamen->cabor_kategori_id;

        // Get atlet IDs from turnamen_peserta table
        $atletIds = \Illuminate\Support\Facades\DB::table('turnamen_peserta')
            ->where('turnamen_id', $turnamenId)
            ->where('peserta_type', 'App\\Models\\Atlet')
            ->pluck('peserta_id');

        // Get atlet
        $atletQuery = \App\Models\Atlet::with(['media'])
            ->whereIn('atlets.id', $atletIds)
            ->leftJoin('cabor_kategori_atlet', function ($join) use ($caborKategoriId) {
                $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                    ->where('cabor_kategori_atlet.cabor_kategori_id', $caborKategoriId)
                    ->whereNull('cabor_kategori_atlet.deleted_at');
            })
            ->leftJoin('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
            ->select(
                'atlets.*',
                \Illuminate\Support\Facades\DB::raw("COALESCE(mst_posisi_atlet.nama, '-') as posisi")
            );

        // Get pelatih IDs from turnamen_peserta table
        $pelatihIds = \Illuminate\Support\Facades\DB::table('turnamen_peserta')
            ->where('turnamen_id', $turnamenId)
            ->where('peserta_type', 'App\\Models\\Pelatih')
            ->pluck('peserta_id');

        // Get pelatih
        $pelatihQuery = \App\Models\Pelatih::with(['media'])
            ->whereIn('pelatihs.id', $pelatihIds)
            ->leftJoin('cabor_kategori_pelatih', function ($join) use ($caborKategoriId) {
                $join->on('pelatihs.id', '=', 'cabor_kategori_pelatih.pelatih_id')
                    ->where('cabor_kategori_pelatih.cabor_kategori_id', $caborKategoriId)
                    ->whereNull('cabor_kategori_pelatih.deleted_at');
            })
            ->leftJoin('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
            ->select(
                'pelatihs.*',
                \Illuminate\Support\Facades\DB::raw("COALESCE(mst_jenis_pelatih.nama, '-') as jenis_pelatih")
            );

        // Get tenaga pendukung IDs from turnamen_peserta table
        $tenagaIds = \Illuminate\Support\Facades\DB::table('turnamen_peserta')
            ->where('turnamen_id', $turnamenId)
            ->where('peserta_type', 'App\\Models\\TenagaPendukung')
            ->pluck('peserta_id');

        // Get tenaga pendukung
        $tenagaQuery = \App\Models\TenagaPendukung::with(['media'])
            ->whereIn('tenaga_pendukungs.id', $tenagaIds)
            ->leftJoin('cabor_kategori_tenaga_pendukung', function ($join) use ($caborKategoriId) {
                $join->on('tenaga_pendukungs.id', '=', 'cabor_kategori_tenaga_pendukung.tenaga_pendukung_id')
                    ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $caborKategoriId)
                    ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at');
            })
            ->leftJoin('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
            ->select(
                'tenaga_pendukungs.*',
                \Illuminate\Support\Facades\DB::raw("COALESCE(mst_jenis_tenaga_pendukung.nama, '-') as jenis_tenaga_pendukung")
            );

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $atletQuery->where('atlets.nama', 'like', "%{$search}%");
            $pelatihQuery->where('pelatihs.nama', 'like', "%{$search}%");
            $tenagaQuery->where('tenaga_pendukungs.nama', 'like', "%{$search}%");
        }

        $atlet = $atletQuery->orderBy('atlets.nama')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'foto' => $item->foto,
                'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                'usia' => $this->calculateAge($item->tanggal_lahir),
                'posisi' => $item->posisi,
            ];
        });

        $pelatih = $pelatihQuery->orderBy('pelatihs.nama')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'foto' => $item->foto,
                'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                'usia' => $this->calculateAge($item->tanggal_lahir),
                'jenisPelatih' => $item->jenis_pelatih,
            ];
        });

        $tenaga = $tenagaQuery->orderBy('tenaga_pendukungs.nama')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama,
                'foto' => $item->foto,
                'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                'usia' => $this->calculateAge($item->tanggal_lahir),
                'jenisTenagaPendukung' => $item->jenis_tenaga_pendukung,
            ];
        });

        return [
            'atlet' => $atlet,
            'pelatih' => $pelatih,
            'tenagaPendukung' => $tenaga,
        ];
    }

    /**
     * Map jenis kelamin
     */
    protected function mapJenisKelamin($jenisKelamin)
    {
        if ($jenisKelamin === 'L') return 'Laki-laki';
        if ($jenisKelamin === 'P') return 'Perempuan';
        return '-';
    }

    /**
     * Calculate age
     */
    protected function calculateAge($tanggalLahir)
    {
        if (!$tanggalLahir) return '-';
        
        try {
            $tanggalLahir = new \Carbon\Carbon($tanggalLahir);
            $today = \Carbon\Carbon::today();
            return (int) $tanggalLahir->diffInYears($today);
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Get detail with relations
     */
    public function getDetailWithRelations($id)
    {
        return $this->model->with(['caborKategori.cabor', 'tingkat', 'juara'])->find($id);
    }
}
