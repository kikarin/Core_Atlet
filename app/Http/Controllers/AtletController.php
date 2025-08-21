<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtletRequest;
use App\Imports\AtletImport;
use App\Models\Atlet;
use App\Models\Pemeriksaan;
use App\Models\PemeriksaanPeserta;
use App\Models\PemeriksaanPesertaParameter;
use App\Repositories\AtletRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AtletController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    private $request;

    public function __construct(AtletRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request    = AtletRequest::createFromBase($request);
        $this->initialize();
        $this->commonData['kode_first_menu']  = $this->kode_menu;
        $this->commonData['kode_second_menu'] = null;
    }

    public static function middleware(): array
    {
        $className  = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));

        return [
            new Middleware("can:$permission Show", only: ['index','riwayatPemeriksaan', 'getByAtletId',]),
            new Middleware("can:$permission Add", only: ['create', 'store']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update' ]),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function index()
    {
        return Inertia::render('modules/atlet/Index');
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);

        return response()->json([
            'data' => $data['atlets'],
            'meta' => [
                'total'        => $data['total'],
                'current_page' => $data['currentPage'],
                'per_page'     => $data['perPage'],
                'search'       => $data['search'],
                'sort'         => $data['sort'],
                'order'        => $data['order'],
            ],
        ]);
    }

    public function show($id)
    {
        $item = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/atlet/Show', [
            'item' => $item,
        ]);
    }

    public function apiShow($id)
    {
        try {
            // Debug logging
            Log::info('AtletController: apiShow method called', [
                'id' => $id,
            ]);

            $item = $this->repository->getDetailWithRelations($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atlet tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching atlet detail: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data atlet',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        Log::info('AtletController: import method called', [
            'file_name' => $request->file('file')?->getClientOriginalName(),
            'file_size' => $request->file('file')?->getSize(),
        ]);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new AtletImport();
            Excel::import($import, $request->file('file'));

            Log::info('AtletController: import successful', [
                'rows_processed' => $import->getRowCount(),
                'success_count'  => $import->getSuccessCount(),
                'error_count'    => $import->getErrorCount(),
            ]);

            $message = 'Import berhasil! ';
            if ($import->getSuccessCount() > 0) {
                $message .= "Berhasil import {$import->getSuccessCount()} data.";
            }
            if ($import->getErrorCount() > 0) {
                $message .= " {$import->getErrorCount()} data gagal diimport.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => [
                    'total_rows'    => $import->getRowCount(),
                    'success_count' => $import->getSuccessCount(),
                    'error_count'   => $import->getErrorCount(),
                    'errors'        => $import->getErrors(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('AtletController: import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import: '.$e->getMessage(),
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    public function riwayatPemeriksaan($id)
    {
        $atlet = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/atlet/RiwayatPemeriksaan', [
            'atlet' => $atlet,
        ]);
    }

    public function parameterDetail($atletId, $pemeriksaanId)
    {
        $atlet = $this->repository->getDetailWithRelations($atletId);

        // Ambil data pemeriksaan
        $pemeriksaan = Pemeriksaan::with(['tenagaPendukung'])
            ->findOrFail($pemeriksaanId);

        // Ambil data parameter pemeriksaan untuk atlet ini
        $pemeriksaanPeserta = PemeriksaanPeserta::where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', Atlet::class)
            ->where('peserta_id', $atletId)
            ->first();

        $parameters = [];
        if ($pemeriksaanPeserta) {
            $parameters = PemeriksaanPesertaParameter::with(['pemeriksaanParameter'])
                ->where('pemeriksaan_peserta_id', $pemeriksaanPeserta->id)
                ->get()
                ->map(function ($item) {
                    return [
                        'id'             => $item->id,
                        'nama_parameter' => $item->pemeriksaanParameter->nama_parameter ?? '-',
                        'nilai'          => $item->nilai,
                        'trend'          => $item->trend,
                    ];
                });
        }

        return Inertia::render('modules/atlet/ParameterDetail', [
            'atlet'       => $atlet,
            'pemeriksaan' => [
                'id'                  => $pemeriksaan->id,
                'nama_pemeriksaan'    => $pemeriksaan->nama_pemeriksaan,
                'tanggal_pemeriksaan' => $pemeriksaan->tanggal_pemeriksaan,
                'tenaga_pendukung'    => $pemeriksaan->tenagaPendukung->nama ?? '-',
                'status'              => $pemeriksaan->status,
            ],
            'parameters' => $parameters,
        ]);
    }

    public function apiRiwayatPemeriksaan($id)
    {
        try {
            $atlet = $this->repository->getDetailWithRelations($id);

            // Ambil semua pemeriksaan yang melibatkan atlet ini
            $pemeriksaanPeserta = PemeriksaanPeserta::where('peserta_type', Atlet::class)
                ->where('peserta_id', $id)
                ->with(['pemeriksaan.tenagaPendukung', 'pemeriksaanPesertaParameter'])
                ->get();

            $riwayat = $pemeriksaanPeserta->map(function ($item) {
                return [
                    'id'                  => $item->pemeriksaan->id,
                    'nama_pemeriksaan'    => $item->pemeriksaan->nama_pemeriksaan,
                    'tanggal_pemeriksaan' => $item->pemeriksaan->tanggal_pemeriksaan,
                    'tenaga_pendukung'    => $item->pemeriksaan->tenagaPendukung->nama ?? '-',
                    'status'              => $item->pemeriksaan->status,
                    'jumlah_parameter'    => $item->pemeriksaanPesertaParameter->count(),
                ];
            });

            return response()->json([
                'data' => $riwayat,
                'meta' => [
                    'total'        => $riwayat->count(),
                    'current_page' => 1,
                    'per_page'     => $riwayat->count(),
                    'search'       => '',
                    'sort'         => '',
                    'order'        => 'asc',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching riwayat pemeriksaan: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data riwayat pemeriksaan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle akun atlet
     */
    public function handleAkun($id)
    {
        try {
            $atlet = $this->repository->getDetailWithRelations($id);

            return Inertia::render('modules/atlet/Edit', [
                'item' => $atlet,
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling akun atlet: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses akun atlet');
        }
    }

    /**
     * Store akun atlet
     */
    public function storeAkun(Request $request, $id)
    {
        try {
            $atlet = $this->repository->getDetailWithRelations($id);

            // Validasi request untuk akun
            $request->validate([
                'akun_email'    => 'required|email|unique:users,email',
                'akun_password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|not_in:password,123456,admin',
            ], [
                'akun_email.required'    => 'Email wajib diisi.',
                'akun_email.email'       => 'Format email tidak valid.',
                'akun_email.unique'      => 'Email sudah digunakan.',
                'akun_password.required' => 'Password wajib diisi.',
                'akun_password.min'      => 'Password minimal 8 karakter.',
                'akun_password.regex'    => 'Password harus mengandung huruf kecil, huruf besar, dan angka.',
                'akun_password.not_in'   => 'Password tidak boleh menggunakan kata yang mudah ditebak.',
            ]);

            // Handle akun creation di repository
            $this->repository->handleAtletAkun($atlet, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Akun atlet berhasil dibuat!',
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating akun atlet: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat akun atlet: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update akun atlet
     */
    public function updateAkun(Request $request, $id)
    {
        try {
            $atlet = $this->repository->getDetailWithRelations($id);

            // Validasi request untuk akun
            $rules = [
                'akun_email' => 'required|email',
            ];

            // Jika ada users_id, validasi email unique kecuali untuk user yang sama
            if ($atlet->users_id) {
                $rules['akun_email'] = 'required|email|unique:users,email,'.$atlet->users_id;
            } else {
                $rules['akun_email'] = 'required|email|unique:users,email';
            }

            // Password opsional untuk update
            if ($request->akun_password) {
                $rules['akun_password'] = 'string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/|not_in:password,123456,admin';
            }

            $request->validate($rules, [
                'akun_email.required'  => 'Email wajib diisi.',
                'akun_email.email'     => 'Format email tidak valid.',
                'akun_email.unique'    => 'Email sudah digunakan.',
                'akun_password.min'    => 'Password minimal 8 karakter.',
                'akun_password.regex'  => 'Password harus mengandung huruf kecil, huruf besar, dan angka.',
                'akun_password.not_in' => 'Password tidak boleh menggunakan kata yang mudah ditebak.',
            ]);

            // Handle akun update di repository
            $this->repository->handleAtletAkun($atlet, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Akun atlet berhasil diperbarui!',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating akun atlet: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun atlet: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Show karakteristik atlet
     */
    public function karakteristik()
    {
        return Inertia::render('modules/atlet/Karakteristik');
    }

    /**
     * API untuk mendapatkan data karakteristik atlet
     */
    public function apiKarakteristik(Request $request)
    {
        try {
            $data = $this->repository->jumlah_karakteristik([
                'tanggal_awal'  => $request->tanggal_awal,
                'tanggal_akhir' => $request->tanggal_akhir,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching karakteristik atlet: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data karakteristik atlet',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
