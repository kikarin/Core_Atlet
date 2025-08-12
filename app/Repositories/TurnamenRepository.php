<?php

namespace App\Repositories;

use App\Http\Requests\TurnamenRequest;
use App\Models\Turnamen;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TurnamenRepository
{
    use RepositoryTrait;

    protected $model;

    protected $request;

    public function __construct(Turnamen $model)
    {
        $this->model   = $model;
        $this->request = TurnamenRequest::createFromBase(request());
        $this->with    = ['created_by_user', 'updated_by_user', 'caborKategori', 'tingkat', 'juara'];
    }

    public function customIndex($data)
    {
        $query = $this->model->select('id', 'nama', 'cabor_kategori_id', 'tanggal_mulai', 'tanggal_selesai', 'tingkat_id', 'lokasi', 'juara_id', 'hasil', 'evaluasi');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%'.$search.'%')
                  ->orWhere('lokasi', 'like', '%'.$search.'%')
                  ->orWhere('hasil', 'like', '%'.$search.'%');
            });
        }

        if (request('sort')) {
            $order        = request('order', 'asc');
            $sortField    = request('sort');
            $validColumns = ['id', 'nama', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'created_at', 'updated_at'];
            if (in_array($sortField, $validColumns)) {
                $query->orderBy($sortField, $order);
            } else {
                $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

                $auth = Auth::user();
        if ($auth->current_role_id == 35) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriAtlet", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("atlet_id", $auth->atlet->id);
                });
            });
        }

        $auth = Auth::user();
        if ($auth->current_role_id == 36) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("caborKategoriPelatih", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("pelatih_id", $auth->pelatih->id);
                });
            });
        }

        $auth = Auth::user();
        if ($auth->current_role_id == 37) {
            $query->whereHas("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->whereHas("tenagaPendukung", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("tenaga_pendukung_id", $auth->tenagaPendukung->id);
                });
            });
        }


        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);

        if ($perPage === -1) {
            $allData         = $query->get();
            $transformedData = $allData->map(function ($item) {
                $pesertaCounts = $this->getPesertaCount($item->id);
                return [
                    'id'                  => $item->id,
                    'nama'                => $item->nama,
                    'cabor_kategori_id'   => $item->cabor_kategori_id,
                    'cabor_kategori_nama' => $item->caborKategori ? $item->caborKategori->cabor->nama . ' - ' . $item->caborKategori->nama : '-',
                    'tanggal_mulai'       => $item->tanggal_mulai,
                    'tanggal_selesai'     => $item->tanggal_selesai,
                    'tingkat_id'          => $item->tingkat_id,
                    'tingkat_nama'        => $item->tingkat ? $item->tingkat->nama : '-',
                    'lokasi'              => $item->lokasi,
                    'juara_id'            => $item->juara_id,
                    'juara_nama'          => $item->juara ? $item->juara->nama : '-',
                    'hasil'               => $item->hasil,
                    'evaluasi'            => $item->evaluasi,
                    'peserta_counts'      => $pesertaCounts,
                ];
            });
            $data += [
                'turnamens'     => $transformedData,
                'total'         => $transformedData->count(),
                'currentPage'   => 1,
                'perPage'       => -1,
                'search'        => request('search', ''),
                'sort'          => request('sort', ''),
                'order'         => request('order', 'asc'),
            ];

            return $data;
        }

        $pageForPaginate = $page < 1 ? 1 : $page;
        $items           = $query->paginate($perPage, ['*'], 'page', $pageForPaginate)->withQueryString();

        $transformedData = collect($items->items())->map(function ($item) {
            $pesertaCounts = $this->getPesertaCount($item->id);
            return [
                'id'                  => $item->id,
                'nama'                => $item->nama,
                'cabor_kategori_id'   => $item->cabor_kategori_id,
                'cabor_kategori_nama' => $item->caborKategori ? $item->caborKategori->cabor->nama . ' - ' . $item->caborKategori->nama : '-',
                'tanggal_mulai'       => $item->tanggal_mulai,
                'tanggal_selesai'     => $item->tanggal_selesai,
                'tingkat_id'          => $item->tingkat_id,
                'tingkat_nama'        => $item->tingkat ? $item->tingkat->nama : '-',
                'lokasi'              => $item->lokasi,
                'juara_id'            => $item->juara_id,
                'juara_nama'          => $item->juara ? $item->juara->nama : '-',
                'hasil'               => $item->hasil,
                'evaluasi'            => $item->evaluasi,
                'peserta_counts'      => $pesertaCounts,
            ];
        });

        $data += [
            'turnamens'     => $transformedData,
            'total'         => $items->total(),
            'currentPage'   => $items->currentPage(),
            'perPage'       => $items->perPage(),
            'search'        => request('search', ''),
            'sort'          => request('sort', ''),
            'order'         => request('order', 'asc'),
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

    public function delete_selected(array $ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getDetailWithUserTrack($id)
    {
        return $this->model
            ->with(['created_by_user', 'updated_by_user', 'caborKategori.cabor', 'tingkat', 'juara'])
            ->where('id', $id)
            ->first();
    }

    public function handleShow($id)
    {
        $item = $this->getDetailWithUserTrack($id);

        if (! $item) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        $itemArray = $item->toArray();

        return Inertia::render('modules/turnamen/Show', [
            'item' => $itemArray,
        ]);
    }

    public function validateRequest($request)
    {
        $rules    = method_exists($request, 'rules') ? $request->rules() : [];
        $messages = method_exists($request, 'messages') ? $request->messages() : [];

        return $request->validate($rules, $messages);
    }

    public function syncPeserta($turnamenId, $pesertaData)
    {
        $turnamen = $this->model->find($turnamenId);

        if (!$turnamen) {
            return false;
        }

        if (isset($pesertaData['atlet_ids'])) {
            $turnamen->peserta()->sync($pesertaData['atlet_ids']);
        }

        if (isset($pesertaData['pelatih_ids'])) {
            $turnamen->pelatihPeserta()->sync($pesertaData['pelatih_ids']);
        }

        if (isset($pesertaData['tenaga_pendukung_ids'])) {
            $turnamen->tenagaPendukungPeserta()->sync($pesertaData['tenaga_pendukung_ids']);
        }

        return true;
    }

    public function getPesertaCount($turnamenId)
    {
        $turnamen = $this->model->with(['peserta', 'pelatihPeserta', 'tenagaPendukungPeserta'])->find($turnamenId);

        if (!$turnamen) {
            return ['atlet' => 0, 'pelatih' => 0, 'tenaga_pendukung' => 0];
        }

        return [
            'atlet'            => $turnamen->peserta->count(),
            'pelatih'          => $turnamen->pelatihPeserta->count(),
            'tenaga_pendukung' => $turnamen->tenagaPendukungPeserta->count(),
        ];
    }
}
