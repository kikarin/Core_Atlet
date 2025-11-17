<?php

namespace App\Repositories;

use App\Models\AtletParameterUmum;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AtletParameterUmumRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(AtletParameterUmum $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        Log::info('AtletParameterUmumRepository: create method called with data', $data);
        $data = $this->customDataCreateUpdate($data);

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        Log::info('AtletParameterUmumRepository: update method called with data', ['id' => $id, 'data' => $data]);
        $record = $this->model->find($id);

        if ($record) {
            $processedData = $this->customDataCreateUpdate($data, $record);
            $record->update($processedData);
            Log::info('AtletParameterUmumRepository: update method - record updated', $record->toArray());

            return $record;
        }

        Log::warning('AtletParameterUmumRepository: update method - record not found for update', ['id' => $id]);

        return null;
    }

    public function delete($id)
    {
        Log::info('AtletParameterUmumRepository: delete method called (hard delete)', ['id' => $id]);
        $record = $this->model->withTrashed()->find($id);

        if ($record) {
            $record->forceDelete();
            Log::info('AtletParameterUmumRepository: record successfully hard-deleted', ['id' => $id]);

            return true;
        }

        Log::warning('AtletParameterUmumRepository: record not found for deletion', ['id' => $id]);

        return false;
    }

    public function customDataCreateUpdate($data, $record = null)
    {
        Log::info('AtletParameterUmumRepository: customDataCreateUpdate method called', ['data_before_processing' => $data]);
        $userId = Auth::check() ? Auth::id() : null;

        if (is_null($record)) {
            $data['created_by'] = $userId;
        }
        $data['updated_by'] = $userId;

        Log::info('AtletParameterUmumRepository: customDataCreateUpdate method - data after processing', $data);

        return $data;
    }

    public function getByAtletId($atletId)
    {
        return $this->model->where('atlet_id', $atletId)->with('mstParameter')->get();
    }

    public function upsertByAtletId($atletId, array $parameterData)
    {
        // Delete existing
        $this->model->where('atlet_id', $atletId)->delete();

        // Insert new
        $userId = Auth::check() ? Auth::id() : null;
        foreach ($parameterData as $param) {
            if (isset($param['mst_parameter_id']) && isset($param['nilai']) && $param['nilai'] !== '') {
                $this->model->create([
                    'atlet_id'         => $atletId,
                    'mst_parameter_id' => $param['mst_parameter_id'],
                    'nilai'            => $param['nilai'],
                    'created_by'       => $userId,
                    'updated_by'       => $userId,
                ]);
            }
        }
    }
}
