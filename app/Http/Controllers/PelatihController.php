<?php

namespace App\Http\Controllers;

use App\Http\Requests\PelatihRequest;
use App\Imports\PelatihImport;
use App\Models\Pelatih;
use App\Models\Pemeriksaan;
use App\Models\PemeriksaanPeserta;
use App\Models\PemeriksaanPesertaParameter;
use App\Repositories\PelatihRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PelatihController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    private $request;

    public function __construct(PelatihRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request    = PelatihRequest::createFromBase($request);
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
            new Middleware("can:$permission Add", only: ['create', 'store', 'import']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);

        return response()->json([
            'data' => $data['pelatihs'],
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

    public function apiShow($id)
    {
        try {
            // Debug logging
            Log::info('PelatihController: apiShow method called', [
                'id' => $id,
            ]);

            $item = $this->repository->getDetailWithRelations($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pelatih tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pelatih detail: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pelatih',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PelatihRequest $request)
    {
        $data  = $this->repository->validateRequest($request);
        $model = $this->repository->create($data);

        return redirect()->route('pelatih.edit', $model->id)->with('success', 'Pelatih berhasil ditambahkan!');
    }

    public function update(PelatihRequest $request, $id)
    {
        try {
            // Debug logging
            Log::info('PelatihController: update method called', [
                'id'             => $id,
                'all_data'       => $request->all(),
                'validated_data' => $request->validated(),
            ]);

            // Use the same validation as store method
            $data = $this->repository->validateRequest($request);

            // Handle file upload if exists
            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file');
            }

            Log::info('PelatihController: data after validation', [
                'data' => $data,
            ]);

            // Update the record
            $model = $this->repository->update($id, $data);

            return redirect()->route('pelatih.edit', $model->id)->with('success', 'Pelatih berhasil diperbarui!');

        } catch (\Exception $e) {
            Log::error('Error updating pelatih: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data pelatih.');
        }
    }

    public function show($id)
    {
        $item = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/pelatih/Show', [
            'item' => $item,
        ]);
    }

    public function index()
    {
        return Inertia::render('modules/pelatih/Index', $this->repository->customIndex([]));
    }

    public function create()
    {
        return Inertia::render('modules/pelatih/Create', $this->repository->customCreateEdit([]));
    }

    public function edit($id)
    {
        return Inertia::render('modules/pelatih/Edit', $this->repository->customCreateEdit([], $this->repository->getById($id)));
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        return redirect()->route('pelatih.index')->with('success', 'Pelatih berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|integer|exists:pelatihs,id',
        ]);

        $this->repository->delete_selected($request->ids);

        return response()->json(['message' => 'Pelatih terpilih berhasil dihapus!']);
    }

    public function import(Request $request)
    {
        Log::info('PelatihController: import method called', [
            'file_name' => $request->file('file')?->getClientOriginalName(),
            'file_size' => $request->file('file')?->getSize(),
        ]);

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $import = new PelatihImport();
            Excel::import($import, $request->file('file'));

            Log::info('PelatihController: import successful', [
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
            Log::error('PelatihController: import failed', [
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
        $pelatih = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/pelatih/RiwayatPemeriksaan', [
            'pelatih' => $pelatih,
        ]);
    }

    public function parameterDetail($pelatihId, $pemeriksaanId)
    {
        $pelatih = $this->repository->getDetailWithRelations($pelatihId);

        // Ambil data pemeriksaan
        $pemeriksaan = Pemeriksaan::with(['tenagaPendukung'])
            ->findOrFail($pemeriksaanId);

        // Ambil data parameter pemeriksaan untuk pelatih ini
        $pemeriksaanPeserta = PemeriksaanPeserta::where('pemeriksaan_id', $pemeriksaanId)
            ->where('peserta_type', Pelatih::class)
            ->where('peserta_id', $pelatihId)
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

        return Inertia::render('modules/pelatih/ParameterDetail', [
            'pelatih'     => $pelatih,
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
            $pelatih = $this->repository->getDetailWithRelations($id);

            // Ambil semua pemeriksaan yang melibatkan pelatih ini
            $pemeriksaanPeserta = PemeriksaanPeserta::where('peserta_type', Pelatih::class)
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
}
