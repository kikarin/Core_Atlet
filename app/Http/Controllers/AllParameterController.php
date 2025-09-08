<?php

namespace App\Http\Controllers;

use App\Models\MstParameter;
use App\Models\PemeriksaanParameter;
use App\Repositories\MstParameterRepository;
use App\Traits\BaseTrait;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Models\Pemeriksaan;
use Illuminate\Support\Facades\DB;

class AllParameterController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    public function __construct(MstParameterRepository $repository)
    {
        $this->repository = $repository;
        $this->initialize();
        $this->route = 'all-parameter';
    }

    public static function middleware(): array
    {
        return [];
    }

    public function index()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }

        // Get all unique parameters that are used in pemeriksaan
        $parameters = MstParameter::whereHas('pemeriksaanParameters')
            ->withCount('pemeriksaanParameters')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                 => $item->id,
                    'nama'               => $item->nama,
                    'satuan'             => $item->satuan,
                    'jumlah_pemeriksaan' => $item->pemeriksaan_parameters_count,
                ];
            });

        $data['parameters'] = $parameters;

        return inertia('modules/all-parameter/Index', $data);
    }

    public function show($parameterId)
    {
        $parameter = MstParameter::findOrFail($parameterId);

        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }

        $data['parameter'] = [
            'id'     => $parameter->id,
            'nama'   => $parameter->nama,
            'satuan' => $parameter->satuan,
        ];

        return inertia('modules/all-parameter/Statistik', $data);
    }

    public function chart($parameterId)
    {
        $parameter = MstParameter::findOrFail($parameterId);

        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }

        $data['parameter'] = [
            'id'     => $parameter->id,
            'nama'   => $parameter->nama,
            'satuan' => $parameter->satuan,
        ];

        return inertia('modules/all-parameter/Chart', $data);
    }

    public function apiIndex()
    {
        // Get all unique parameters that are used in pemeriksaan
        $parameters = MstParameter::whereHas('pemeriksaanParameters')
            ->withCount('pemeriksaanParameters')
            ->get()
            ->map(function ($item) {
                return [
                    'id'                 => $item->id,
                    'nama'               => $item->nama,
                    'satuan'             => $item->satuan,
                    'jumlah_pemeriksaan' => $item->pemeriksaan_parameters_count,
                ];
            });

        return response()->json([
            'data'  => $parameters,
            'total' => $parameters->count(),
        ]);
    }

    public function apiStatistik($parameterId)
    {
        $parameter = MstParameter::findOrFail($parameterId);

        // Get all pemeriksaan that use this parameter
        $pemeriksaanIds = PemeriksaanParameter::where('mst_parameter_id', $parameterId)
            ->pluck('pemeriksaan_id')
            ->unique();

        // Get all pemeriksaan with their details
        $pemeriksaanList = Pemeriksaan::whereIn('id', $pemeriksaanIds)
            ->select('id', 'nama_pemeriksaan', 'tanggal_pemeriksaan')
            ->get();

        // Get all statistik data for this parameter across all pemeriksaan
        $statistikData = DB::table('pemeriksaan_peserta_parameter')
            ->join('pemeriksaan_peserta', 'pemeriksaan_peserta_parameter.pemeriksaan_peserta_id', '=', 'pemeriksaan_peserta.id')
            ->join('pemeriksaan_parameter', 'pemeriksaan_peserta_parameter.pemeriksaan_parameter_id', '=', 'pemeriksaan_parameter.id')
            ->where('pemeriksaan_parameter.mst_parameter_id', $parameterId)
            ->select(
                'pemeriksaan_peserta_parameter.pemeriksaan_peserta_id',
                'pemeriksaan_peserta.peserta_id',
                'pemeriksaan_peserta.peserta_type',
                'pemeriksaan_peserta_parameter.nilai',
                'pemeriksaan_peserta_parameter.trend',
                'pemeriksaan_parameter.pemeriksaan_id'
            )
            ->get();

        // Get peserta list
        $pesertaList = collect();
        foreach ($statistikData as $data) {
            $pesertaType = $data->peserta_type;
            $pesertaId   = $data->peserta_id;

            switch ($pesertaType) {
                case 'App\\Models\\Atlet':
                    $atlet = DB::table('atlets')
                        ->where('id', $pesertaId)
                        ->select('id', 'nama', 'jenis_kelamin')
                        ->first();
                    if ($atlet) {
                        $pesertaList->push([
                            'id'            => $atlet->id,
                            'nama'          => $atlet->nama,
                            'jenis_peserta' => 'atlet',
                            'jenis_kelamin' => $atlet->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        ]);
                    }
                    break;

                case 'App\\Models\\Pelatih':
                    $pelatih = DB::table('pelatihs')
                        ->where('id', $pesertaId)
                        ->select('id', 'nama', 'jenis_kelamin')
                        ->first();
                    if ($pelatih) {
                        $pesertaList->push([
                            'id'            => $pelatih->id,
                            'nama'          => $pelatih->nama,
                            'jenis_peserta' => 'pelatih',
                            'jenis_kelamin' => $pelatih->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        ]);
                    }
                    break;

                case 'App\\Models\\TenagaPendukung':
                    $tenaga = DB::table('tenaga_pendukungs')
                        ->where('id', $pesertaId)
                        ->select('id', 'nama', 'jenis_kelamin')
                        ->first();
                    if ($tenaga) {
                        $pesertaList->push([
                            'id'            => $tenaga->id,
                            'nama'          => $tenaga->nama,
                            'jenis_peserta' => 'tenaga-pendukung',
                            'jenis_kelamin' => $tenaga->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                        ]);
                    }
                    break;
            }
        }

        // Transform data untuk menambahkan tanggal pemeriksaan
        $transformedData = $statistikData->map(function ($item) use ($pemeriksaanList) {
            $pemeriksaan = $pemeriksaanList->firstWhere('id', $item->pemeriksaan_id);
            return [
                'peserta_id'             => $item->peserta_id,
                'pemeriksaan_peserta_id' => $item->pemeriksaan_peserta_id,
                'nilai'                  => $item->nilai,
                'trend'                  => $item->trend,
                'tanggal_pemeriksaan'    => $pemeriksaan->tanggal_pemeriksaan ?? null,
                'pemeriksaan_id'         => $item->pemeriksaan_id,
            ];
        });

        return response()->json([
            'data'                => $transformedData,
            'rencana_pemeriksaan' => $pemeriksaanList->toArray(),
            'peserta'             => $pesertaList->unique('id')->values()->toArray(),
        ]);
    }
}
