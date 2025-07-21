<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemeriksaanPesertaRequest;
use App\Models\Pemeriksaan;
use App\Models\Atlet;
use App\Models\Pelatih;
use App\Models\TenagaPendukung;
use App\Models\RefStatusPemeriksaan;
use App\Repositories\PemeriksaanPesertaRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;

class PemeriksaanPesertaController extends Controller
{
    use BaseTrait;
    private $repository;

    public function __construct(PemeriksaanPesertaRepository $repository)
    {
        $this->repository = $repository;
        $this->initialize();
    }

    public function index(Pemeriksaan $pemeriksaan)
    {
        $pemeriksaan->load(['cabor', 'caborKategori', 'tenagaPendukung']);
        request()->merge(['pemeriksaan_id' => $pemeriksaan->id]);
        $data = $this->repository->customIndex(['pemeriksaan' => $pemeriksaan]);
        
        // Pass pemeriksaan data to inertia
        $data['pemeriksaan'] = $pemeriksaan;

        return inertia('modules/pemeriksaan-peserta/Index', $data);
    }
    
    public function create(Pemeriksaan $pemeriksaan)
    {
        $pemeriksaan->load(['cabor', 'caborKategori', 'tenagaPendukung']);
        $atlets = Atlet::where('is_active', true)->get(['id', 'nama']);
        $pelatihs = Pelatih::where('is_active', true)->get(['id', 'nama']);
        $tenagaPendukung = TenagaPendukung::where('is_active', true)->get(['id', 'nama']);
        $ref_status_pemeriksaan = RefStatusPemeriksaan::all();
        
        return inertia('modules/pemeriksaan-peserta/Create', [
            'pemeriksaan' => $pemeriksaan,
            'atlets' => $atlets,
            'pelatihs' => $pelatihs,
            'tenagaPendukung' => $tenagaPendukung,
            'ref_status_pemeriksaan' => $ref_status_pemeriksaan,
        ]);
    }

    public function store(PemeriksaanPesertaRequest $request, Pemeriksaan $pemeriksaan)
    {
        $data = $request->validated();
        
        $this->repository->createMultiple($pemeriksaan, $data);
        return redirect()->route('pemeriksaan.peserta.index', $pemeriksaan->id)->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function show(Pemeriksaan $pemeriksaan, $peserta_id)
    {
        $item = $this->repository->getDetailWithRelations($peserta_id);
        $pemeriksaan->load(['cabor', 'caborKategori', 'tenagaPendukung']);
        return inertia('modules/pemeriksaan-peserta/Show', [
            'pemeriksaan' => $pemeriksaan,
            'item' => $item,
        ]);
    }

    public function edit(Pemeriksaan $pemeriksaan, $peserta_id)
    {
        $item = $this->repository->getById($peserta_id);
        $pemeriksaan->load(['cabor', 'caborKategori', 'tenagaPendukung']);
        $atlets = Atlet::where('is_active', true)->get(['id', 'nama']);
        $pelatihs = Pelatih::where('is_active', true)->get(['id', 'nama']);
        $tenagaPendukung = TenagaPendukung::where('is_active', true)->get(['id', 'nama']);
        $ref_status_pemeriksaan = RefStatusPemeriksaan::all();

        return inertia('modules/pemeriksaan-peserta/Edit', [
            'pemeriksaan' => $pemeriksaan,
            'item' => $item,
            'atlets' => $atlets,
            'pelatihs' => $pelatihs,
            'tenagaPendukung' => $tenagaPendukung,
            'ref_status_pemeriksaan' => $ref_status_pemeriksaan,
        ]);
    }

    public function update(PemeriksaanPesertaRequest $request, Pemeriksaan $pemeriksaan, $peserta_id)
    {
        $data = $request->validated();
        $this->repository->update($peserta_id, $data);
        return redirect()->route('pemeriksaan.peserta.index', $pemeriksaan->id)->with('success', 'Peserta berhasil diperbarui.');
    }
    
    public function destroy(Pemeriksaan $pemeriksaan, $peserta_id)
    {
        $this->repository->delete($peserta_id);
        return redirect()->route('pemeriksaan.peserta.index', $pemeriksaan->id)->with('success', 'Peserta berhasil dihapus.');
    }

    public function apiIndex(Pemeriksaan $pemeriksaan)
    {
        request()->merge(['pemeriksaan_id' => $pemeriksaan->id]);
        $data = $this->repository->customIndex([]);
        return response()->json($data);
    }
} 