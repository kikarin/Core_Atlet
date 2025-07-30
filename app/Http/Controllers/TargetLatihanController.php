<?php

namespace App\Http\Controllers;

use App\Http\Requests\TargetLatihanRequest;
use App\Models\ProgramLatihan;
use App\Repositories\TargetLatihanRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class TargetLatihanController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    private $request;

    public function __construct(Request $request, TargetLatihanRepository $repository)
    {
        $this->repository = $repository;
        $this->request = $request;
        $this->initialize();
        $this->route = 'target-latihan';
        $this->commonData['kode_first_menu'] = 'PROGRAM-LATIHAN';
        $this->commonData['kode_second_menu'] = 'TARGET-LATIHAN';
    }

    public static function middleware(): array
    {
        $className = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));

        return [
            new Middleware("can:$permission Add", only: ['create', 'store']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);

        return response()->json([
            'data' => $data['data'],
            'meta' => [
                'total' => $data['total'],
                'current_page' => $data['currentPage'],
                'per_page' => $data['perPage'],
                'search' => $data['search'],
                'sort' => $data['sort'],
                'order' => $data['order'],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customIndex($data);

        $programLatihan = null;
        if ($request->has('program_latihan_id')) {
            $programLatihan = ProgramLatihan::with(['cabor', 'caborKategori'])->find($request->program_latihan_id);
        }
        $data['infoHeader'] = [
            'nama_program' => $programLatihan?->nama_program ?? '-',
            'cabor_nama' => $programLatihan?->cabor?->nama ?? '-',
            'periode_mulai' => $programLatihan?->periode_mulai ?? '-',
            'periode_selesai' => $programLatihan?->periode_selesai ?? '-',
            'jenis_target' => $request->jenis_target ?? '-',
        ];

        return inertia('modules/target-latihan/Index', $data);
    }

    public function create(Request $request)
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'item' => null,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        // Ambil info program/cabor/periode/jenis target dari query param jika ada
        $programLatihan = null;
        if ($request->has('program_latihan_id')) {
            $programLatihan = ProgramLatihan::with(['cabor', 'caborKategori'])->find($request->program_latihan_id);
        }
        $data['infoHeader'] = [
            'nama_program' => $programLatihan?->nama_program ?? '-',
            'cabor_nama' => $programLatihan?->cabor?->nama ?? '-',
            'periode_mulai' => $programLatihan?->periode_mulai ?? '-',
            'periode_selesai' => $programLatihan?->periode_selesai ?? '-',
            'jenis_target' => $request->jenis_target ?? '-',
        ];
        $data = $this->repository->customCreateEdit($data);

        return inertia('modules/target-latihan/Create', $data);
    }

    public function store(TargetLatihanRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        
        // Untuk target kelompok, set peruntukan ke null
        if ($data['jenis_target'] === 'kelompok') {
            $data['peruntukan'] = null;
        }
        
        $this->repository->create($data);

        return redirect()->route('target-latihan.index')->with('success', 'Target latihan berhasil ditambahkan!');
    }

    public function edit($id = '')
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = $this->commonData + [
            'item' => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data, $item);

        return inertia('modules/target-latihan/Edit', $data);
    }

    public function update(TargetLatihanRequest $request, $id)
    {
        $data = $this->repository->validateRequest($request);
        
        // Untuk target kelompok, set peruntukan ke null
        if ($data['jenis_target'] === 'kelompok') {
            $data['peruntukan'] = null;
        }
        
        $this->repository->update($id, $data);

        return redirect()->route('target-latihan.index')->with('success', 'Target latihan berhasil diperbarui!');
    }

    public function show($id)
    {
        $item = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/target-latihan/Show', [
            'item' => $item,
        ]);
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        return redirect()->route('target-latihan.index')->with('success', 'Target latihan berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:target_latihan,id',
        ]);
        $this->repository->delete_selected($request->ids);

        return response()->json(['message' => 'Target latihan terpilih berhasil dihapus!']);
    }

    // =====================
    // NESTED MODULAR CRUD
    // =====================
    public function nestedIndex($program_id, $jenis_target, Request $request)
    {
        $programLatihan = ProgramLatihan::with(['cabor', 'caborKategori'])->findOrFail($program_id);
        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        // Filter by program_id & jenis_target
        $request->merge(['program_latihan_id' => $program_id, 'jenis_target' => $jenis_target]);
        
        // Filter by peruntukan only for target individu
        $peruntukan = $request->get('peruntukan');
        if ($peruntukan && $jenis_target === 'individu') {
            $request->merge(['peruntukan' => $peruntukan]);
        }
        
        // Set default peruntukan if not provided for target individu
        if (!$peruntukan && $jenis_target === 'individu') {
            $request->merge(['peruntukan' => 'atlet']);
        }
        $data = $this->repository->customIndex($data);
        $data['infoHeader'] = [
            'program_latihan_id' => $programLatihan->id,
            'nama_program' => $programLatihan->nama_program,
            'cabor_nama' => $programLatihan->cabor ? $programLatihan->cabor->nama : '-',
            'cabor_kategori_nama' => $programLatihan->caborKategori ? $programLatihan->caborKategori->nama : null,
            'periode_mulai' => $programLatihan->periode_mulai,
            'periode_selesai' => $programLatihan->periode_selesai,
            'jenis_target' => $jenis_target,
            'peruntukan' => $jenis_target === 'individu' ? ($peruntukan ?: 'atlet') : null,
        ];

        return inertia('modules/target-latihan/Index', $data);
    }

    public function nestedCreate($program_id, $jenis_target)
    {
        $programLatihan = ProgramLatihan::with(['cabor', 'caborKategori'])->findOrFail($program_id);
        $data = $this->commonData + ['item' => null];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data['infoHeader'] = [
            'program_latihan_id' => $programLatihan->id,
            'nama_program' => $programLatihan->nama_program,
            'cabor_nama' => $programLatihan->cabor ? $programLatihan->cabor->nama : '-',
            'cabor_kategori_nama' => $programLatihan->caborKategori ? $programLatihan->caborKategori->nama : null,
            'periode_mulai' => $programLatihan->periode_mulai,
            'periode_selesai' => $programLatihan->periode_selesai,
            'jenis_target' => $jenis_target,
        ];
        $data = $this->repository->customCreateEdit($data);

        return inertia('modules/target-latihan/Create', $data);
    }

    public function nestedStore($program_id, $jenis_target, Request $request)
    {
        $programLatihan = ProgramLatihan::findOrFail($program_id);
        $request->merge([
            'program_latihan_id' => $program_id,
            'jenis_target' => $jenis_target,
        ]);
        $data = $this->repository->validateRequest($request);
        
        // Untuk target kelompok, set peruntukan ke null
        if ($jenis_target === 'kelompok') {
            $data['peruntukan'] = null;
        }
        
        $this->repository->create($data);

        return redirect()->route('program-latihan.target-latihan.index', [$program_id, $jenis_target])->with('success', 'Target latihan berhasil ditambahkan!');
    }

    public function nestedShow($program_id, $jenis_target, $target_id)
    {
        $item = $this->repository->getDetailWithRelations($target_id);

        return inertia('modules/target-latihan/Show', ['item' => $item]);
    }

    public function nestedEdit($program_id, $jenis_target, $target_id)
    {
        $item = $this->repository->getById($target_id);
        $programLatihan = ProgramLatihan::with(['cabor', 'caborKategori'])->findOrFail($program_id);
        $data = $this->commonData + ['item' => $item];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data['infoHeader'] = [
            'program_latihan_id' => $programLatihan->id,
            'nama_program' => $programLatihan->nama_program,
            'cabor_nama' => $programLatihan->cabor ? $programLatihan->cabor->nama : '-',
            'cabor_kategori_nama' => $programLatihan->caborKategori ? $programLatihan->caborKategori->nama : null,
            'periode_mulai' => $programLatihan->periode_mulai,
            'periode_selesai' => $programLatihan->periode_selesai,
            'jenis_target' => $jenis_target,
        ];
        $data = $this->repository->customCreateEdit($data, $item);

        return inertia('modules/target-latihan/Edit', $data);
    }

    public function nestedUpdate($program_id, $jenis_target, $target_id, Request $request)
    {
        $request->merge([
            'program_latihan_id' => $program_id,
            'jenis_target' => $jenis_target,
        ]);
        $data = $this->repository->validateRequest($request);
        
        // Untuk target kelompok, set peruntukan ke null
        if ($jenis_target === 'kelompok') {
            $data['peruntukan'] = null;
        }
        
        $this->repository->update($target_id, $data);

        return redirect()->route('program-latihan.target-latihan.index', [$program_id, $jenis_target])->with('success', 'Target latihan berhasil diperbarui!');
    }

    public function nestedDestroy($program_id, $jenis_target, $target_id)
    {
        $this->repository->delete($target_id);

        return redirect()->route('program-latihan.target-latihan.index', [$program_id, $jenis_target])->with('success', 'Target latihan berhasil dihapus!');
    }
}
