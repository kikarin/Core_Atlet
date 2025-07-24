<?php

namespace App\Repositories;

use App\Models\PemeriksaanPesertaParameter;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;

class PemeriksaanPesertaParameterRepository
{
    use RepositoryTrait;
    protected $model;

    public function __construct(PemeriksaanPesertaParameter $model)
    {
        $this->model = $model;
        $this->with  = [
            'pemeriksaan',
            'pemeriksaanPeserta',
            'pemeriksaanParameter'
            ];
    }

    public function customIndex($data)
    {
        $query = $this->model->with($this->with);
        if (request('pemeriksaan_id')) {
            $query->where('pemeriksaan_id', request('pemeriksaan_id'));
        }
        if (request('pemeriksaan_peserta_id')) {
            $query->where('pemeriksaan_peserta_id', request('pemeriksaan_peserta_id'));
        }
        if (request('search')) {
            $search = request('search');
            $query->whereHas('pemeriksaanParameter', function ($q) use ($search) {
                $q->where('nama_parameter', 'like', "%$search%");
            });
        }
        $query->orderBy('id', 'desc');
        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);
        $items   = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'pemeriksaan_id' => $item->pemeriksaan_id,
                'pemeriksaan_peserta_id' => $item->pemeriksaan_peserta_id,
                'pemeriksaan_parameter_id' => $item->pemeriksaan_parameter_id,
                'parameter' => $item->pemeriksaanParameter?->nama_parameter ?? '-',
                'nilai' => $item->nilai,
                'trend' => $item->trend,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });
        $data += [
            'data'        => $transformed,
            'total'       => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage'     => $items->perPage(),
            'search'      => request('search', ''),
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

    public function getDetailWithRelations($id)
    {
        return $this->model->with($this->with)->findOrFail($id);
    }
} 