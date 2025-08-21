<?php

namespace App\Repositories;

use App\Models\TenagaPendukung;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Role;

class TenagaPendukungRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(TenagaPendukung $model)
    {
        $this->model = $model;
        $this->with  = [
            'media',
            'created_by_user',
            'updated_by_user',
            'user',
            'sertifikat',
            'sertifikat.media',
            'sertifikat.created_by_user',
            'sertifikat.updated_by_user',
            'prestasi',
            'prestasi.created_by_user',
            'prestasi.updated_by_user',
            'kesehatan',
            'kesehatan.created_by_user',
            'kesehatan.updated_by_user',
            'dokumen',
            'dokumen.created_by_user',
            'dokumen.updated_by_user',
            'dokumen.jenis_dokumen',
        ];
    }

    public function customIndex($data)
    {
        $query = $this->model->query();
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
                return $item->toArray();
            });
            $data += [
                'tenaga_pendukungs' => $transformed,
                'total'             => $transformed->count(),
                'currentPage'       => 1,
                'perPage'           => -1,
                'search'            => request('search', ''),
                'sort'              => request('sort', ''),
                'order'             => request('order', 'asc'),
            ];

            return $data;
        }
        $pageForPaginate = $page < 1 ? 1 : $page;
        $items           = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();
        $transformed     = collect($items->items())->map(function ($item) {
            return $item->toArray();
        });
        $data += [
            'tenaga_pendukungs' => $transformed,
            'total'             => $items->total(),
            'currentPage'       => $items->currentPage(),
            'perPage'           => $items->perPage(),
            'search'            => request('search', ''),
            'sort'              => request('sort', ''),
            'order'             => request('order', 'asc'),
        ];

        return $data;
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
        Log::info('TenagaPendukungRepository: customDataCreateUpdate', [
            'data'   => $data,
            'method' => is_null($record) ? 'create' : 'update',
        ]);

        return $data;
    }

    public function callbackAfterStoreOrUpdate($model, $data, $method = 'store', $record_sebelumnya = null)
    {
        try {
            DB::beginTransaction();
            Log::info('TenagaPendukungRepository: Starting file upload process', [
                'method'         => $method,
                'has_file'       => isset($data['file']),
                'file_data'      => $data['file'] ? 'File exists' : 'No file',
                'is_delete_foto' => @$data['is_delete_foto'],
            ]);
            if (@$data['is_delete_foto'] == 1) {
                $model->clearMediaCollection('images');
                Log::info('TenagaPendukungRepository: Cleared media collection');
            }
            if (@$data['file']) {
                Log::info('TenagaPendukungRepository: Adding media file', [
                    'file_name' => $data['file']->getClientOriginalName(),
                    'file_size' => $data['file']->getSize(),
                    'model_id'  => $model->id,
                ]);
                $media = $model->addMedia($data['file'])
                    ->usingName($data['nama'])
                    ->toMediaCollection('images');
                Log::info('TenagaPendukungRepository: Media added successfully', [
                    'media_id'  => $media->id,
                    'file_name' => $media->file_name,
                    'disk'      => $media->disk,
                    'path'      => $media->getPath(),
                ]);
            }
            DB::commit();
            Log::info('TenagaPendukungRepository: Transaction committed successfully');

            return $model;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('TenagaPendukungRepository: Error during file upload or other save operations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
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
     * Handle Tenaga Pendukung Akun creation/update
     */
    public function handleTenagaPendukungAkun($tenagaPendukung, $data)
    {
        $userId   = Auth::check() ? Auth::id() : null;
        $userData = [
            'name'            => $tenagaPendukung->nama,
            'email'           => $data['akun_email'],
            'no_hp'           => $tenagaPendukung->no_hp,
            'is_active'       => 1,
            'current_role_id' => 37,
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
                $role = Role::find(37); // Role Tenaga Pendukung
                if ($role && !$user->hasRole($role)) {
                    $user->assignRole($role);
                }

                Log::info('TenagaPendukungRepository: Updated existing user for tenaga pendukung', [
                    'tenaga_pendukung_id' => $tenagaPendukung->id,
                    'user_id'             => $user->id,
                ]);
            }
        } else {
            // Create new user
            $user = User::create($userData);

            // Assign role Tenaga Pendukung using Spatie Permission
            $role = Role::find(37); // Role Tenaga Pendukung
            if ($role) {
                $user->assignRole($role);
            }

            // Also create users_role record for compatibility
            $user->users_role()->create([
                'users_id'   => $user->id,
                'role_id'    => 37,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $tenagaPendukung->update(['users_id' => $user->id]);

            Log::info('TenagaPendukungRepository: Created new user for tenaga pendukung', [
                'tenaga_pendukung_id' => $tenagaPendukung->id,
                'user_id'             => $user->id,
            ]);
        }
    }

    /**
     * Get jumlah karakteristik tenaga pendukung
     */
    public function jumlah_karakteristik($data = [])
    {
        $tanggal_awal  = $data['tanggal_awal'] ?? null;
        $tanggal_akhir = $data['tanggal_akhir'] ?? null;

        // Ambil semua data yang akan direkap
        $this->with = [];
        $getData = $this->getAll([
            "filter_start_date" => $tanggal_awal,
            "filter_end_date"   => $tanggal_akhir,
        ]);
        $totalData = count($getData); // total keseluruhan

        $result = [];

        // Jenis Kelamin
        $listIndikator = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
        $listIndikator['NULL'] = "-"; 

        $indikatorData = [];
        foreach ($listIndikator as $key => $value) {
            $jumlah = collect($getData)->filter(function($item) use ($key) {
                $key_value = $item->jenis_kelamin ?? null;
        
                if ($key === 'NULL') {
                    return is_null($key_value);
                }
        
                return $key_value == $key;
            })->count();
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;
    
            $indikatorData[] = [
                "nama_indikator" => $value,
                "jumlah"         => $jumlah,
                "persentase"     => $persentase,
            ];
        }
    
        $result[] = [
            "key"  => "jenis_kelamin",
            "name" => "Jenis Kelamin",
            "data" => $indikatorData,
        ];

        // Status Aktif
        $listIndikator = [1 => 'Aktif', 0 => 'Nonaktif'];
        $listIndikator['NULL'] = "-"; 
    
        $indikatorData = [];
        foreach ($listIndikator as $key => $value) {
            $jumlah = collect($getData)->filter(function($item) use ($key) {
                $key_value = $item->is_active ?? null;
        
                if ($key === 'NULL') {
                    return is_null($key_value);
                }
        
                return $key_value == $key;
            })->count();
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;
    
            $indikatorData[] = [
                "nama_indikator" => $value,
                "jumlah"         => $jumlah,
                "persentase"     => $persentase,
            ];
        }
    
        $result[] = [
            "key"  => "status_aktif",
            "name" => "Status Aktif",
            "data" => $indikatorData,
        ];

        // Usia (dibagi berdasarkan range)
        $usiaRanges = [
            'dewasa_muda' => ['min' => 18, 'max' => 25, 'label' => 'Dewasa Muda (18-25 tahun)'],
            'dewasa' => ['min' => 26, 'max' => 35, 'label' => 'Dewasa (26-35 tahun)'],
            'dewasa_tua' => ['min' => 36, 'max' => 45, 'label' => 'Dewasa Tua (36-45 tahun)'],
            'senior' => ['min' => 46, 'max' => 55, 'label' => 'Senior (46-55 tahun)'],
            'veteran' => ['min' => 56, 'max' => 100, 'label' => 'Veteran (56+ tahun)'],
        ];

        $indikatorData = [];
        foreach ($usiaRanges as $key => $range) {
            $jumlah = collect($getData)->filter(function($item) use ($range) {
                if (!$item->tanggal_lahir) return false;
                
                $usia = date_diff(date_create($item->tanggal_lahir), date_create('today'))->y;
                return $usia >= $range['min'] && $usia <= $range['max'];
            })->count();
            
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;
    
            $indikatorData[] = [
                "nama_indikator" => $range['label'],
                "jumlah"         => $jumlah,
                "persentase"     => $persentase,
            ];
        }

        // Tambahkan kategori "Tidak ada data tanggal lahir"
        $jumlahNoTanggalLahir = collect($getData)->filter(function($item) {
            return !$item->tanggal_lahir;
        })->count();
        
        if ($jumlahNoTanggalLahir > 0) {
            $persentase = $totalData > 0 ? round(($jumlahNoTanggalLahir / $totalData) * 100, 2) : 0;
            $indikatorData[] = [
                "nama_indikator" => "Tidak ada data tanggal lahir",
                "jumlah"         => $jumlahNoTanggalLahir,
                "persentase"     => $persentase,
            ];
        }
    
        $result[] = [
            "key"  => "usia",
            "name" => "Kategori Usia",
            "data" => $indikatorData,
        ];

        // Lama Bergabung
        $lamaBergabungRanges = [
            'baru' => ['min' => 0, 'max' => 2, 'label' => 'Baru bergabung (< 2 tahun)'],
            'sedang' => ['min' => 2, 'max' => 5, 'label' => 'Sedang (2-5 tahun)'],
            'lama' => ['min' => 5, 'max' => 10, 'label' => 'Lama (5-10 tahun)'],
            'sangat_lama' => ['min' => 10, 'max' => 100, 'label' => 'Sangat lama (10+ tahun)'],
        ];

        $indikatorData = [];
        foreach ($lamaBergabungRanges as $key => $range) {
            $jumlah = collect($getData)->filter(function($item) use ($range) {
                if (!$item->tanggal_bergabung) return false;
                
                $lamaBergabung = date_diff(date_create($item->tanggal_bergabung), date_create('today'))->y;
                return $lamaBergabung >= $range['min'] && $lamaBergabung <= $range['max'];
            })->count();
            
            $persentase = $totalData > 0 ? round(($jumlah / $totalData) * 100, 2) : 0;
    
            $indikatorData[] = [
                "nama_indikator" => $range['label'],
                "jumlah"         => $jumlah,
                "persentase"     => $persentase,
            ];
        }

        // Tambahkan kategori "Tidak ada data tanggal bergabung"
        $jumlahNoTanggalBergabung = collect($getData)->filter(function($item) {
            return !$item->tanggal_bergabung;
        })->count();
        
        if ($jumlahNoTanggalBergabung > 0) {
            $persentase = $totalData > 0 ? round(($jumlahNoTanggalBergabung / $totalData) * 100, 2) : 0;
            $indikatorData[] = [
                "nama_indikator" => "Tidak ada data tanggal bergabung",
                "jumlah"         => $jumlahNoTanggalBergabung,
                "persentase"     => $persentase,
            ];
        }
    
        $result[] = [
            "key"  => "lama_bergabung",
            "name" => "Lama Bergabung",
            "data" => $indikatorData,
        ];

        return $result;
    }
}
