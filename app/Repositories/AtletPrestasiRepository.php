<?php

namespace App\Repositories;

use App\Models\AtletPrestasi;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AtletPrestasiRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(AtletPrestasi $model)
    {
        $this->model = $model;
        $this->with = [
            'created_by_user',
            'updated_by_user',
            'tingkat', 
        ];
    }

    public function create(array $data)
    {
        Log::info('AtletPrestasiRepository: create', $data);
        $data = $this->customDataCreateUpdate($data);
        $model = $this->model->create($data);
        return $model;
    }

    public function update($id, array $data)
    {
        Log::info('AtletPrestasiRepository: update', ['id' => $id, 'data' => $data]);
        $record = $this->model->find($id);
        if ($record) {
            $processedData = $this->customDataCreateUpdate($data, $record);
            $record->update($processedData);
            Log::info('AtletPrestasiRepository: updated', $record->toArray());
            return $record;
        }
        Log::warning('AtletPrestasiRepository: not found for update', ['id' => $id]);
        return null;
    }

    public function delete($id)
    {
        Log::info('AtletPrestasiRepository: delete', ['id' => $id]);
        $record = $this->model->withTrashed()->find($id);
        if ($record) {
            $record->forceDelete();
            Log::info('AtletPrestasiRepository: deleted', ['id' => $id]);
            return true;
        }
        Log::warning('AtletPrestasiRepository: not found for delete', ['id' => $id]);
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
        return $this->model->with($this->with)->find($id);
    }

    public function apiIndex($atletId)
    {
        $query = $this->model->where('atlet_id', $atletId);

        // Search
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_event', 'like', "%$search%")
                  ->orWhere('peringkat', 'like', "%$search%")
                  ->orWhere('keterangan', 'like', "%$search%")
                  ->orWhere('tanggal', 'like', "%$search%")
                  ;
            });
        }
        // Sort
        if (request('sort')) {
            $order = request('order', 'asc');
            $sortField = request('sort');
            $validColumns = ['id', 'nama_event', 'tingkat_id', 'tanggal', 'peringkat', 'created_at', 'updated_at'];
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
            $all = $query->with($this->with)->get();
            $transformed = collect($all)->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_event' => $item->nama_event,
                    'tingkat' => $item->tingkat ? ['id' => $item->tingkat->id, 'nama' => $item->tingkat->nama] : null,
                    'tanggal' => $item->tanggal,
                    'peringkat' => $item->peringkat,
                    'keterangan' => $item->keterangan,
                ];
            });
            return [
                'data' => $transformed,
                'meta' => [
                    'total' => $transformed->count(),
                    'current_page' => 1,
                    'per_page' => -1,
                    'search' => request('search', ''),
                    'sort' => request('sort', ''),
                    'order' => request('order', 'asc'),
                ],
            ];
        }
        $pageForPaginate = $page < 1 ? 1 : $page;
        $items = $query->with($this->with)->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_event' => $item->nama_event,
                'tingkat' => $item->tingkat ? ['id' => $item->tingkat->id, 'nama' => $item->tingkat->nama] : null,
                'tanggal' => $item->tanggal,
                'peringkat' => $item->peringkat,
                'keterangan' => $item->keterangan,
            ];
        });
        return [
            'data' => $transformed,
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'search' => request('search', ''),
                'sort' => request('sort', ''),
                'order' => request('order', 'asc'),
            ],
        ];
    }

    public function handleCreate($atletId)
    {
        return Inertia::render('modules/atlet/prestasi/Create', [
            'atletId' => (int) $atletId,
        ]);
    }

    public function handleEdit($atletId, $id)
    {
        $prestasi = $this->getById($id);
        if (!$prestasi) {
            return redirect()->back()->with('error', 'Prestasi tidak ditemukan');
        }
        
        return Inertia::render('modules/atlet/prestasi/Edit', [
            'atletId' => (int) $atletId,
            'item' => $prestasi,
        ]);
    }

    public function delete_selected(array $ids)
    {
        return $this->model->whereIn('id', $ids)->forceDelete();
    }
} 