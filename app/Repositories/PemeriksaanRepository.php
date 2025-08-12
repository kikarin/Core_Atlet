<?php

namespace App\Repositories;

use App\Http\Requests\PemeriksaanRequest;
use App\Models\Pemeriksaan;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;

class PemeriksaanRepository
{
    use RepositoryTrait;

    protected $model;

    protected $request;

    public function __construct(Pemeriksaan $model)
    {
        $this->model   = $model;
        $this->request = PemeriksaanRequest::createFromBase(request());
        $this->with    = [
            'cabor',
            'caborKategori',
            'tenagaPendukung',
            'created_by_user',
            'updated_by_user',
        ];
    }

    public function customIndex($data)
    {
        $query = $this->model->with($this->with)
            ->withCount([
                'pemeriksaanParameter as jumlah_parameter',
                'pemeriksaanPeserta as jumlah_peserta',
                'pemeriksaanPeserta as jumlah_atlet' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Atlet');
                },
                'pemeriksaanPeserta as jumlah_pelatih' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\Pelatih');
                },
                'pemeriksaanPeserta as jumlah_tenaga_pendukung' => function ($q) {
                    $q->where('peserta_type', 'App\\Models\\TenagaPendukung');
                },
            ]);

        $sortField = request('sort');
        $order     = request('order', 'asc');

        if ($sortField === 'cabor') {
            $query->join('cabor', 'pemeriksaan.cabor_id', '=', 'cabor.id')
                ->orderBy('cabor.nama', $order)
                ->select('pemeriksaan.*');
        } elseif ($sortField === 'cabor_kategori') {
            $query->join('cabor_kategori', 'pemeriksaan.cabor_kategori_id', '=', 'cabor_kategori.id')
                ->orderBy('cabor_kategori.nama', $order)
                ->select('pemeriksaan.*');
        } elseif ($sortField === 'tenaga_pendukung') {
            $query->join('tenaga_pendukungs', 'pemeriksaan.tenaga_pendukung_id', '=', 'tenaga_pendukungs.id')
                ->orderBy('tenaga_pendukungs.nama', $order)
                ->select('pemeriksaan.*');
        } else {
            // Sort by kolom di tabel pemeriksaan
            $validColumns = ['id', 'cabor_id', 'cabor_kategori_id', 'tenaga_pendukung_id', 'nama_pemeriksaan', 'tanggal_pemeriksaan', 'status', 'created_at', 'updated_at'];
            if (in_array($sortField, $validColumns)) {
                $query->orderBy($sortField, $order);
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        if (request('search')) {
            $search = request('search');
            $query->where('nama_pemeriksaan', 'like', "%$search%");
        }

        $auth = Auth::user();
        if ($auth->current_role_id == 35) {
            $query->where("caborKategori", function ($sub_query) use ($auth) {
                $sub_query->where("caborKategoriAtlet", function ($sub_sub_query) use ($auth) {
                    $sub_sub_query->where("atlet_id", $auth->atlet_id);
                });
            });
        }

        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);

        if ($perPage === -1) {
            $all         = $query->get();
            $transformed = collect($all)->map(function ($item) {
                return [
                    'id'                      => $item->id,
                    'cabor'                   => $item->cabor?->nama           ?? '-',
                    'cabor_kategori'          => $item->caborKategori?->nama   ?? '-',
                    'tenaga_pendukung'        => $item->tenagaPendukung?->nama ?? '-',
                    'nama_pemeriksaan'        => $item->nama_pemeriksaan,
                    'tanggal_pemeriksaan'     => $item->tanggal_pemeriksaan,
                    'status'                  => $item->status,
                    'jumlah_parameter'        => $item->jumlah_parameter        ?? 0,
                    'jumlah_peserta'          => $item->jumlah_peserta          ?? 0,
                    'jumlah_atlet'            => $item->jumlah_atlet            ?? 0,
                    'jumlah_pelatih'          => $item->jumlah_pelatih          ?? 0,
                    'jumlah_tenaga_pendukung' => $item->jumlah_tenaga_pendukung ?? 0,
                ];
            });
            $data += [
                'pemeriksaan' => $transformed,
                'total'       => $transformed->count(),
                'currentPage' => 1,
                'perPage'     => -1,
                'search'      => request('search', ''),
                'sort'        => request('sort', ''),
                'order'       => request('order', 'asc'),
            ];

            return $data;
        }
        $items       = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        $transformed = collect($items->items())->map(function ($item) {
            return [
                'id'                      => $item->id,
                'cabor'                   => $item->cabor?->nama           ?? '-',
                'cabor_kategori'          => $item->caborKategori?->nama   ?? '-',
                'tenaga_pendukung'        => $item->tenagaPendukung?->nama ?? '-',
                'nama_pemeriksaan'        => $item->nama_pemeriksaan,
                'tanggal_pemeriksaan'     => $item->tanggal_pemeriksaan,
                'status'                  => $item->status,
                'jumlah_parameter'        => $item->jumlah_parameter        ?? 0,
                'jumlah_peserta'          => $item->jumlah_peserta          ?? 0,
                'jumlah_atlet'            => $item->jumlah_atlet            ?? 0,
                'jumlah_pelatih'          => $item->jumlah_pelatih          ?? 0,
                'jumlah_tenaga_pendukung' => $item->jumlah_tenaga_pendukung ?? 0,
            ];
        });
        $data += [
            'pemeriksaan' => $transformed,
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
}
