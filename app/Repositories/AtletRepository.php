<?php

namespace App\Repositories;

use App\Models\Atlet;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Role;

class AtletRepository
{
    use RepositoryTrait;

    protected $model;

    protected $atletOrangTuaRepository;

    public function __construct(Atlet $model, AtletOrangTuaRepository $atletOrangTuaRepository)
    {
        $this->model                   = $model;
        $this->atletOrangTuaRepository = $atletOrangTuaRepository;
        $this->with                    = [
            'media',
            'created_by_user',
            'updated_by_user',
            'user',
            'atletOrangTua.created_by_user',
            'atletOrangTua.updated_by_user',
            'sertifikat',
            'sertifikat.media',
            'sertifikat.created_by_user',
            'sertifikat.updated_by_user',
            'prestasi',
            'prestasi.created_by_user',
            'prestasi.updated_by_user',
            'dokumen',
            'dokumen.created_by_user',
            'dokumen.updated_by_user',
            'kesehatan',
            'kesehatan.created_by_user',
            'kesehatan.updated_by_user',
            'caborKategoriAtlet.cabor',
            'caborKategoriAtlet.caborKategori',
        ];
    }

    public function customIndex($data)
    {
        $query = $this->model->query();

        // Filter untuk exclude atlet yang sudah ada di kategori tertentu
        if (request('exclude_cabor_kategori_id')) {
            $excludeKategoriId = request('exclude_cabor_kategori_id');
            $query->whereNotExists(function ($subQuery) use ($excludeKategoriId) {
                $subQuery->select(DB::raw(1))
                    ->from('cabor_kategori_atlet')
                    ->whereColumn('cabor_kategori_atlet.atlet_id', 'atlets.id')
                    ->where('cabor_kategori_atlet.cabor_kategori_id', $excludeKategoriId)
                    ->whereNull('cabor_kategori_atlet.deleted_at'); // hanya relasi aktif
            });
        }

        // Apply filters
        $this->applyFilters($query);

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', '%'.$search.'%')
                    ->orWhere('nama', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('no_hp', 'like', '%'.$search.'%')
                    ->orWhere('jenis_kelamin', 'like', '%'.$search.'%')
                    ->orWhere('tempat_lahir', 'like', '%'.$search.'%')
                    ->orWhere('alamat', 'like', '%'.$search.'%');
            });
        }
        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'nik', 'nama', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'no_hp', 'email', 'is_active', 'created_at', 'updated_at'];
            if (in_array($sortField, $validColumns)) {
                $query->orderBy($sortField, $order);
            } else {
                $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);
        if ($perPage === -1) {
            $all         = $query->get();
            $transformed = collect($all)->map(function ($item) {
                // relasi caborKategoriAtlet dimuat
                $item->load(['caborKategoriAtlet.cabor']);
                $itemArray = $item->toArray();
                if (!isset($itemArray['cabor_kategori_atlet'])) {
                    $itemArray['cabor_kategori_atlet'] = $item->caborKategoriAtlet->map(function ($cabor) {
                        return [
                            'id'    => $cabor->id,
                            'cabor' => $cabor->cabor ? $cabor->cabor->toArray() : null,
                        ];
                    })->toArray();
                }
                return $itemArray;
            });
            $data += [
                'atlets'      => $transformed,
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
            // relasi caborKategoriAtlet dimuat
            $item->load(['caborKategoriAtlet.cabor']);
            $itemArray = $item->toArray();
            if (!isset($itemArray['cabor_kategori_atlet'])) {
                $itemArray['cabor_kategori_atlet'] = $item->caborKategoriAtlet->map(function ($cabor) {
                    return [
                        'id'    => $cabor->id,
                        'cabor' => $cabor->cabor ? $cabor->cabor->toArray() : null,
                    ];
                })->toArray();
            }
            return $itemArray;
        });
        $data += [
            'atlets'      => $transformed,
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
            $caborId = request('cabor_id');
            $query->whereExists(function ($sub) use ($caborId) {
                $sub->select(DB::raw(1))
                    ->from('cabor_kategori_atlet as cka')
                    ->whereColumn('cka.atlet_id', 'atlets.id')
                    ->where('cka.cabor_id', $caborId)
                    ->whereNull('cka.deleted_at');
            });
        }

        // Filter by cabor_kategori_id
        if (request('cabor_kategori_id') && request('cabor_kategori_id') !== 'all') {
            $caborKategoriId = request('cabor_kategori_id');
            $query->whereExists(function ($sub) use ($caborKategoriId) {
                $sub->select(DB::raw(1))
                    ->from('cabor_kategori_atlet as cka')
                    ->whereColumn('cka.atlet_id', 'atlets.id')
                    ->where('cka.cabor_kategori_id', $caborKategoriId)
                    ->whereNull('cka.deleted_at');
            });
        }

        // Filter by jenis kelamin
        if (request('jenis_kelamin') && request('jenis_kelamin') !== 'all') {
            $query->where('jenis_kelamin', request('jenis_kelamin'));
        }

        // Filter by status (is_active)
        if (request('status') && request('status') !== 'all') {
            $statusValue = request('status');
            Log::info('Filtering by status:', ['status' => $statusValue, 'type' => gettype($statusValue)]);

            // Convert string to boolean/integer for database query
            if ($statusValue === '1' || $statusValue === 1 || $statusValue === true) {
                $query->where('is_active', 1);
            } elseif ($statusValue === '0' || $statusValue === 0 || $statusValue === false) {
                $query->where('is_active', 0);
            }
        }

        // Filter by kategori usia
        if (request('kategori_usia') && request('kategori_usia') !== 'all') {
            $this->applyKategoriUsiaFilter($query, request('kategori_usia'));
        }

        // Filter by lama bergabung
        if (request('lama_bergabung') && request('lama_bergabung') !== 'all') {
            $this->applyLamaBergabungFilter($query, request('lama_bergabung'));
        }

        // Filter by date range
        if (request('filter_start_date') && request('filter_end_date')) {
            $query->whereBetween('created_at', [
                request('filter_start_date') . ' 00:00:00',
                request('filter_end_date') . ' 23:59:59',
            ]);
        }
    }

    /**
     * Apply kategori usia filter
     */
    protected function applyKategoriUsiaFilter($query, $kategori)
    {
        $today = now();

        switch ($kategori) {
            case 'anak':
                $query->where('tanggal_lahir', '>=', $today->copy()->subYears(12));
                break;
            case 'remaja':
                $query->where('tanggal_lahir', '>=', $today->copy()->subYears(17))
                      ->where('tanggal_lahir', '<', $today->copy()->subYears(13));
                break;
            case 'dewasa_muda':
                $query->where('tanggal_lahir', '>=', $today->copy()->subYears(25))
                      ->where('tanggal_lahir', '<', $today->copy()->subYears(18));
                break;
            case 'dewasa':
                $query->where('tanggal_lahir', '>=', $today->copy()->subYears(35))
                      ->where('tanggal_lahir', '<', $today->copy()->subYears(26));
                break;
            case 'dewasa_tua':
                $query->where('tanggal_lahir', '<', $today->copy()->subYears(36));
                break;
        }
    }

    /**
     * Apply lama bergabung filter
     */
    protected function applyLamaBergabungFilter($query, $kategori)
    {
        $today = now();

        switch ($kategori) {
            case 'baru':
                $query->where('tanggal_bergabung', '>=', $today->copy()->subYear());
                break;
            case 'sedang':
                $query->where('tanggal_bergabung', '>=', $today->copy()->subYears(3))
                      ->where('tanggal_bergabung', '<', $today->copy()->subYear());
                break;
            case 'lama':
                $query->where('tanggal_bergabung', '>=', $today->copy()->subYears(5))
                      ->where('tanggal_bergabung', '<', $today->copy()->subYears(3));
                break;
            case 'sangat_lama':
                $query->where('tanggal_bergabung', '<', $today->copy()->subYears(5));
                break;
        }
    }

    public function customCreateEdit($data, $item = null)
    {
        // Tambahkan relasi untuk nanti kecamatan/kelurahan
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

        $nullableFields = ['kecamatan_id', 'kelurahan_id', 'tanggal_bergabung'];
        foreach ($nullableFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        Log::info('AtletRepository: customDataCreateUpdate', [
            'data'   => $data,
            'method' => is_null($record) ? 'create' : 'update',
        ]);

        return $data;
    }

    public function callbackAfterStoreOrUpdate($model, $data, $method = 'store', $record_sebelumnya = null)
    {
        try {
            DB::beginTransaction();

            Log::info('AtletRepository: Starting file upload process', [
                'method'         => $method,
                'has_file'       => isset($data['file']),
                'file_data'      => $data['file'] ? 'File exists' : 'No file',
                'is_delete_foto' => @$data['is_delete_foto'],
            ]);

            // Handle file upload
            if (@$data['is_delete_foto'] == 1) {
                $model->clearMediaCollection('images');
                Log::info('AtletRepository: Cleared media collection');
            }

            if (@$data['file']) {
                Log::info('AtletRepository: Adding media file', [
                    'file_name' => $data['file']->getClientOriginalName(),
                    'file_size' => $data['file']->getSize(),
                    'model_id'  => $model->id,
                ]);

                $media = $model->addMedia($data['file'])
                    ->usingName($data['nama'])
                    ->toMediaCollection('images');

                Log::info('AtletRepository: Media added successfully', [
                    'media_id'  => $media->id,
                    'file_name' => $media->file_name,
                    'disk'      => $media->disk,
                    'path'      => $media->getPath(),
                ]);
            }

            // Handle AtletOrangTua data
            $atletOrangTuaData   = [];
            $atletOrangTuaFields = [
                'nama_ibu_kandung', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'alamat_ibu', 'no_hp_ibu', 'pekerjaan_ibu',
                'nama_ayah_kandung', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'alamat_ayah', 'no_hp_ayah', 'pekerjaan_ayah',
                'nama_wali', 'tempat_lahir_wali', 'tanggal_lahir_wali', 'alamat_wali', 'no_hp_wali', 'pekerjaan_wali',
            ];

            foreach ($atletOrangTuaFields as $field) {
                if (isset($data[$field])) {
                    $atletOrangTuaData[$field] = $data[$field];
                }
            }

            if (! empty($atletOrangTuaData)) {
                $atletOrangTuaData['atlet_id'] = $model->id;

                if (isset($data['atlet_orang_tua_id']) && ! is_null($data['atlet_orang_tua_id'])) {
                    $this->atletOrangTuaRepository->update($data['atlet_orang_tua_id'], $atletOrangTuaData);
                    Log::info('AtletRepository: Updated AtletOrangTua', ['id' => $data['atlet_orang_tua_id']]);
                } else {
                    $this->atletOrangTuaRepository->create($atletOrangTuaData);
                    Log::info('AtletRepository: Created new AtletOrangTua for atlet_id', ['atlet_id' => $model->id]);
                }
            }

            // Handle Atlet Akun data
            if (isset($data['akun_email']) && $data['akun_email']) {
                $this->handleAtletAkun($model, $data);
            }

            DB::commit();
            Log::info('AtletRepository: Transaction committed successfully');

            return $model;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('AtletRepository: Error during file upload or AtletOrangTua save', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle Atlet Akun creation/update
     */
    public function handleAtletAkun($atlet, $data)
    {
        $userId   = Auth::check() ? Auth::id() : null;
        $userData = [
            'name'            => $atlet->nama,
            'email'           => $data['akun_email'],
            'no_hp'           => $atlet->no_hp,
            'is_active'       => 1,
            'current_role_id' => 35, // Set current_role_id ke Role Atlet
            'created_by'      => $userId,
            'updated_by'      => $userId,
        ];

        // Jika ada password, hash password
        if (isset($data['akun_password']) && $data['akun_password']) {
            $userData['password'] = bcrypt($data['akun_password']);
        }

        // Jika sudah ada users_id, update user
        if (isset($data['users_id']) && $data['users_id']) {
            $user = User::find($data['users_id']);
            if ($user) {
                $user->update($userData);

                // Ensure role is assigned using Spatie Permission
                $role = Role::find(35); // Role Atlet
                if ($role && !$user->hasRole($role)) {
                    $user->assignRole($role);
                }

                Log::info('AtletRepository: Updated existing user for atlet', [
                    'atlet_id' => $atlet->id,
                    'user_id'  => $user->id,
                ]);
            }
        } else {
            // Create new user
            $user = User::create($userData);

            // Assign role Atlet using Spatie Permission
            $role = Role::find(35); // Role Atlet
            if ($role) {
                $user->assignRole($role);
            }

            // Also create users_role record for compatibility
            $user->users_role()->create([
                'users_id'   => $user->id,
                'role_id'    => 35, // Role Atlet
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Update atlet dengan users_id
            $atlet->update(['users_id' => $user->id]);

            Log::info('AtletRepository: Created new user for atlet', [
                'atlet_id' => $atlet->id,
                'user_id'  => $user->id,
            ]);
        }
    }

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function getDetailWithRelations($id)
    {
        $with = array_merge($this->with, ['kecamatan', 'kelurahan']);

        return $this->model->with($with)->findOrFail($id);
    }

    /**
     * Get jumlah karakteristik atlet
     */
    public function jumlah_karakteristik($data = [])
    {
        $tanggal_awal  = $data['tanggal_awal']  ?? null;
        $tanggal_akhir = $data['tanggal_akhir'] ?? null;

        // Ambil semua data yang akan direkap
        $this->with = [];
        $getData    = $this->getAll([
            'filter_start_date' => $tanggal_awal,
            'filter_end_date'   => $tanggal_akhir,
        ]);
        $totalData = count($getData); // total keseluruhan

        $result = [];

        // Jenis Kelamin
        $listIndikator         = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
        $listIndikator['NULL'] = '-';

        $indikatorData = [];
        foreach ($listIndikator as $key => $value) {
            $jumlah = collect($getData)->filter(function ($item) use ($key) {
                $key_value = $item->jenis_kelamin ?? null;

                if ($key === 'NULL') {
                    return is_null($key_value);
                }

                return $key_value == $key;
            })->count();
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;

            $indikatorData[] = [
                'nama_indikator' => $value,
                'jumlah'         => $jumlah,
                'persentase'     => $persentase,
            ];
        }

        $result[] = [
            'key'  => 'jenis_kelamin',
            'name' => 'Jenis Kelamin',
            'data' => $indikatorData,
        ];

        // Status Aktif
        $listIndikator         = [1 => 'Aktif', 0 => 'Nonaktif'];
        $listIndikator['NULL'] = '-';

        $indikatorData = [];
        foreach ($listIndikator as $key => $value) {
            $jumlah = collect($getData)->filter(function ($item) use ($key) {
                $key_value = $item->is_active ?? null;

                if ($key === 'NULL') {
                    return is_null($key_value);
                }

                return $key_value == $key;
            })->count();
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;

            $indikatorData[] = [
                'nama_indikator' => $value,
                'jumlah'         => $jumlah,
                'persentase'     => $persentase,
            ];
        }

        $result[] = [
            'key'  => 'status_aktif',
            'name' => 'Status Aktif',
            'data' => $indikatorData,
        ];

        // Usia (dibagi berdasarkan range)
        $usiaRanges = [
            'anak'        => ['min' => 0, 'max' => 12, 'label' => 'Anak-anak (0-12 tahun)'],
            'remaja'      => ['min' => 13, 'max' => 17, 'label' => 'Remaja (13-17 tahun)'],
            'dewasa_muda' => ['min' => 18, 'max' => 25, 'label' => 'Dewasa Muda (18-25 tahun)'],
            'dewasa'      => ['min' => 26, 'max' => 35, 'label' => 'Dewasa (26-35 tahun)'],
            'dewasa_tua'  => ['min' => 36, 'max' => 100, 'label' => 'Dewasa Tua (36+ tahun)'],
        ];

        $indikatorData = [];
        foreach ($usiaRanges as $key => $range) {
            $jumlah = collect($getData)->filter(function ($item) use ($range) {
                if (!$item->tanggal_lahir) {
                    return false;
                }

                $usia = date_diff(date_create($item->tanggal_lahir), date_create('today'))->y;
                return $usia >= $range['min'] && $usia <= $range['max'];
            })->count();

            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;

            $indikatorData[] = [
                'nama_indikator' => $range['label'],
                'jumlah'         => $jumlah,
                'persentase'     => $persentase,
            ];
        }

        // Tambahkan kategori "Tidak ada data tanggal lahir"
        $jumlahNoTanggalLahir = collect($getData)->filter(function ($item) {
            return !$item->tanggal_lahir;
        })->count();

        if ($jumlahNoTanggalLahir > 0) {
            $persentase      = $totalData > 0 ? round(($jumlahNoTanggalLahir / $totalData) * 100, 2) : 0;
            $indikatorData[] = [
                'nama_indikator' => 'Tidak ada data tanggal lahir',
                'jumlah'         => $jumlahNoTanggalLahir,
                'persentase'     => $persentase,
            ];
        }

        $result[] = [
            'key'  => 'usia',
            'name' => 'Kategori Usia',
            'data' => $indikatorData,
        ];

        // Lama Bergabung
        $lamaBergabungRanges = [
            'baru'        => ['min' => 0, 'max' => 1, 'label' => 'Baru bergabung (< 1 tahun)'],
            'sedang'      => ['min' => 1, 'max' => 3, 'label' => 'Sedang (1-3 tahun)'],
            'lama'        => ['min' => 3, 'max' => 5, 'label' => 'Lama (3-5 tahun)'],
            'sangat_lama' => ['min' => 5, 'max' => 100, 'label' => 'Sangat lama (5+ tahun)'],
        ];

        $indikatorData = [];
        foreach ($lamaBergabungRanges as $key => $range) {
            $jumlah = collect($getData)->filter(function ($item) use ($range) {
                if (!$item->tanggal_bergabung) {
                    return false;
                }

                $lamaBergabung = date_diff(date_create($item->tanggal_bergabung), date_create('today'))->y;
                return $lamaBergabung >= $range['min'] && $lamaBergabung <= $range['max'];
            })->count();

            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;

            $indikatorData[] = [
                'nama_indikator' => $range['label'],
                'jumlah'         => $jumlah,
                'persentase'     => $persentase,
            ];
        }

        // Tambahkan kategori "Tidak ada data tanggal bergabung"
        $jumlahNoTanggalBergabung = collect($getData)->filter(function ($item) {
            return !$item->tanggal_bergabung;
        })->count();

        if ($jumlahNoTanggalBergabung > 0) {
            $persentase      = $totalData > 0 ? round(($jumlahNoTanggalBergabung / $totalData) * 100, 2) : 0;
            $indikatorData[] = [
                'nama_indikator' => 'Tidak ada data tanggal bergabung',
                'jumlah'         => $jumlahNoTanggalBergabung,
                'persentase'     => $persentase,
            ];
        }

        $result[] = [
            'key'  => 'lama_bergabung',
            'name' => 'Lama Bergabung',
            'data' => $indikatorData,
        ];

        // Cabor (agregasi berdasarkan relasi cabor kategori -> cabor)
        try {
            $atletIds = collect($getData)->pluck('id')->filter()->values()->all();
            if (!empty($atletIds)) {
                $rows = DB::table('cabor_kategori_atlet as cka')
                    ->join('cabor as c', 'cka.cabor_id', '=', 'c.id')
                    ->whereNull('cka.deleted_at')
                    ->whereIn('cka.atlet_id', $atletIds)
                    ->select('c.id', 'c.nama', DB::raw('COUNT(DISTINCT cka.atlet_id) as jumlah'))
                    ->groupBy('c.id', 'c.nama')
                    ->orderBy('c.nama')
                    ->get();

                $indikatorData = [];
                foreach ($rows as $row) {
                    $jumlah          = (int) $row->jumlah;
                    $persentase      = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;
                    $indikatorData[] = [
                        'nama_indikator' => $row->nama ?? '-',
                        'jumlah'         => $jumlah,
                        'persentase'     => $persentase,
                    ];
                }

                $result[] = [
                    'key'  => 'cabor',
                    'name' => 'Cabor',
                    'data' => $indikatorData,
                ];
            } else {
                $result[] = [
                    'key'  => 'cabor',
                    'name' => 'Cabor',
                    'data' => [],
                ];
            }
        } catch (\Exception $e) {
            // Jika terjadi error agregasi, tetap kembalikan blok kosong agar UI tidak rusak
            $result[] = [
                'key'  => 'cabor',
                'name' => 'Cabor',
                'data' => [],
            ];
        }

        return $result;
    }
}
