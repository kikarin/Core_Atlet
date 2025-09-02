<?php

namespace App\Repositories;

use App\Http\Requests\PemeriksaanRequest;
use App\Models\Pemeriksaan;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PemeriksaanPeserta;
use App\Models\PemeriksaanParameter;

class PemeriksaanRepository
{
    use RepositoryTrait;

    protected $model;

    protected $request;

    public function __construct(Pemeriksaan $model)
    {
        $this->model   = $model;
        $this->request = PemeriksaanRequest::createFromBase(request());
        $this->with    = [
            'cabor',
            'caborKategori',
            'tenagaPendukung',
            'created_by_user',
            'updated_by_user',
        ];
    }

    public function customIndex($data)
    {
        $query = $this->model->with($this->with)
            ->withCount([
                'pemeriksaanParameter as jumlah_parameter',
                'pemeriksaanPeserta as jumlah_peserta',
                'pemeriksaanPeserta as jumlah_atlet' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Atlet');
                },
                'pemeriksaanPeserta as jumlah_pelatih' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Pelatih');
                },
                'pemeriksaanPeserta as jumlah_tenaga_pendukung' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\TenagaPendukung');
                },
            ]);

        // Apply filters
        $this->applyFilters($query);

        $sortField = request('sort');
        $order     = request('order', 'asc');

        if ($sortField === 'cabor') {
            $query->join('cabor', 'pemeriksaan.cabor_id', '=', 'cabor.id')
                ->orderBy('cabor.nama', $order)
                ->select('pemeriksaan.*');
        } elseif ($sortField === 'cabor_kategori') {
            $query->join('cabor_kategori', 'pemeriksaan.cabor_kategori_id', '=', 'cabor_kategori.id')
                ->orderBy('cabor_kategori.nama', $order)
                ->select('pemeriksaan.*');
        } elseif ($sortField === 'tenaga_pendukung') {
            $query->join('tenaga_pendukungs', 'pemeriksaan.tenaga_pendukung_id', '=', 'tenaga_pendukungs.id')
                ->orderBy('tenaga_pendukungs.nama', $order)
                ->select('pemeriksaan.*');
        } else {
            // Sort by kolom di tabel pemeriksaan
            $validColumns = ['id', 'cabor_id', 'cabor_kategori_id', 'tenaga_pendukung_id', 'nama_pemeriksaan', 'tanggal_pemeriksaan', 'status', 'created_at', 'updated_at'];
            if (in_array($sortField, $validColumns)) {
                $query->orderBy($sortField, $order);
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        if (request('search')) {
            $search = request('search');
            $query->where('nama_pemeriksaan', 'like', "%$search%");
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
            $all         = $query->get();
            $transformed = collect($all)->map(function ($item) {
                return [
                    'id'                      => $item->id,
                    'cabor'                   => $item->cabor?->nama           ?? '-',
                    'cabor_kategori'          => $item->caborKategori?->nama   ?? '-',
                    'tenaga_pendukung'        => $item->tenagaPendukung?->nama ?? '-',
                    'nama_pemeriksaan'        => $item->nama_pemeriksaan,
                    'tanggal_pemeriksaan'     => $item->tanggal_pemeriksaan,
                    'status'                  => $item->status,
                    'jumlah_parameter'        => $item->jumlah_parameter        ?? 0,
                    'jumlah_peserta'          => $item->jumlah_peserta          ?? 0,
                    'jumlah_atlet'            => $item->jumlah_atlet            ?? 0,
                    'jumlah_pelatih'          => $item->jumlah_pelatih          ?? 0,
                    'jumlah_tenaga_pendukung' => $item->jumlah_tenaga_pendukung ?? 0,
                ];
            });
            $data += [
                'pemeriksaan' => $transformed,
                'total'       => $transformed->count(),
                'currentPage' => 1,
                'perPage'     => -1,
                'search'      => request('search', ''),
                'sort'        => request('sort', ''),
                'order'       => request('order', 'asc'),
            ];

            return $data;
        }
        $items       = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return [
                'id'                      => $item->id,
                'cabor'                   => $item->cabor?->nama           ?? '-',
                'cabor_kategori'          => $item->caborKategori?->nama   ?? '-',
                'tenaga_pendukung'        => $item->tenagaPendukung?->nama ?? '-',
                'nama_pemeriksaan'        => $item->nama_pemeriksaan,
                'tanggal_pemeriksaan'     => $item->tanggal_pemeriksaan,
                'status'                  => $item->status,
                'jumlah_parameter'        => $item->jumlah_parameter        ?? 0,
                'jumlah_peserta'          => $item->jumlah_peserta          ?? 0,
                'jumlah_atlet'            => $item->jumlah_atlet            ?? 0,
                'jumlah_pelatih'          => $item->jumlah_pelatih          ?? 0,
                'jumlah_tenaga_pendukung' => $item->jumlah_tenaga_pendukung ?? 0,
            ];
        });
        $data += [
            'pemeriksaan' => $transformed,
            'total'       => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage'     => $items->perPage(),
            'search'      => request('search', ''),
        ];

        return $data;
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query)
    {
        // Filter by cabor_id
        if (request('cabor_id') && request('cabor_id') !== 'all') {
            $query->where('cabor_id', request('cabor_id'));
        }

        // Filter by cabor_kategori_id
        if (request('cabor_kategori_id') && request('cabor_kategori_id') !== 'all') {
            $query->where('cabor_kategori_id', request('cabor_kategori_id'));
        }

        // Filter by date range
        if (request('filter_start_date') && request('filter_end_date')) {
            $query->whereBetween('created_at', [
                request('filter_start_date') . ' 00:00:00',
                request('filter_end_date') . ' 23:59:59'
            ]);
        }
    }

    public function customCreateEdit($data, $item = null)
    {
        $data['item'] = $item;

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

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function getById($id)
    {
        return $this->model->with($this->with)->findOrFail($id);
    }

    /**
     * Get data for mobile API with search and filters
     */
    public function getForMobile($request)
    {
        $query = $this->model->with($this->with)
            ->withCount([
                'pemeriksaanParameter as jumlah_parameter',
                'pemeriksaanPeserta as jumlah_peserta',
                'pemeriksaanPeserta as jumlah_atlet' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Atlet');
                },
                'pemeriksaanPeserta as jumlah_pelatih' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Pelatih');
                },
                'pemeriksaanPeserta as jumlah_tenaga_pendukung' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\TenagaPendukung');
                },
            ]);

        // Apply filters
        $this->applyMobileFilters($query, $request);

        // Apply role-based filtering
        $this->applyRoleFilter($query);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_pemeriksaan', 'like', "%{$search}%")
                  ->orWhereHas('cabor', function ($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tenagaPendukung', function ($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $query->orderBy('tanggal_pemeriksaan', 'desc');

        // Pagination
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        if ($perPage === -1) {
            $all = $query->get();
            $transformed = $all->map(function ($item) {
                return $this->transformPemeriksaanForMobile($item);
            });

            return [
                'data' => $transformed,
                'total' => $transformed->count(),
                'currentPage' => 1,
                'perPage' => -1,
                'search' => $request->get('search', ''),
                'filters' => [
                    'cabor_id' => $request->get('cabor_id'),
                    'tanggal_pemeriksaan' => $request->get('tanggal_pemeriksaan'),
                ],
            ];
        }

        $items = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return $this->transformPemeriksaanForMobile($item);
        });

        return [
            'data' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => $request->get('search', ''),
            'filters' => [
                'cabor_id' => $request->get('cabor_id'),
                'tanggal_pemeriksaan' => $request->get('tanggal_pemeriksaan'),
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
            $query->where('cabor_id', $request->cabor_id);
        }

        // Filter by tanggal_pemeriksaan
        if ($request->filled('tanggal_pemeriksaan')) {
            $query->whereDate('tanggal_pemeriksaan', $request->tanggal_pemeriksaan);
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
     * Transform pemeriksaan data for mobile
     */
    protected function transformPemeriksaanForMobile($item)
    {
        return [
            'id' => $item->id,
            'nama' => $item->nama_pemeriksaan,
            'cabor' => $item->cabor?->nama ?? '-',
            'kategori' => $item->caborKategori?->nama ?? '-',
            'tenagaPendukung' => $item->tenagaPendukung?->nama ?? '-',
            'peserta' => $item->jumlah_peserta ?? 0,
            'tanggal' => $item->tanggal_pemeriksaan,
            'status' => $this->mapStatus($item->status),
            'jumlah_parameter' => $item->jumlah_parameter ?? 0,
            'jumlah_atlet' => $item->jumlah_atlet ?? 0,
            'jumlah_pelatih' => $item->jumlah_pelatih ?? 0,
            'jumlah_tenaga_pendukung' => $item->jumlah_tenaga_pendukung ?? 0,
        ];
    }

    /**
     * Map status to Indonesian
     */
    protected function mapStatus($status)
    {
        return match ($status) {
            'selesai' => 'Sudah',
            'sebagian' => 'Sebagian',
            'belum' => 'Belum',
            default => $status,
        };
    }

    /**
     * Get cabor list for filter
     */
    public function getCaborList()
    {
        return $this->model->with('cabor')
            ->get()
            ->pluck('cabor.nama')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get peserta for mobile
     */
    public function getPesertaForMobile($pemeriksaanId, $request)
    {
        $pemeriksaan = $this->model->findOrFail($pemeriksaanId);
        $caborKategoriId = $pemeriksaan->cabor_kategori_id;

        // Get atlet
        $atletQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\Atlet');

        // Get pelatih
        $pelatihQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\Pelatih');

        // Get tenaga pendukung
        $tenagaQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\TenagaPendukung');

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $atletQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            $pelatihQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            $tenagaQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $atlet = $atletQuery->get()->map(function ($item) use ($caborKategoriId) {
            $atletModel = $item->peserta;
            $posisi = $this->getAtletPosisi($atletModel->id, $caborKategoriId);
            
            return [
                'id' => $atletModel->id,
                'nama' => $atletModel->nama,
                'foto' => $atletModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($atletModel->jenis_kelamin),
                'usia' => $this->calculateAge($atletModel->tanggal_lahir),
                'posisi' => $posisi,
                'statusPemeriksaan' => $item->status?->nama ?? null,
            ];
        });

        $pelatih = $pelatihQuery->get()->map(function ($item) use ($caborKategoriId) {
            $pelatihModel = $item->peserta;
            $jenisPelatih = $this->getPelatihJenis($pelatihModel->id, $caborKategoriId);
            
            return [
                'id' => $pelatihModel->id,
                'nama' => $pelatihModel->nama,
                'foto' => $pelatihModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($pelatihModel->jenis_kelamin),
                'usia' => $this->calculateAge($pelatihModel->tanggal_lahir),
                'jenisPelatih' => $jenisPelatih,
                'statusPemeriksaan' => $item->status?->nama ?? null,
            ];
        });

        $tenaga = $tenagaQuery->get()->map(function ($item) use ($caborKategoriId) {
            $tenagaModel = $item->peserta;
            $jenisTenaga = $this->getTenagaPendukungJenis($tenagaModel->id, $caborKategoriId);
            
            return [
                'id' => $tenagaModel->id,
                'nama' => $tenagaModel->nama,
                'foto' => $tenagaModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($tenagaModel->jenis_kelamin),
                'usia' => $this->calculateAge($tenagaModel->tanggal_lahir),
                'jenisTenagaPendukung' => $jenisTenaga,
                'statusPemeriksaan' => $item->status?->nama ?? null,
            ];
        });

        return [
            'atlet' => $atlet,
            'pelatih' => $pelatih,
            'tenagaPendukung' => $tenaga,
        ];
    }

    /**
     * Get atlet posisi
     */
    protected function getAtletPosisi($atletId, $caborKategoriId)
    {
        $posisi = DB::table('cabor_kategori_atlet')
            ->join('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
            ->where('cabor_kategori_atlet.atlet_id', $atletId)
            ->where('cabor_kategori_atlet.cabor_kategori_id', $caborKategoriId)
            ->whereNull('cabor_kategori_atlet.deleted_at')
            ->value('mst_posisi_atlet.nama');
            
        return $posisi ?? '-';
    }

    /**
     * Get pelatih jenis
     */
    protected function getPelatihJenis($pelatihId, $caborKategoriId)
    {
        $jenis = DB::table('cabor_kategori_pelatih')
            ->join('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
            ->where('cabor_kategori_pelatih.pelatih_id', $pelatihId)
            ->where('cabor_kategori_pelatih.cabor_kategori_id', $caborKategoriId)
            ->whereNull('cabor_kategori_pelatih.deleted_at')
            ->value('mst_jenis_pelatih.nama');
            
        return $jenis ?? '-';
    }

    /**
     * Get tenaga pendukung jenis
     */
    protected function getTenagaPendukungJenis($tenagaId, $caborKategoriId)
    {
        $jenis = DB::table('cabor_kategori_tenaga_pendukung')
            ->join('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
            ->where('cabor_kategori_tenaga_pendukung.tenaga_pendukung_id', $tenagaId)
            ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $caborKategoriId)
            ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at')
            ->value('mst_jenis_tenaga_pendukung.nama');
            
        return $jenis ?? '-';
    }

    /**
     * Get parameter for mobile
     */
    public function getParameterForMobile($pemeriksaanId, $request)
    {
        $query = PemeriksaanParameter::where('pemeriksaan_id', $pemeriksaanId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_parameter', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_parameter')->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_parameter' => $item->nama_parameter,
                'satuan' => $item->satuan,
            ];
        });
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
            $tanggalLahir = new Carbon($tanggalLahir);
            $today = Carbon::today();
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
        return $this->model->with($this->with)->find($id);
    }

    /**
     * Get parameter detail
     */
    public function getParameterDetail($parameterId)
    {
        return PemeriksaanParameter::find($parameterId);
    }

    /**
     * Get peserta parameter for mobile
     */
    public function getPesertaParameterForMobile($pemeriksaanId, $parameterId, $request)
    {
        $pemeriksaan = $this->model->findOrFail($pemeriksaanId);
        $caborKategoriId = $pemeriksaan->cabor_kategori_id;

        // Get atlet dengan nilai parameter
        $atletQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\Atlet')
            ->whereHas('pemeriksaanPesertaParameter', function ($q) use ($parameterId) {
                $q->where('pemeriksaan_parameter_id', $parameterId);
            });

        // Get pelatih dengan nilai parameter
        $pelatihQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\Pelatih')
            ->whereHas('pemeriksaanPesertaParameter', function ($q) use ($parameterId) {
                $q->where('pemeriksaan_parameter_id', $parameterId);
            });

        // Get tenaga pendukung dengan nilai parameter
        $tenagaQuery = PemeriksaanPeserta::with(['peserta', 'status'])
            ->where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', 'App\\Models\\TenagaPendukung')
            ->whereHas('pemeriksaanPesertaParameter', function ($q) use ($parameterId) {
                $q->where('pemeriksaan_parameter_id', $parameterId);
            });

        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $atletQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            $pelatihQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
            $tenagaQuery->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $atlet = $atletQuery->get()->map(function ($item) use ($caborKategoriId, $parameterId) {
            $atletModel = $item->peserta;
            $posisi = $this->getAtletPosisi($atletModel->id, $caborKategoriId);
            $parameterData = $this->getPesertaParameterData($item->id, $parameterId);
            
            return [
                'id' => $atletModel->id,
                'nama' => $atletModel->nama,
                'foto' => $atletModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($atletModel->jenis_kelamin),
                'usia' => $this->calculateAge($atletModel->tanggal_lahir),
                'posisi' => $posisi,
                'nilai' => $parameterData['nilai'],
                'status' => $this->mapTrendStatus($parameterData['trend']),
            ];
        });

        $pelatih = $pelatihQuery->get()->map(function ($item) use ($caborKategoriId, $parameterId) {
            $pelatihModel = $item->peserta;
            $jenisPelatih = $this->getPelatihJenis($pelatihModel->id, $caborKategoriId);
            $parameterData = $this->getPesertaParameterData($item->id, $parameterId);
            
            return [
                'id' => $pelatihModel->id,
                'nama' => $pelatihModel->nama,
                'foto' => $pelatihModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($pelatihModel->jenis_kelamin),
                'usia' => $this->calculateAge($pelatihModel->tanggal_lahir),
                'jenisPelatih' => $jenisPelatih,
                'nilai' => $parameterData['nilai'],
                'status' => $this->mapTrendStatus($parameterData['trend']),
            ];
        });

        $tenaga = $tenagaQuery->get()->map(function ($item) use ($caborKategoriId, $parameterId) {
            $tenagaModel = $item->peserta;
            $jenisTenaga = $this->getTenagaPendukungJenis($tenagaModel->id, $caborKategoriId);
            $parameterData = $this->getPesertaParameterData($item->id, $parameterId);
            
            return [
                'id' => $tenagaModel->id,
                'nama' => $tenagaModel->nama,
                'foto' => $tenagaModel->foto,
                'jenisKelamin' => $this->mapJenisKelamin($tenagaModel->jenis_kelamin),
                'usia' => $this->calculateAge($tenagaModel->tanggal_lahir),
                'jenisTenagaPendukung' => $jenisTenaga,
                'nilai' => $parameterData['nilai'],
                'status' => $this->mapTrendStatus($parameterData['trend']),
            ];
        });

        return [
            'atlet' => $atlet,
            'pelatih' => $pelatih,
            'tenagaPendukung' => $tenaga,
        ];
    }

    /**
     * Get peserta parameter data (nilai dan trend)
     */
    protected function getPesertaParameterData($pemeriksaanPesertaId, $parameterId)
    {
        $data = DB::table('pemeriksaan_peserta_parameter')
            ->where('pemeriksaan_peserta_id', $pemeriksaanPesertaId)
            ->where('pemeriksaan_parameter_id', $parameterId)
            ->first();

        return [
            'nilai' => $data->nilai ?? '-',
            'trend' => $data->trend ?? 'stabil',
        ];
    }

    /**
     * Map trend status to Indonesian
     */
    protected function mapTrendStatus($trend)
    {
        return match ($trend) {
            'stabil' => 'Stabil',
            'kenaikan' => 'Kenaikan',
            'penurunan' => 'Penurunan',
            default => 'Stabil',
        };
    }
}
