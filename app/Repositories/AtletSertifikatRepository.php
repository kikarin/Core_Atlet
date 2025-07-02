<?php

namespace App\Repositories;

use App\Models\AtletSertifikat;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AtletSertifikatRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(AtletSertifikat $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        Log::info('AtletSertifikatRepository: create', $data);
        $file = $data['file'] ?? null;
        unset($data['file']);
        $data = $this->customDataCreateUpdate($data);
        $model = $this->model->create($data);
        if ($file) {
            $model->addMedia($file)->usingName($data['nama_sertifikat'] ?? 'Sertifikat')->toMediaCollection('sertifikat_file');
        }
        return $model;
    }

    public function update($id, array $data)
    {
        Log::info('AtletSertifikatRepository: update', ['id' => $id, 'data' => $data]);
        $record = $this->model->find($id);
        if ($record) {
            $file = $data['file'] ?? null;
            unset($data['file']);
            $processedData = $this->customDataCreateUpdate($data, $record);
            $record->update($processedData);
            if ($file) {
                $record->clearMediaCollection('sertifikat_file');
                $record->addMedia($file)->usingName($data['nama_sertifikat'] ?? 'Sertifikat')->toMediaCollection('sertifikat_file');
            }
            Log::info('AtletSertifikatRepository: updated', $record->toArray());
            return $record;
        }
        Log::warning('AtletSertifikatRepository: not found for update', ['id' => $id]);
        return null;
    }

    public function delete($id)
    {
        Log::info('AtletSertifikatRepository: delete', ['id' => $id]);
        $record = $this->model->withTrashed()->find($id);
        if ($record) {
            $record->forceDelete();
            Log::info('AtletSertifikatRepository: deleted', ['id' => $id]);
            return true;
        }
        Log::warning('AtletSertifikatRepository: not found for delete', ['id' => $id]);
        return false;
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

    public function getByAtletId($atletId)
    {
        return $this->model->where('atlet_id', $atletId)->get();
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }
} 