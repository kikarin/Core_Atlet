<?php

namespace App\Repositories;

use App\Models\Atlet;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AtletOrangTuaRepository;

class AtletRepository
{
    use RepositoryTrait;

    protected $model;
    protected $atletOrangTuaRepository;

    public function __construct(Atlet $model, AtletOrangTuaRepository $atletOrangTuaRepository)
    {
        $this->model            = $model;
        $this->atletOrangTuaRepository = $atletOrangTuaRepository;
        $this->with = ['media', 'created_by_user', 'updated_by_user', 'atletOrangTua.created_by_user', 'atletOrangTua.updated_by_user'];
    }

    public function customIndex($data)
    {
        $query = $this->model->query();
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%" . $search . "%")
                  ->orWhere('nama', 'like', "%" . $search . "%")
                  ->orWhere('email', 'like', "%" . $search . "%")
                  ->orWhere('no_hp', 'like', "%" . $search . "%")
                  ->orWhere('jenis_kelamin', 'like', "%" . $search . "%")
                  ->orWhere('tempat_lahir', 'like', "%" . $search . "%")
                  ->orWhere('alamat', 'like', "%" . $search . "%")
                  ;
            });
        }
        if (request('sort')) {
            $order     = request('order', 'asc');
            $sortField = request('sort');
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
            $all = $query->get();
            $transformed = collect($all)->map(function ($item) {
                return $item->toArray();
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
        $transformed = collect($items->items())->map(function ($item) {
            return $item->toArray();
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
        
        Log::info('AtletRepository: customDataCreateUpdate', [
            'data' => $data,
            'method' => is_null($record) ? 'create' : 'update'
        ]);
        
        return $data;
    }

    public function callbackAfterStoreOrUpdate($model, $data, $method = 'store', $record_sebelumnya = null)
    {
        try {
            DB::beginTransaction();

            Log::info('AtletRepository: Starting file upload process', [
                'method' => $method,
                'has_file' => isset($data['file']),
                'file_data' => $data['file'] ? 'File exists' : 'No file',
                'is_delete_foto' => @$data['is_delete_foto']
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
                    'model_id' => $model->id
                ]);
                
                $media = $model->addMedia($data['file'])
                      ->usingName($data['nama'])
                      ->toMediaCollection('images');
                      
                Log::info('AtletRepository: Media added successfully', [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'disk' => $media->disk,
                    'path' => $media->getPath()
                ]);
            }

            // Handle AtletOrangTua data
            $atletOrangTuaData = [];
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

            if (!empty($atletOrangTuaData)) {
                $atletOrangTuaData['atlet_id'] = $model->id;

                if (isset($data['atlet_orang_tua_id']) && !is_null($data['atlet_orang_tua_id'])) {
                    $this->atletOrangTuaRepository->update($data['atlet_orang_tua_id'], $atletOrangTuaData);
                    Log::info('AtletRepository: Updated AtletOrangTua', ['id' => $data['atlet_orang_tua_id']]);
                } else {
                    $this->atletOrangTuaRepository->create($atletOrangTuaData);
                    Log::info('AtletRepository: Created new AtletOrangTua for atlet_id', ['atlet_id' => $model->id]);
                }
            }

            DB::commit();
            Log::info('AtletRepository: Transaction committed successfully');
            return $model;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('AtletRepository: Error during file upload or AtletOrangTua save', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
}
