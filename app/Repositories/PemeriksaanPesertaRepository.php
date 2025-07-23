<?php

namespace App\Repositories;

use App\Models\PemeriksaanPeserta;
use App\Traits\RepositoryTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pemeriksaan;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\TenagaPendukung;
use Illuminate\Support\Facades\Log;

class PemeriksaanPesertaRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(PemeriksaanPeserta $model)
    {
        $this->model = $model;
        $this->with  = ['pemeriksaan', 'status', 'peserta', 'created_by_user', 'updated_by_user'];
    }

    public function customIndex($data)
    {
        // Pastikan relasi peserta dimuat dengan benar
        $query = $this->model->with($this->with);

        // Log untuk debugging
        Log::info('customIndex called with data: ' . json_encode($data));
        Log::info('Request pemeriksaan_id: ' . request('pemeriksaan_id'));
        Log::info('Request jenis_peserta: ' . request('jenis_peserta'));

        if (request('pemeriksaan_id')) {
            $query->where('pemeriksaan_id', request('pemeriksaan_id'));
        }

        if (request('jenis_peserta')) {
            $jenis = request('jenis_peserta');
            $modelClass = match ($jenis) {
                'atlet' => Atlet::class,
                'pelatih' => Pelatih::class,
                'tenaga-pendukung' => TenagaPendukung::class,
                default => null,
            };
            if ($modelClass) {
                $query->where('peserta_type', $modelClass);
                Log::info('Filtering by peserta_type: ' . $modelClass);
            }
        }

        if (request('search')) {
            $search = request('search');
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }
        
        $query->orderBy('id', 'desc');

        $perPage = (int) request('per_page', 10);
        $page    = (int) request('page', 1);
        
        // Pastikan relasi peserta dimuat dengan benar
        $query->with(['peserta']);
        
        // Log query SQL untuk debugging
        Log::info('SQL Query: ' . $query->toSql());
        Log::info('SQL Bindings: ' . json_encode($query->getBindings()));
        
        $items = $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
        
        // Log hasil query untuk debugging
        Log::info('Query result count: ' . count($items));

        $transformed = collect($items->items())->map(function ($item) {
            // Pastikan relasi peserta dimuat dengan benar
            $peserta = $item->peserta;
            
            // Tambahkan logging untuk debugging
            Log::info('PemeriksaanPeserta ID: ' . $item->id);
            Log::info('Peserta Type: ' . $item->peserta_type);
            Log::info('Peserta ID: ' . $item->peserta_id);
            Log::info('Peserta Data: ' . json_encode($peserta));
            
            // Jika peserta null, coba muat ulang relasinya
            if (!$peserta) {
                Log::warning('Peserta is null, trying to reload relation');
                $item->load('peserta');
                $peserta = $item->peserta;
                Log::info('After reload - Peserta Data: ' . json_encode($peserta));
            }
            
            return [
                'id' => $item->id,
                'pemeriksaan_id' => $item->pemeriksaan_id,
                'peserta_type' => $item->peserta_type,
                'peserta_id' => $item->peserta_id,
                'peserta' => $peserta,
                'status' => $item->status,
                'catatan_umum' => $item->catatan_umum,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return [
            'data'        => $transformed,
            'total'       => $items->total(),
            'currentPage' => $items->currentPage(),
            'perPage'     => $items->perPage(),
            'search'      => request('search', ''),
            'sort'        => request('sort', ''),
            'order'       => request('order', 'asc'),
        ];
    }

    public function createMultiple(Pemeriksaan $pemeriksaan, array $data)
    {
        DB::beginTransaction();
        try {
            $this->createForType($pemeriksaan, $data, 'atlet_ids', Atlet::class);
            $this->createForType($pemeriksaan, $data, 'pelatih_ids', Pelatih::class);
            $this->createForType($pemeriksaan, $data, 'tenaga_pendukung_ids', TenagaPendukung::class);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createForType(Pemeriksaan $pemeriksaan, array $data, string $idKey, string $typeClass)
    {
        if (empty($data[$idKey])) {
            return;
        }

        $existingPesertaIds = PemeriksaanPeserta::where('pemeriksaan_id', $pemeriksaan->id)
            ->where('peserta_type', $typeClass)
            ->pluck('peserta_id')
            ->toArray();

        $newPesertaIds = array_diff($data[$idKey], $existingPesertaIds);

        $insertData = [];
        $now = now();
        $userId = Auth::id();

        foreach ($newPesertaIds as $pesertaId) {
            $insertData[] = [
                'pemeriksaan_id' => $pemeriksaan->id,
                'peserta_id' => $pesertaId,
                'peserta_type' => $typeClass,
                'ref_status_pemeriksaan_id' => $data['ref_status_pemeriksaan_id'],
                'catatan_umum' => $data['catatan_umum'],
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
        }

        if (!empty($insertData)) {
            PemeriksaanPeserta::insert($insertData);
        }
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

    public function getDetailWithRelations($id)
    {
        return $this->model->with($this->with)->findOrFail($id);
    }
}