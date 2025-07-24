<?php

namespace App\Http\Controllers;

use App\Http\Requests\PemeriksaanPesertaParameterRequest;
use App\Models\Pemeriksaan;
use App\Models\PemeriksaanPeserta;
use App\Models\PemeriksaanParameter;
use App\Repositories\PemeriksaanPesertaParameterRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class PemeriksaanPesertaParameterController extends Controller implements HasMiddleware
{
    use BaseTrait;
    private $repository;
    private $request;

    public function __construct(Request $request, PemeriksaanPesertaParameterRepository $repository)
    {
        $this->repository = $repository;
        $this->request    = PemeriksaanPesertaParameterRequest::createFromBase($request);
        $this->initialize();
        $this->route = 'pemeriksaan-peserta-parameter';
    }

    public static function middleware(): array
    {
        $className  = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));
        return [
            new Middleware("can:$permission Add", only: ['create', 'store']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function index(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta)
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = [
            'pemeriksaan' => $pemeriksaan,
            'peserta' => $peserta,
        ];
        request()->merge([
            'pemeriksaan_id' => $pemeriksaan->id,
            'pemeriksaan_peserta_id' => $peserta->id,
        ]);
        $data = $this->repository->customIndex($data);
        return inertia('modules/pemeriksaan-peserta-parameter/Index', $data + [
            'pemeriksaan' => $pemeriksaan,
            'peserta' => $peserta,
        ]);
    }

    public function create(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta)
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = [
            'item' => null,
            'pemeriksaan' => $pemeriksaan,
            'peserta' => $peserta,
            'parameters' => PemeriksaanParameter::where('pemeriksaan_id', $pemeriksaan->id)->get(),
        ];
        $data = $this->repository->customCreateEdit($data);
        return inertia('modules/pemeriksaan-peserta-parameter/Create', $data);
    }

    public function store(PemeriksaanPesertaParameterRequest $request, Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta)
    {
        $data = $this->repository->validateRequest($request);
        $data['pemeriksaan_id'] = $pemeriksaan->id;
        $data['pemeriksaan_peserta_id'] = $peserta->id;
        $this->repository->create($data);
        return redirect()->route('pemeriksaan.peserta.parameter.index', [$pemeriksaan->id, $peserta->id])->with('success', 'Parameter peserta berhasil ditambahkan!');
    }

    public function show(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta, $id)
    {
        $item = $this->repository->getById($id);
        return Inertia::render('modules/pemeriksaan-peserta-parameter/Show', [
            'item' => $item,
            'pemeriksaan' => $pemeriksaan,
            'peserta' => $peserta,
        ]);
    }

    public function edit(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta, $id)
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = [
            'item' => $item,
            'pemeriksaan' => $pemeriksaan,
            'peserta' => $peserta,
            'parameters' => PemeriksaanParameter::where('pemeriksaan_id', $pemeriksaan->id)->get(),
        ];
        $data = $this->repository->customCreateEdit($data, $item);
        return inertia('modules/pemeriksaan-peserta-parameter/Edit', $data);
    }

    public function update(PemeriksaanPesertaParameterRequest $request, Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta, $id)
    {
        $data = $this->repository->validateRequest($request);
        $data['pemeriksaan_id'] = $pemeriksaan->id;
        $data['pemeriksaan_peserta_id'] = $peserta->id;
        $this->repository->update($id, $data);
        return redirect()->route('pemeriksaan.peserta.parameter.index', [$pemeriksaan->id, $peserta->id])->with('success', 'Parameter peserta berhasil diperbarui!');
    }

    public function destroy(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta, $id)
    {
        $this->repository->delete($id);
        return redirect()->route('pemeriksaan.peserta.parameter.index', [$pemeriksaan->id, $peserta->id])->with('success', 'Parameter peserta berhasil dihapus!');
    }

    public function destroy_selected(Request $request, Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'required|numeric|exists:pemeriksaan_peserta_parameter,id',
        ]);
        $this->repository->delete_selected($request->ids);
        return response()->json(['message' => 'Parameter peserta berhasil dihapus!']);
    }

    public function apiIndex(Pemeriksaan $pemeriksaan, PemeriksaanPeserta $peserta)
    {
        request()->merge([
            'pemeriksaan_id' => $pemeriksaan->id,
            'pemeriksaan_peserta_id' => $peserta->id,
        ]);
        $data = $this->repository->customIndex([]);
        return response()->json([
            'data' => $data['data'],
            'meta' => [
                'total'        => $data['total'],
                'current_page' => $data['currentPage'],
                'per_page'     => $data['perPage'],
                'search'       => $data['search'],
            ],
        ]);
    }
} 