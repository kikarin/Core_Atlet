<?php

namespace App\Repositories;

use App\Models\TenagaPendukung;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenagaPendukungRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(TenagaPendukung $model)
    {
        $this->model = $model;
        $this->with = [
            'media',
            'created_by_user',
            'updated_by_user',
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
            $order = request('order', 'asc');
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
        $page = (int) request('page', 1);
        if ($perPage === -1) {
            $all = $query->get();
            $transformed = collect($all)->map(function ($item) {
                return $item->toArray();
            });
            $data += [
                'tenaga_pendukungs' => $transformed,
                'total' => $transformed->count(),
                'currentPage' => 1,
                'perPage' => -1,
                'search' => request('search', ''),
                'sort' => request('sort', ''),
                'order' => request('order', 'asc'),
            ];

            return $data;
        }
        $pageForPaginate = $page < 1 ? 1 : $page;
        $items = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return $item->toArray();
        });
        $data += [
            'tenaga_pendukungs' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => request('search', ''),
            'sort' => request('sort', ''),
            'order' => request('order', 'asc'),
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
            'data' => $data,
            'method' => is_null($record) ? 'create' : 'update',
        ]);

        return $data;
    }

    public function callbackAfterStoreOrUpdate($model, $data, $method = 'store', $record_sebelumnya = null)
    {
        try {
            DB::beginTransaction();
            Log::info('TenagaPendukungRepository: Starting file upload process', [
                'method' => $method,
                'has_file' => isset($data['file']),
                'file_data' => $data['file'] ? 'File exists' : 'No file',
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
                    'model_id' => $model->id,
                ]);
                $media = $model->addMedia($data['file'])
                    ->usingName($data['nama'])
                    ->toMediaCollection('images');
                Log::info('TenagaPendukungRepository: Media added successfully', [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'disk' => $media->disk,
                    'path' => $media->getPath(),
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
        $rules = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function getDetailWithRelations($id)
    {
        $with = $this->with;

        return $this->model->with($with)->findOrFail($id);
    }
}
