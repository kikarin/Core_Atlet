<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\ProgramLatihanRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProgramLatihanController extends Controller
{
    protected $repository;

    public function __construct(ProgramLatihanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get list of program latihan with search and filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Add logging untuk debug
            Log::info('Program Latihan Mobile API called', [
                'user'            => auth()->user(),
                'user_id'         => auth()->id(),
                'current_role_id' => auth()->user()->current_role_id ?? 'no role',
                'headers'         => $request->headers->all(),
            ]);

            $data = $this->repository->getForMobile($request);

            return response()->json([
                'status'  => 'success',
                'message' => 'Data program latihan berhasil diambil',
                'data'    => $data['data'],
                'meta'    => [
                    'total'        => $data['total'],
                    'current_page' => $data['currentPage'],
                    'per_page'     => $data['perPage'],
                    'search'       => $data['search'],
                    'filters'      => [
                        'cabor_id'   => $data['filters']['cabor_id']   ?? null,
                        'start_date' => $data['filters']['start_date'] ?? null,
                        'end_date'   => $data['filters']['end_date']   ?? null,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data program latihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail program latihan by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $program = $this->repository->getDetailWithRelations($id);

            if (!$program) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Program latihan tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Detail program latihan berhasil diambil',
                'data'    => [
                    'id'           => $program->id,
                    'nama_program' => $program->nama_program,
                    'cabor'        => [
                        'id'   => $program->cabor->id   ?? null,
                        'nama' => $program->cabor->nama ?? null,
                    ],
                    'kategori' => [
                        'id'   => $program->caborKategori->id   ?? null,
                        'nama' => $program->caborKategori->nama ?? null,
                    ],
                    'periode' => [
                        'mulai'     => $program->periode_mulai,
                        'selesai'   => $program->periode_selesai,
                        'formatted' => $this->formatPeriode($program->periode_mulai, $program->periode_selesai),
                    ],
                    'keterangan'             => $program->keterangan,
                    'jumlah_rencana_latihan' => $program->rencanaLatihan()->count(),
                    'created_at'             => $program->created_at,
                    'updated_at'             => $program->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil detail program latihan: ' . $e->getMessage(),
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
                'status'  => 'success',
                'message' => 'Data cabor berhasil diambil',
                'data'    => $caborList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data cabor: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format periode tanggal untuk mobile app
     */
    private function formatPeriode($startDate, $endDate): string
    {
        if (!$startDate || !$endDate) {
            return '-';
        }

        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        $startDay   = $start->format('j');
        $startMonth = $this->getIndonesianMonth($start->format('n'));
        $startYear  = $start->format('Y');

        $endDay   = $end->format('j');
        $endMonth = $this->getIndonesianMonth($end->format('n'));
        $endYear  = $end->format('Y');

        // Jika tahun sama
        if ($startYear === $endYear) {
            // Jika bulan sama
            if ($startMonth === $endMonth) {
                return "{$startDay}-{$endDay} {$startMonth} {$startYear}";
            } else {
                // Jika bulan berbeda
                return "{$startDay} {$startMonth} - {$endDay} {$endMonth} {$startYear}";
            }
        } else {
            // Jika tahun berbeda
            return "{$startDay} {$startMonth} {$startYear} - {$endDay} {$endMonth} {$endYear}";
        }
    }

    /**
     * Get Indonesian month name
     */
    private function getIndonesianMonth($monthNumber): string
    {
        $months = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$monthNumber] ?? '';
    }

}
