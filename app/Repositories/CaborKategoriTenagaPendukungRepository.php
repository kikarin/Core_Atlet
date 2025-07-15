<?php

namespace App\Repositories;

use App\Models\CaborKategoriTenagaPendukung;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaborKategoriTenagaPendukungRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(CaborKategoriTenagaPendukung $model)
    {
        $this->model = $model;
        $this->with = [
            'cabor',
            'caborKategori',
            'tenagaPendukung',
            'jenisTenagaPendukung',
            'created_by_user',
            'updated_by_user',
        ];
    }

    public function create(array $data)
    {
        $data = $this->customDataCreateUpdate($data);
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $processedData = $this->customDataCreateUpdate($data, $record);
            $record->update($processedData);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        $record = $this->model->withTrashed()->find($id);
        if ($record) {
            $record->forceDelete();
            return true;
        }
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

    public function getByCaborKategoriId($caborKategoriId)
    {
        return $this->model->where('cabor_kategori_id', $caborKategoriId)->get();
    }

    public function getById($id)
    {
        return $this->model->with($this->with)->find($id);
    }

    public function customIndex($data)
    {
        $query = $this->model->with($this->with);
        if (request('cabor_kategori_id')) {
            $query->where('cabor_kategori_id', request('cabor_kategori_id'));
        }
        if (request('is_active') !== null) {
            $query->where('is_active', request('is_active'));
        }
        if (request('search')) {
            $search = request('search');
            $query->whereHas('tenagaPendukung', function ($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nik', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('no_hp', 'like', "%$search%")
                  ;
            });
        }
        if (request('sort')) {
            $order = request('order', 'asc');
            $sortField = request('sort');
            $validColumns = ['id', 'cabor_id', 'cabor_kategori_id', 'tenaga_pendukung_id', 'jenis_tenaga_pendukung_id', 'is_active', 'created_at', 'updated_at'];
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
                'records' => $transformed,
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
            'records' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => request('search', ''),
            'sort' => request('sort', ''),
            'order' => request('order', 'asc'),
        ];
        return $data;
    }

    public function batchInsert($data)
    {
        $userId = Auth::id();
        $insertData = [];
        foreach ($data['tenaga_pendukung_ids'] as $tpId) {
            $existing = $this->model->withTrashed()
                ->where('cabor_kategori_id', $data['cabor_kategori_id'])
                ->where('tenaga_pendukung_id', $tpId)
                ->first();
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->is_active = (int) $data['is_active'];
                $existing->jenis_tenaga_pendukung_id = $data['jenis_tenaga_pendukung_id'];
                $existing->cabor_id = $data['cabor_id'];
                $existing->updated_by = $userId;
                $existing->updated_at = now();
                $existing->save();
            } else {
                $insertData[] = [
                    'cabor_id' => $data['cabor_id'],
                    'cabor_kategori_id' => $data['cabor_kategori_id'],
                    'tenaga_pendukung_id' => $tpId,
                    'jenis_tenaga_pendukung_id' => $data['jenis_tenaga_pendukung_id'],
                    'is_active' => (int) $data['is_active'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($insertData)) {
            DB::table('cabor_kategori_tenaga_pendukung')->insert($insertData);
        }
        return true;
    }

    public function validateRequest($request)
{
    $rules = [
        'cabor_id' => 'required|exists:cabor,id',
        'cabor_kategori_id' => 'required|exists:cabor_kategori,id',
        'tenaga_pendukung_ids' => 'required|array|min:1',
        'tenaga_pendukung_ids.*' => 'required|exists:tenaga_pendukungs,id',
        'jenis_tenaga_pendukung_id' => 'required|exists:mst_jenis_tenaga_pendukung,id',
        'is_active' => 'required|boolean',
    ];

    $messages = [
        'cabor_id.required' => 'Cabor harus dipilih.',
        'cabor_id.exists' => 'Cabor tidak valid.',
        'cabor_kategori_id.required' => 'Kategori harus dipilih.',
        'cabor_kategori_id.exists' => 'Kategori tidak valid.',
        'tenaga_pendukung_ids.required' => 'Minimal satu tenaga pendukung harus dipilih.',
        'tenaga_pendukung_ids.array' => 'Format tenaga pendukung tidak valid.',
        'tenaga_pendukung_ids.min' => 'Pilih minimal satu tenaga pendukung.',
        'tenaga_pendukung_ids.*.required' => 'Tenaga pendukung tidak boleh kosong.',
        'tenaga_pendukung_ids.*.exists' => 'Tenaga pendukung yang dipilih tidak valid.',
        'jenis_tenaga_pendukung_id.required' => 'Jenis tenaga pendukung wajib dipilih.',
        'jenis_tenaga_pendukung_id.exists' => 'Jenis tenaga pendukung tidak valid.',
        'is_active.required' => 'Status aktif harus dipilih.',
        'is_active.boolean' => 'Status aktif tidak valid.',
    ];

    return $request->validate($rules, $messages);
}


    public function delete_selected(array $ids)
    {
        return $this->model->whereIn('id', $ids)->forceDelete();
    }
} 