<?php

namespace App\Repositories;

use App\Models\ProgramLatihan;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;

class ProgramLatihanRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(ProgramLatihan $model)
    {
        $this->model = $model;
        $this->with  = ['caborKategori', 'cabor', 'created_by_user', 'updated_by_user'];
    }

    public function customIndex($data)
    {
        $query = $this->model->with(['caborKategori', 'cabor'])
            ->withCount(['rencanaLatihan']);

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_program', 'like', "%$search%")
                    ->orWhere('keterangan', 'like', "%$search%");
            });
        }
        
        // Apply filters
        $this->applyFilters($query);
        
        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'nama_program', 'periode_mulai', 'periode_selesai', 'created_at', 'updated_at'];
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
            $all         = $query->get();
            $transformed = collect($all)->map(function ($item) {
                return [
                    'id'                     => $item->id,
                    'nama_program'           => $item->nama_program,
                    'cabor_kategori_id'      => $item->cabor_kategori_id,
                    'cabor_kategori_nama'    => $item->caborKategori?->nama,
                    'periode_mulai'          => $item->periode_mulai,
                    'periode_selesai'        => $item->periode_selesai,
                    'keterangan'             => $item->keterangan,
                    'jumlah_rencana_latihan' => $item->rencana_latihan_count,
                ];
            });
            $data += [
                'data'        => $transformed,
                'total'       => $transformed->count(),
                'currentPage' => 1,
                'perPage'     => -1,
                'search'      => request('search', ''),
                'sort'        => request('sort', ''),
                'order'       => request('order', 'asc'),
            ];

            return $data;
        }
        $pageForPaginate = $page < 1 ? 1 : $page;
        $items           = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();
        $transformed     = collect($items->items())->map(function ($item) {
            return [
                'id'                             => $item->id,
                'cabor_id'                       => $item->cabor_id,
                'cabor_nama'                     => $item->cabor?->nama,
                'nama_program'                   => $item->nama_program,
                'cabor_kategori_id'              => $item->cabor_kategori_id,
                'cabor_kategori_nama'            => $item->caborKategori?->nama,
                'periode_mulai'                  => $item->periode_mulai,
                'periode_selesai'                => $item->periode_selesai,
                'keterangan'                     => $item->keterangan,
                'jumlah_target_individu'         => $item->targetLatihan()->where('jenis_target', 'individu')->count(),
                'jumlah_target_kelompok'         => $item->targetLatihan()->where('jenis_target', 'kelompok')->count(),
                'jumlah_target_atlet'            => $item->targetLatihan()->where('peruntukan', 'atlet')->count(),
                'jumlah_target_pelatih'          => $item->targetLatihan()->where('peruntukan', 'pelatih')->count(),
                'jumlah_target_tenaga_pendukung' => $item->targetLatihan()->where('peruntukan', 'tenaga-pendukung')->count(),
                'jumlah_rencana_latihan'         => $item->rencana_latihan_count,
            ];
        });
        $data += [
            'data'        => $transformed,
            'total'       => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage'     => $items->perPage(),
            'search'      => request('search', ''),
            'sort'        => request('sort', ''),
            'order'       => request('order', 'asc'),
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
        $userId = Auth::check() ? Auth::id() : null;
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

    public function getDetailWithRelations($id)
    {
        $with = array_merge($this->with, ['caborKategori', 'cabor']);

        return $this->model->with($with)->findOrFail($id);
    }

    /**
     * Get data for mobile app with search and filters
     */
    public function getForMobile($request)
    {
        $query = $this->model->with(['caborKategori', 'cabor'])
            ->withCount(['rencanaLatihan']);
            $this->applyRoleBasedFiltering($query);

        // Search functionality
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_program', 'like', "%$search%")
                    ->orWhere('keterangan', 'like', "%$search%")
                    ->orWhereHas('cabor', function ($caborQuery) use ($search) {
                        $caborQuery->where('nama', 'like', "%$search%");
                    })
                    ->orWhereHas('caborKategori', function ($kategoriQuery) use ($search) {
                        $kategoriQuery->where('nama', 'like', "%$search%");
                    });
            });
        }
        
        // Apply filters
        $this->applyFilters($query);
        
        // Apply role-based filtering
        $this->applyRoleBasedFiltering($query);
        
        // Sorting
        $query->orderBy('id', 'desc');

        // Pagination
        $perPage = (int) $request->per_page ?: 10;
        $page = (int) $request->page ?: 1;
        
        $items = $query->paginate($perPage, ['*'], 'page', $page);
        
        // Transform data for mobile
        $transformed = collect($items->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_program' => $item->nama_program,
                'cabor' => $item->cabor?->nama ?? '-',
                'kategori' => $item->caborKategori?->nama ?? '-',
                'periode' => $this->formatPeriodeForMobile($item->periode_mulai, $item->periode_selesai),
                'keterangan' => $item->keterangan,
                'jumlah_rencana_latihan' => $item->rencana_latihan_count,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return [
            'data' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => $request->search ?? '',
            'filters' => [
                'cabor_id' => $request->cabor_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ],
        ];
    }

    /**
     * Get list of cabor for filter options
     */
    public function getCaborList()
    {
        return $this->model->with('cabor')
            ->select('cabor_id')
            ->distinct()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->cabor->id ?? null,
                    'nama' => $item->cabor->nama ?? null,
                ];
            })
            ->filter(function ($item) {
                return $item['id'] && $item['nama'];
            })
            ->values();
    }

    /**
     * Format periode for mobile app
     */
    private function formatPeriodeForMobile($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return '-';
        }

        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);

        $startDay = $start->format('j');
        $startMonth = $this->getIndonesianMonth($start->format('n'));
        $startYear = $start->format('Y');

        $endDay = $end->format('j');
        $endMonth = $this->getIndonesianMonth($end->format('n'));
        $endYear = $end->format('Y');

        // Jika tahun sama
        if ($startYear === $endYear) {
            // Jika bulan sama
            if ($startMonth === $endMonth) {
                return "{$startDay}-{$endDay} {$startMonth} {$startYear}";
            } else {
                // Jika bulan berbeda
                return "{$startDay} {$startMonth} - {$endDay} {$endMonth} {$startYear}";
            }
        } else {
            // Jika tahun berbeda
            return "{$startDay} {$startMonth} {$startYear} - {$endDay} {$endMonth} {$endYear}";
        }
    }

    /**
     * Get Indonesian month name
     */
    private function getIndonesianMonth($monthNumber)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$monthNumber] ?? '';
    }

    /**
     * Apply role-based filtering
     */
    protected function applyRoleBasedFiltering($query)
    {
        $auth = Auth::user();
        
        if ($auth->current_role_id == 35) { // Atlet
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriAtlet", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("atlet_id", $auth->atlet->id);
                });
            });
        }

        if ($auth->current_role_id == 36) { // Pelatih
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriPelatih", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("pelatih_id", $auth->pelatih->id);
                });
            });
        }

        if ($auth->current_role_id == 37) { // Tenaga Pendukung
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("tenagaPendukung", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("tenaga_pendukung_id", $auth->tenagaPendukung->id);
                });
            });
        }
    }
}
