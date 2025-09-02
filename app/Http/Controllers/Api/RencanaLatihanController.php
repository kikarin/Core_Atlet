<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RencanaLatihan;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\TenagaPendukung;
use App\Repositories\RencanaLatihanRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RencanaLatihanController extends Controller
{
    protected $repository;

    public function __construct(RencanaLatihanRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List rencana latihan untuk mobile per program
     */
    public function index(Request $request, int $programId): JsonResponse
    {
        try {
            $data = $this->repository->getForMobile($request, $programId);

            return response()->json([
                'status' => 'success',
                'message' => 'Data rencana latihan berhasil diambil',
                'data' => $data['data'],
                'meta' => [
                    'total' => $data['total'],
                    'current_page' => $data['currentPage'],
                    'per_page' => $data['perPage'],
                    'search' => $data['search'],
                    'filters' => [
                        'date' => $data['filters']['date'] ?? null,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data rencana latihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail rencana latihan (opsional untuk mobile)
     */
    public function show(int $id): JsonResponse
    {
        try {
            $rencana = $this->repository->getDetailWithRelations($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Detail rencana latihan berhasil diambil',
                'data' => [
                    'id' => $rencana->id,
                    'tanggal' => $rencana->tanggal,
                    'materi' => $rencana->materi,
                    'lokasi' => $rencana->lokasi_latihan,
                    'catatan' => $rencana->catatan,
                    'target_latihan' => $rencana->targetLatihan->map(function ($t) {
                        return [
                            'id' => $t->id,
                            'deskripsi' => $t->deskripsi,
                            'satuan' => $t->satuan,
                            'jenis_target' => $t->jenis_target,
                        ];
                    })->values(),
                    'jumlah_atlet' => $rencana->atlets()->count(),
                    'jumlah_pelatih' => $rencana->pelatihs()->count(),
                    'jumlah_tenaga_pendukung' => $rencana->tenagaPendukung()->count(),
                    'total_peserta' => $rencana->atlets()->count() + $rencana->pelatihs()->count() + $rencana->tenagaPendukung()->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail rencana latihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Daftar peserta rencana latihan (grouped untuk mobile)
     */
    public function participants(Request $request, int $rencanaId): JsonResponse
    {
        try {
            $rencana = RencanaLatihan::with(['programLatihan'])->findOrFail($rencanaId);
            $caborKategoriId = $rencana->programLatihan->cabor_kategori_id;

            // ATLET - menggunakan Eloquent untuk akses accessor foto
            $atletIds = DB::table('rencana_latihan_atlet')
                ->where('rencana_latihan_id', $rencanaId)
                ->pluck('atlet_id');

            $atletQuery = Atlet::with(['media'])
                ->whereIn('atlets.id', $atletIds)
                ->leftJoin('cabor_kategori_atlet', function ($join) use ($caborKategoriId) {
                    $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                        ->where('cabor_kategori_atlet.cabor_kategori_id', $caborKategoriId)
                        ->whereNull('cabor_kategori_atlet.deleted_at');
                })
                ->leftJoin('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
                ->leftJoin('rencana_latihan_atlet', function ($join) use ($rencanaId) {
                    $join->on('atlets.id', '=', 'rencana_latihan_atlet.atlet_id')
                        ->where('rencana_latihan_atlet.rencana_latihan_id', $rencanaId);
                })
                ->select(
                    'atlets.*',
                    DB::raw("COALESCE(mst_posisi_atlet.nama, '-') as posisi"),
                    'rencana_latihan_atlet.kehadiran as kehadiran'
                );

            // PELATIH - menggunakan Eloquent untuk akses accessor foto
            $pelatihIds = DB::table('rencana_latihan_pelatih')
                ->where('rencana_latihan_id', $rencanaId)
                ->pluck('pelatih_id');

            $pelatihQuery = Pelatih::with(['media'])
                ->whereIn('pelatihs.id', $pelatihIds)
                ->leftJoin('cabor_kategori_pelatih', function ($join) use ($caborKategoriId) {
                    $join->on('pelatihs.id', '=', 'cabor_kategori_pelatih.pelatih_id')
                        ->where('cabor_kategori_pelatih.cabor_kategori_id', $caborKategoriId)
                        ->whereNull('cabor_kategori_pelatih.deleted_at');
                })
                ->leftJoin('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
                ->leftJoin('rencana_latihan_pelatih', function ($join) use ($rencanaId) {
                    $join->on('pelatihs.id', '=', 'rencana_latihan_pelatih.pelatih_id')
                        ->where('rencana_latihan_pelatih.rencana_latihan_id', $rencanaId);
                })
                ->select(
                    'pelatihs.*',
                    DB::raw("COALESCE(mst_jenis_pelatih.nama, '-') as jenis_pelatih"),
                    'rencana_latihan_pelatih.kehadiran as kehadiran'
                );

            // TENAGA PENDUKUNG - menggunakan Eloquent untuk akses accessor foto
            $tenagaIds = DB::table('rencana_latihan_tenaga_pendukung')
                ->where('rencana_latihan_id', $rencanaId)
                ->pluck('tenaga_pendukung_id');

            $tenagaQuery = TenagaPendukung::with(['media'])
                ->whereIn('tenaga_pendukungs.id', $tenagaIds)
                ->leftJoin('cabor_kategori_tenaga_pendukung', function ($join) use ($caborKategoriId) {
                    $join->on('tenaga_pendukungs.id', '=', 'cabor_kategori_tenaga_pendukung.tenaga_pendukung_id')
                        ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $caborKategoriId)
                        ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at');
                })
                ->leftJoin('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
                ->leftJoin('rencana_latihan_tenaga_pendukung', function ($join) use ($rencanaId) {
                    $join->on('tenaga_pendukungs.id', '=', 'rencana_latihan_tenaga_pendukung.tenaga_pendukung_id')
                        ->where('rencana_latihan_tenaga_pendukung.rencana_latihan_id', $rencanaId);
                })
                ->select(
                    'tenaga_pendukungs.*',
                    DB::raw("COALESCE(mst_jenis_tenaga_pendukung.nama, '-') as jenis_tenaga_pendukung"),
                    'rencana_latihan_tenaga_pendukung.kehadiran as kehadiran'
                );

            // Optional search by name
            if ($request->filled('search')) {
                $keyword = '%' . $request->search . '%';
                $atletQuery->where('atlets.nama', 'like', $keyword);
                $pelatihQuery->where('pelatihs.nama', 'like', $keyword);
                $tenagaQuery->where('tenaga_pendukungs.nama', 'like', $keyword);
            }

            $atlet = $atletQuery->orderBy('atlets.nama')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'foto' => $item->foto,
                    'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                    'usia' => $this->calculateAge($item->tanggal_lahir),
                    'posisi' => $item->posisi,
                    'kehadiran' => $item->kehadiran,
                ];
            });

            $pelatih = $pelatihQuery->orderBy('pelatihs.nama')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'foto' => $item->foto,
                    'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                    'usia' => $this->calculateAge($item->tanggal_lahir),
                    'jenisPelatih' => $item->jenis_pelatih,
                    'kehadiran' => $item->kehadiran,
                ];
            });

            $tenaga = $tenagaQuery->orderBy('tenaga_pendukungs.nama')->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->nama,
                    'foto' => $item->foto,
                    'jenisKelamin' => $this->mapJenisKelamin($item->jenis_kelamin),
                    'usia' => $this->calculateAge($item->tanggal_lahir),
                    'jenisTenagaPendukung' => $item->jenis_tenaga_pendukung,
                    'kehadiran' => $item->kehadiran,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data peserta berhasil diambil',
                'data' => [
                    'atlet' => $atlet->values(),
                    'pelatih' => $pelatih->values(),
                    'tenagaPendukung' => $tenaga->values(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data peserta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get full photo URL
     */
    private function getFullPhotoUrl($photoPath): ?string
    {
        if (empty($photoPath)) {
            return null;
        }

        if (filter_var($photoPath, FILTER_VALIDATE_URL)) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, '/')) {
            return config('app.url') . $photoPath;
        }

        return config('app.url') . '/storage/' . $photoPath;
    }

    /**
     * Map jenis kelamin
     */
    private function mapJenisKelamin($jenisKelamin): string
    {
        if ($jenisKelamin === 'L') return 'Laki-laki';
        if ($jenisKelamin === 'P') return 'Perempuan';
        return '-';
    }

    /**
     * Calculate age
     */
    private function calculateAge($tanggalLahir): int|string
    {
        if (!$tanggalLahir) return '-';
        
        try {
            $tanggalLahir = new Carbon($tanggalLahir);
            $today = Carbon::today();
            return (int) $tanggalLahir->diffInYears($today);
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Daftar target latihan berdasarkan rencana latihan (mobile)
     */
    public function targets(Request $request, int $rencanaId): JsonResponse
    {
        try {
            $rencana = RencanaLatihan::with(['targetLatihan', 'programLatihan'])->findOrFail($rencanaId);

            // Pisahkan target berdasarkan jenis
            $targetIndividu = $rencana->targetLatihan->filter(function ($target) {
                return $target->jenis_target === 'individu';
            })->map(function ($target) {
                return [
                    'id' => $target->id,
                    'nama' => $target->deskripsi,
                    'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                    'peserta' => ucfirst($target->peruntukan ?? 'Semua'),
                ];
            })->values();

            $targetKelompok = $rencana->targetLatihan->filter(function ($target) {
                return $target->jenis_target === 'kelompok';
            })->map(function ($target) use ($rencanaId) {
                // Ambil nilai dan trend dari pivot table untuk target kelompok
                $pivotData = DB::table('rencana_latihan_target_latihan')
                    ->where('rencana_latihan_id', $rencanaId)
                    ->where('target_latihan_id', $target->id)
                    ->first();

                return [
                    'id' => $target->id,
                    'nama' => $target->deskripsi,
                    'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                    'nilai' => $pivotData->nilai ?? null,
                    'trend' => $pivotData->trend ?? null,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Data target latihan berhasil diambil',
                'data' => [
                    'rencana' => [
                        'id' => $rencana->id,
                        'materi' => $rencana->materi,
                        'tanggal' => $rencana->tanggal,
                    ],
                    'targetIndividu' => $targetIndividu,
                    'targetKelompok' => $targetKelompok,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data target latihan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail target individu dengan daftar peserta dan nilai mereka (mobile)
     */
    public function targetDetail(Request $request, int $rencanaId, int $targetId): JsonResponse
    {
        try {
            $rencana = RencanaLatihan::with(['programLatihan'])->findOrFail($rencanaId);
            $target = $rencana->targetLatihan()->where('target_latihan.id', $targetId)->first();

            if (!$target) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Target latihan tidak ditemukan',
                ], 404);
            }

            // Ambil peserta yang sudah memiliki nilai untuk target ini
            $pesertaData = [];

            if ($target->peruntukan === 'atlet' || $target->peruntukan === 'semua') {
                $atletData = DB::table('rencana_latihan_peserta_target')
                    ->join('atlets', 'rencana_latihan_peserta_target.peserta_id', '=', 'atlets.id')
                    ->leftJoin('cabor_kategori_atlet', function ($join) use ($rencana) {
                        $join->on('atlets.id', '=', 'cabor_kategori_atlet.atlet_id')
                            ->where('cabor_kategori_atlet.cabor_kategori_id', $rencana->programLatihan->cabor_kategori_id)
                            ->whereNull('cabor_kategori_atlet.deleted_at');
                    })
                    ->leftJoin('mst_posisi_atlet', 'cabor_kategori_atlet.posisi_atlet_id', '=', 'mst_posisi_atlet.id')
                    ->where('rencana_latihan_peserta_target.rencana_latihan_id', $rencanaId)
                    ->where('rencana_latihan_peserta_target.target_latihan_id', $targetId)
                    ->where('rencana_latihan_peserta_target.peserta_type', 'App\\Models\\Atlet')
                    ->select(
                        'atlets.id',
                        'atlets.nama',
                        'atlets.jenis_kelamin',
                        'atlets.tanggal_lahir',
                        DB::raw("COALESCE(mst_posisi_atlet.nama, '-') as posisi"),
                        'rencana_latihan_peserta_target.nilai',
                        'rencana_latihan_peserta_target.trend'
                    )
                    ->get();

                foreach ($atletData as $atlet) {
                    $atletModel = \App\Models\Atlet::find($atlet->id);
                    $pesertaData[] = [
                        'id' => $atlet->id,
                        'nama' => $atlet->nama,
                        'foto' => $atletModel ? $atletModel->foto : null,
                        'jenisKelamin' => $this->mapJenisKelamin($atlet->jenis_kelamin),
                        'usia' => $this->calculateAge($atlet->tanggal_lahir),
                        'posisi' => $atlet->posisi,
                        'nilai' => $atlet->nilai,
                        'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                        'trend' => ucfirst($atlet->trend ?? 'stabil'),
                    ];
                }
            }

            if ($target->peruntukan === 'pelatih' || $target->peruntukan === 'semua') {
                $pelatihData = DB::table('rencana_latihan_peserta_target')
                    ->join('pelatihs', 'rencana_latihan_peserta_target.peserta_id', '=', 'pelatihs.id')
                    ->leftJoin('cabor_kategori_pelatih', function ($join) use ($rencana) {
                        $join->on('pelatihs.id', '=', 'cabor_kategori_pelatih.pelatih_id')
                            ->where('cabor_kategori_pelatih.cabor_kategori_id', $rencana->programLatihan->cabor_kategori_id)
                            ->whereNull('cabor_kategori_pelatih.deleted_at');
                    })
                    ->leftJoin('mst_jenis_pelatih', 'cabor_kategori_pelatih.jenis_pelatih_id', '=', 'mst_jenis_pelatih.id')
                    ->where('rencana_latihan_peserta_target.rencana_latihan_id', $rencanaId)
                    ->where('rencana_latihan_peserta_target.target_latihan_id', $targetId)
                    ->where('rencana_latihan_peserta_target.peserta_type', 'App\\Models\\Pelatih')
                    ->select(
                        'pelatihs.id',
                        'pelatihs.nama',
                        'pelatihs.jenis_kelamin',
                        'pelatihs.tanggal_lahir',
                        DB::raw("COALESCE(mst_jenis_pelatih.nama, '-') as posisi"),
                        'rencana_latihan_peserta_target.nilai',
                        'rencana_latihan_peserta_target.trend'
                    )
                    ->get();

                foreach ($pelatihData as $pelatih) {
                    $pelatihModel = \App\Models\Pelatih::find($pelatih->id);
                    $pesertaData[] = [
                        'id' => $pelatih->id,
                        'nama' => $pelatih->nama,
                        'foto' => $pelatihModel ? $pelatihModel->foto : null,
                        'jenisKelamin' => $this->mapJenisKelamin($pelatih->jenis_kelamin),
                        'usia' => $this->calculateAge($pelatih->tanggal_lahir),
                        'posisi' => $pelatih->posisi,
                        'nilai' => $pelatih->nilai,
                        'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                        'trend' => ucfirst($pelatih->trend ?? 'stabil'),
                    ];
                }
            }

            if ($target->peruntukan === 'tenaga-pendukung' || $target->peruntukan === 'semua') {
                $tenagaData = DB::table('rencana_latihan_peserta_target')
                    ->join('tenaga_pendukungs', 'rencana_latihan_peserta_target.peserta_id', '=', 'tenaga_pendukungs.id')
                    ->leftJoin('cabor_kategori_tenaga_pendukung', function ($join) use ($rencana) {
                        $join->on('tenaga_pendukungs.id', '=', 'cabor_kategori_tenaga_pendukung.tenaga_pendukung_id')
                            ->where('cabor_kategori_tenaga_pendukung.cabor_kategori_id', $rencana->programLatihan->cabor_kategori_id)
                            ->whereNull('cabor_kategori_tenaga_pendukung.deleted_at');
                    })
                    ->leftJoin('mst_jenis_tenaga_pendukung', 'cabor_kategori_tenaga_pendukung.jenis_tenaga_pendukung_id', '=', 'mst_jenis_tenaga_pendukung.id')
                    ->where('rencana_latihan_peserta_target.rencana_latihan_id', $rencanaId)
                    ->where('rencana_latihan_peserta_target.target_latihan_id', $targetId)
                    ->where('rencana_latihan_peserta_target.peserta_type', 'App\\Models\\TenagaPendukung')
                    ->select(
                        'tenaga_pendukungs.id',
                        'tenaga_pendukungs.nama',
                        'tenaga_pendukungs.jenis_kelamin',
                        'tenaga_pendukungs.tanggal_lahir',
                        DB::raw("COALESCE(mst_jenis_tenaga_pendukung.nama, '-') as posisi"),
                        'rencana_latihan_peserta_target.nilai',
                        'rencana_latihan_peserta_target.trend'
                    )
                    ->get();

                foreach ($tenagaData as $tenaga) {
                    $tenagaModel = \App\Models\TenagaPendukung::find($tenaga->id);
                    $pesertaData[] = [
                        'id' => $tenaga->id,
                        'nama' => $tenaga->nama,
                        'foto' => $tenagaModel ? $tenagaModel->foto : null,
                        'jenisKelamin' => $this->mapJenisKelamin($tenaga->jenis_kelamin),
                        'usia' => $this->calculateAge($tenaga->tanggal_lahir),
                        'posisi' => $tenaga->posisi,
                        'nilai' => $tenaga->nilai,
                        'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                        'trend' => ucfirst($tenaga->trend ?? 'stabil'),
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail target latihan berhasil diambil',
                'data' => [
                    'target' => [
                        'id' => $target->id,
                        'nama' => $target->deskripsi,
                        'target' => ($target->nilai_target ?? '') . ' ' . ($target->satuan ?? ''),
                        'peserta' => ucfirst($target->peruntukan ?? 'Semua'),
                    ],
                    'rencana' => [
                        'id' => $rencana->id,
                        'materi' => $rencana->materi,
                        'tanggal' => $rencana->tanggal,
                    ],
                    'pesertaTarget' => $pesertaData,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil detail target latihan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
