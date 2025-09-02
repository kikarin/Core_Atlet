<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PemeriksaanRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PemeriksaanController extends Controller
{
    protected $repository;

    public function __construct(PemeriksaanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get list of pemeriksaan with search and filters for mobile
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Add logging untuk debug
            Log::info('Pemeriksaan Mobile API called', [
                'user' => auth()->user(),
                'user_id' => auth()->id(),
                'current_role_id' => auth()->user()->current_role_id ?? 'no role',
                'headers' => $request->headers->all()
            ]);
            
            $data = $this->repository->getForMobile($request);

            return response()->json([
                'status' => 'success',
                'message' => 'Data pemeriksaan berhasil diambil',
                'data' => $data['data'],
                'meta' => [
                    'total' => $data['total'],
                    'current_page' => $data['currentPage'],
                    'per_page' => $data['perPage'],
                    'search' => $data['search'],
                    'filters' => [
                        'cabor_id' => $data['filters']['cabor_id'] ?? null,
                        'tanggal_pemeriksaan' => $data['filters']['tanggal_pemeriksaan'] ?? null,
                    ]
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail pemeriksaan by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $pemeriksaan = $this->repository->getDetailWithRelations($id);

            if (!$pemeriksaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemeriksaan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail pemeriksaan berhasil diambil',
                'data' => [
                    'id' => $pemeriksaan->id,
                    'nama_pemeriksaan' => $pemeriksaan->nama_pemeriksaan,
                    'cabor' => [
                        'id' => $pemeriksaan->cabor->id ?? null,
                        'nama' => $pemeriksaan->cabor->nama ?? null,
                    ],
                    'kategori' => [
                        'id' => $pemeriksaan->caborKategori->id ?? null,
                        'nama' => $pemeriksaan->caborKategori->nama ?? null,
                    ],
                    'tenaga_pendukung' => [
                        'id' => $pemeriksaan->tenagaPendukung->id ?? null,
                        'nama' => $pemeriksaan->tenagaPendukung->nama ?? null,
                    ],
                    'tanggal_pemeriksaan' => $pemeriksaan->tanggal_pemeriksaan,
                    'status' => $pemeriksaan->status,
                    'jumlah_parameter' => $pemeriksaan->pemeriksaanParameter()->count(),
                    'jumlah_peserta' => $pemeriksaan->pemeriksaanPeserta()->count(),
                    'jumlah_atlet' => $pemeriksaan->pemeriksaanPeserta()->where('peserta_type', 'App\\Models\\Atlet')->count(),
                    'jumlah_pelatih' => $pemeriksaan->pemeriksaanPeserta()->where('peserta_type', 'App\\Models\\Pelatih')->count(),
                    'jumlah_tenaga_pendukung' => $pemeriksaan->pemeriksaanPeserta()->where('peserta_type', 'App\\Models\\TenagaPendukung')->count(),
                    'created_at' => $pemeriksaan->created_at,
                    'updated_at' => $pemeriksaan->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of cabor for filter options
     */
    public function getCaborList(): JsonResponse
    {
        try {
            $caborList = $this->repository->getCaborList();

            return response()->json([
                'status' => 'success',
                'message' => 'Data cabor berhasil diambil',
                'data' => $caborList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data cabor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of peserta pemeriksaan (grouped by type)
     */
    public function peserta(Request $request, int $pemeriksaanId): JsonResponse
    {
        try {
            $pemeriksaan = $this->repository->getDetailWithRelations($pemeriksaanId);
            
            if (!$pemeriksaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemeriksaan tidak ditemukan',
                ], 404);
            }

            $pesertaData = $this->repository->getPesertaForMobile($pemeriksaanId, $request);

            return response()->json([
                'status' => 'success',
                'message' => 'Data peserta pemeriksaan berhasil diambil',
                'data' => [
                    'pemeriksaan' => [
                        'id' => $pemeriksaan->id,
                        'nama_pemeriksaan' => $pemeriksaan->nama_pemeriksaan,
                        'tanggal_pemeriksaan' => $pemeriksaan->tanggal_pemeriksaan,
                        'status' => $pemeriksaan->status,
                    ],
                    'atlet' => $pesertaData['atlet'],
                    'pelatih' => $pesertaData['pelatih'],
                    'tenagaPendukung' => $pesertaData['tenagaPendukung'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data peserta pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of parameter pemeriksaan
     */
    public function parameter(Request $request, int $pemeriksaanId): JsonResponse
    {
        try {
            $pemeriksaan = $this->repository->getDetailWithRelations($pemeriksaanId);
            
            if (!$pemeriksaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemeriksaan tidak ditemukan',
                ], 404);
            }

            $parameterData = $this->repository->getParameterForMobile($pemeriksaanId, $request);

            return response()->json([
                'status' => 'success',
                'message' => 'Data parameter pemeriksaan berhasil diambil',
                'data' => [
                    'pemeriksaan' => [
                        'id' => $pemeriksaan->id,
                        'nama_pemeriksaan' => $pemeriksaan->nama_pemeriksaan,
                        'tanggal_pemeriksaan' => $pemeriksaan->tanggal_pemeriksaan,
                    ],
                    'parameter' => $parameterData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data parameter pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail parameter pemeriksaan dengan peserta dan nilai
     */
    public function parameterDetail(Request $request, int $pemeriksaanId, int $parameterId): JsonResponse
    {
        try {
            $pemeriksaan = $this->repository->getDetailWithRelations($pemeriksaanId);
            
            if (!$pemeriksaan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pemeriksaan tidak ditemukan',
                ], 404);
            }

            $parameter = $this->repository->getParameterDetail($parameterId);
            
            if (!$parameter) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter tidak ditemukan',
                ], 404);
            }

            $pesertaData = $this->repository->getPesertaParameterForMobile($pemeriksaanId, $parameterId, $request);

            return response()->json([
                'status' => 'success',
                'message' => 'Data detail parameter pemeriksaan berhasil diambil',
                'data' => [
                    'pemeriksaan' => [
                        'id' => $pemeriksaan->id,
                        'nama_pemeriksaan' => $pemeriksaan->nama_pemeriksaan,
                        'tanggal_pemeriksaan' => $pemeriksaan->tanggal_pemeriksaan,
                    ],
                    'parameter' => [
                        'id' => $parameter->id,
                        'nama_parameter' => $parameter->nama_parameter,
                        'satuan' => $parameter->satuan,
                    ],
                    'atlet' => $pesertaData['atlet'],
                    'pelatih' => $pesertaData['pelatih'],
                    'tenagaPendukung' => $pesertaData['tenagaPendukung'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data detail parameter pemeriksaan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
