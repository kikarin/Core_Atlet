<?php

namespace App\Repositories;

use App\Models\CaborKategoriAtlet;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CaborKategoriAtletRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(CaborKategoriAtlet $model)
    {
        $this->model = $model;
        $this->with = ['cabor', 'caborKategori', 'atlet', 'created_by_user', 'updated_by_user'];
    }

    public function customIndex($data)
    {
        $query = $this->model
            ->with(['cabor', 'caborKategori', 'atlet'])
            ->select('cabor_kategori_atlet.id', 'cabor_kategori_atlet.cabor_id', 'cabor_kategori_atlet.cabor_kategori_id', 'cabor_kategori_atlet.atlet_id', 'cabor_kategori_atlet.created_at');

        // Filter by cabor_kategori_id jika ada
        if (request('cabor_kategori_id')) {
            $query->where('cabor_kategori_atlet.cabor_kategori_id', request('cabor_kategori_id'));
        }

        // Search
        if (request('search')) {
            $search = request('search');
            $query->whereHas('atlet', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nik', 'like', '%' . $search . '%');
            });
        }

        // Sort
        if (request('sort')) {
            $order = request('order', 'asc');
            $sortField = request('sort');
            
            if ($sortField === 'atlet_nama') {
                $query->join('atlets', 'cabor_kategori_atlet.atlet_id', '=', 'atlets.id')
                    ->orderBy('atlets.nama', $order);
            } else {
                $validColumns = ['id', 'cabor_id', 'cabor_kategori_id', 'atlet_id', 'created_at'];
                if (in_array($sortField, $validColumns)) {
                    $query->orderBy('cabor_kategori_atlet.' . $sortField, $order);
                } else {
                    $query->orderBy('cabor_kategori_atlet.id', 'desc');
                }
            }
        } else {
            $query->orderBy('cabor_kategori_atlet.id', 'desc');
        }

        // Pagination
        $perPage = (int) request('per_page', 10);
        $page = (int) request('page', 1);
        
        if ($perPage === -1) {
            $allRecords = $query->get();
            $transformedRecords = collect($allRecords)->map(function ($record) {
                return [
                    'id' => $record->id,
                    'cabor_id' => $record->cabor_id,
                    'cabor_nama' => $record->cabor->nama ?? '-',
                    'cabor_kategori_id' => $record->cabor_kategori_id,
                    'cabor_kategori_nama' => $record->caborKategori->nama ?? '-',
                    'atlet_id' => $record->atlet_id,
                    'atlet_nama' => $record->atlet->nama ?? '-',
                    'atlet_nik' => $record->atlet->nik ?? '-',
                    'created_at' => $record->created_at,
                ];
            });
            
            $data += [
                'records' => $transformedRecords,
                'total' => $transformedRecords->count(),
                'currentPage' => 1,
                'perPage' => -1,
                'search' => request('search', ''),
                'sort' => request('sort', ''),
                'order' => request('order', 'asc'),
            ];
            return $data;
        }

        $pageForPaginate = $page < 1 ? 1 : $page;
        $records = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();

        $transformedRecords = collect($records->items())->map(function ($record) {
            return [
                'id' => $record->id,
                'cabor_id' => $record->cabor_id,
                'cabor_nama' => $record->cabor->nama ?? '-',
                'cabor_kategori_id' => $record->cabor_kategori_id,
                'cabor_kategori_nama' => $record->caborKategori->nama ?? '-',
                'atlet_id' => $record->atlet_id,
                'atlet_nama' => $record->atlet->nama ?? '-',
                'atlet_nik' => $record->atlet->nik ?? '-',
                'created_at' => $record->created_at,
            ];
        });

        $data += [
            'records' => $transformedRecords,
            'total' => $records->total(),
            'currentPage' => $records->currentPage(),
            'perPage' => $records->perPage(),
            'search' => request('search', ''),
            'sort' => request('sort', ''),
            'order' => request('order', 'asc'),
        ];

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

    public function batchInsert($data)
    {
        $userId = Auth::id();
        $insertData = [];

        // Restore data yang sudah soft deleted
        if (!empty($data['atlet_ids'])) {
            foreach ($data['atlet_ids'] as $atletId) {
                $restore = $this->model->withTrashed()
                    ->where('cabor_id', $data['cabor_id'])
                    ->where('cabor_kategori_id', $data['cabor_kategori_id'])
                    ->where('atlet_id', $atletId)
                    ->whereNotNull('deleted_at')
                    ->first();
                if ($restore) {
                    $restore->restore();
                }
            }
        }

        foreach ($data['atlet_ids'] as $atletId) {
            $insertData[] = [
                'cabor_id' => $data['cabor_id'],
                'cabor_kategori_id' => $data['cabor_kategori_id'],
                'atlet_id' => $atletId,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            DB::beginTransaction();
            // Insert batch dengan ignore untuk menghindari duplicate entry
            DB::table('cabor_kategori_atlet')->insertOrIgnore($insertData);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function getByCaborKategoriId($caborKategoriId)
    {
        return $this->model
            ->with(['cabor', 'caborKategori', 'atlet'])
            ->where('cabor_kategori_id', $caborKategoriId)
            ->get();
    }

    public function deleteByCaborKategoriId($caborKategoriId, $atletIds = [])
    {
        $query = $this->model->where('cabor_kategori_id', $caborKategoriId);
        
        if (!empty($atletIds)) {
            $query->whereIn('atlet_id', $atletIds);
        }
        
        return $query->delete();
    }

    public function validateRequest($request)
    {
        $rules = [
            'cabor_id' => 'required|exists:cabor,id',
            'cabor_kategori_id' => 'required|exists:cabor_kategori,id',
            'atlet_ids' => 'required|array|min:1',
            'atlet_ids.*' => 'required|exists:atlets,id',
        ];

        $messages = [
            'cabor_id.required' => 'Cabor harus dipilih.',
            'cabor_id.exists' => 'Cabor yang dipilih tidak valid.',
            'cabor_kategori_id.required' => 'Kategori harus dipilih.',
            'cabor_kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'atlet_ids.required' => 'Atlet harus dipilih minimal 1.',
            'atlet_ids.array' => 'Atlet harus berupa array.',
            'atlet_ids.min' => 'Atlet harus dipilih minimal 1.',
            'atlet_ids.*.required' => 'Atlet tidak boleh kosong.',
            'atlet_ids.*.exists' => 'Atlet yang dipilih tidak valid.',
        ];

        return $request->validate($rules, $messages);
    }
} 