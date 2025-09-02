<?php

namespace App\Repositories;

use App\Models\RencanaLatihan;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RencanaLatihanRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(RencanaLatihan $model)
    {
        $this->model = $model;
        $this->with  = ['programLatihan', 'targetLatihan', 'atlets', 'pelatihs', 'tenagaPendukung', 'created_by_user', 'updated_by_user'];
    }

    public function customIndex($data)
    {
        $query = $this->model
            ->with($this->with)
            ->withCount(['targetLatihan', 'atlets', 'pelatihs', 'tenagaPendukung']);
        if (request('program_latihan_id')) {
            $query->where('program_latihan_id', request('program_latihan_id'));
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('materi', 'like', "%$search%")
                    ->orWhere('lokasi_latihan', 'like', "%$search%")
                    ->orWhere('catatan', 'like', "%$search%");
            });
        }
        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'tanggal', 'lokasi_latihan', 'materi', 'created_at', 'updated_at'];
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
                return array_merge(
                    $item->toArray(),
                    [
                        'jumlah_target'           => $item->target_latihan_count,
                        'jumlah_atlet'            => $item->atlets_count,
                        'jumlah_pelatih'          => $item->pelatihs_count,
                        'jumlah_tenaga_pendukung' => $item->tenaga_pendukung_count,
                    ]
                );
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
            return array_merge(
                $item->toArray(),
                [
                    'jumlah_target'           => $item->target_latihan_count,
                    'jumlah_atlet'            => $item->atlets_count,
                    'jumlah_pelatih'          => $item->pelatihs_count,
                    'jumlah_tenaga_pendukung' => $item->tenaga_pendukung_count,
                ]
            );
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

    public function customDataCreateUpdate($data, $record = null)
    {
        $userId = Auth::id();
        if (is_null($record)) {
            $data['created_by'] = $userId;
        }
        $data['updated_by'] = $userId;

        return $data;
    }

    public function createWithRelations($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->model->create($data);
            if (! empty($data['target_latihan_ids'])) {
                $main->targetLatihan()->sync($data['target_latihan_ids']);
            }
            if (! empty($data['atlet_ids'])) {
                $main->atlets()->sync($data['atlet_ids']);
            }
            if (! empty($data['pelatih_ids'])) {
                $main->pelatihs()->sync($data['pelatih_ids']);
            }
            if (! empty($data['tenaga_pendukung_ids'])) {
                $main->tenagaPendukung()->sync($data['tenaga_pendukung_ids']);
            }
            DB::commit();

            return $main;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithRelations($id, $data)
    {
        DB::beginTransaction();
        try {
            $main = $this->model->findOrFail($id);
            $main->update($data);
            if (isset($data['target_latihan_ids'])) {
                $main->targetLatihan()->sync($data['target_latihan_ids']);
            }
            if (isset($data['atlet_ids'])) {
                $main->atlets()->sync($data['atlet_ids']);
            }
            if (isset($data['pelatih_ids'])) {
                $main->pelatihs()->sync($data['pelatih_ids']);
            }
            if (isset($data['tenaga_pendukung_ids'])) {
                $main->tenagaPendukung()->sync($data['tenaga_pendukung_ids']);
            }
            DB::commit();

            return $main;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete_selected(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getDetailWithRelations($id)
    {
        return $this->model->with($this->with)->findOrFail($id);
    }

    /**
     * Listing rencana latihan untuk mobile per program
     */
    public function getForMobile($request, int $programId)
    {
        $query = $this->model
            ->with(['targetLatihan'])
            ->withCount(['atlets', 'pelatihs', 'tenagaPendukung'])
            ->where('program_latihan_id', $programId);

        // Search by materi/lokasi/catatan
        if (!empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('materi', 'like', "%$search%")
                  ->orWhere('lokasi_latihan', 'like', "%$search%")
                  ->orWhere('catatan', 'like', "%$search%");
            });
        }

        // Filter by exact date (YYYY-MM-DD)
        if (!empty($request->date)) {
            $query->whereDate('tanggal', $request->date);
        }

        // Default sort by tanggal desc, then id desc
        $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        $perPage = (int) ($request->per_page ?? 10);
        $page    = (int) ($request->page ?? 1);

        $items = $query->paginate($perPage, ['*'], 'page', $page);

        $transformed = collect($items->items())->map(function ($item) {
            $targets = $item->targetLatihan->pluck('deskripsi')->toArray();
            $targetText = implode(', ', $targets);

            return [
                'id' => $item->id,
                'tanggal' => $item->tanggal,
                'materi' => $item->materi,
                'lokasi' => $item->lokasi_latihan,
                'catatan' => $item->catatan,
                'targetLatihan' => $targetText,
                'jumlah_atlet' => $item->atlets_count,
                'jumlah_pelatih' => $item->pelatihs_count,
                'jumlah_tenaga_pendukung' => $item->tenaga_pendukung_count,
                'total_peserta' => ($item->atlets_count + $item->pelatihs_count + $item->tenaga_pendukung_count),
            ];
        })->values();

        return [
            'data' => $transformed,
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage' => $items->perPage(),
            'search' => $request->search ?? '',
            'filters' => [
                'date' => $request->date ?? null,
            ],
        ];
    }

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }
}
