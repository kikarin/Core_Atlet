<?php

namespace App\Repositories;

use App\Models\ProgramLatihan;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;

class ProgramLatihanRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(ProgramLatihan $model)
    {
        $this->model = $model;
        $this->with  = ['caborKategori', 'cabor', 'created_by_user', 'updated_by_user'];
    }

    public function customIndex($data)
    {
        $query = $this->model->with(['caborKategori', 'cabor'])
            ->withCount(['rencanaLatihan']);

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_program', 'like', "%$search%")
                    ->orWhere('keterangan', 'like', "%$search%");
            });
        }
        if (request('cabor_kategori_id')) {
            $query->where('cabor_kategori_id', request('cabor_kategori_id'));
        }
        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'nama_program', 'periode_mulai', 'periode_selesai', 'created_at', 'updated_at'];
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
                return [
                    'id'                     => $item->id,
                    'nama_program'           => $item->nama_program,
                    'cabor_kategori_id'      => $item->cabor_kategori_id,
                    'cabor_kategori_nama'    => $item->caborKategori?->nama,
                    'periode_mulai'          => $item->periode_mulai,
                    'periode_selesai'        => $item->periode_selesai,
                    'keterangan'             => $item->keterangan,
                    'jumlah_rencana_latihan' => $item->rencana_latihan_count,
                ];
            });
            $data += [
                'data'        => $transformed,
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
            return [
                'id'                             => $item->id,
                'cabor_id'                       => $item->cabor_id,
                'cabor_nama'                     => $item->cabor?->nama,
                'nama_program'                   => $item->nama_program,
                'cabor_kategori_id'              => $item->cabor_kategori_id,
                'cabor_kategori_nama'            => $item->caborKategori?->nama,
                'periode_mulai'                  => $item->periode_mulai,
                'periode_selesai'                => $item->periode_selesai,
                'keterangan'                     => $item->keterangan,
                'jumlah_target_individu'         => $item->targetLatihan()->where('jenis_target', 'individu')->count(),
                'jumlah_target_kelompok'         => $item->targetLatihan()->where('jenis_target', 'kelompok')->count(),
                'jumlah_target_atlet'            => $item->targetLatihan()->where('peruntukan', 'atlet')->count(),
                'jumlah_target_pelatih'          => $item->targetLatihan()->where('peruntukan', 'pelatih')->count(),
                'jumlah_target_tenaga_pendukung' => $item->targetLatihan()->where('peruntukan', 'tenaga-pendukung')->count(),
                'jumlah_rencana_latihan'         => $item->rencana_latihan_count,
            ];
        });
        $data += [
            'data'        => $transformed,
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

        return $data;
    }

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function getDetailWithRelations($id)
    {
        $with = array_merge($this->with, ['caborKategori', 'cabor']);

        return $this->model->with($with)->findOrFail($id);
    }
}
