<?php

namespace App\Http\Controllers;

use App\Http\Requests\CaborKategoriAtletRequest;
use App\Repositories\CaborKategoriAtletRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use App\Models\CaborKategori;

class CaborKategoriAtletController extends Controller implements HasMiddleware
{
    use BaseTrait;
    private $repository;
    private $request;

    public function __construct(Request $request, CaborKategoriAtletRepository $repository)
    {
        $this->repository = $repository;
        $this->request = $request;
        $this->initialize();
        $this->route = 'cabor-kategori-atlet';
        $this->commonData['kode_first_menu'] = 'CABOR';
        $this->commonData['kode_second_menu'] = $this->kode_menu;
    }

    public static function middleware(): array
    {
        $className = class_basename(__CLASS__);
        $permission = str_replace('Controller', '', $className);
        $permission = trim(implode(' ', preg_split('/(?=[A-Z])/', $permission)));
        return [
            new Middleware("can:$permission Add", only: ['create', 'store', 'storeMultiple']),
            new Middleware("can:$permission Detail", only: ['show']),
            new Middleware("can:$permission Edit", only: ['edit', 'update']),
            new Middleware("can:$permission Delete", only: ['destroy', 'destroy_selected']),
        ];
    }

    public function index()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'titlePage' => 'Cabor Kategori Atlet',
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        return inertia('modules/cabor-kategori-atlet/Index', $data);
    }

    public function create()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [
            'titlePage' => 'Tambah Cabor Kategori Atlet',
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data);
        return inertia('modules/cabor-kategori-atlet/Create', $data);
    }

    public function store(CaborKategoriAtletRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        $this->repository->batchInsert($data);
        return redirect()->route('cabor-kategori-atlet.index')->with('success', 'Atlet berhasil ditambahkan ke kategori!');
    }

    public function show($id)
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = $this->commonData + [
            'titlePage' => 'Detail Cabor Kategori Atlet',
            'item' => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customShow($data, $item);
        return inertia('modules/cabor-kategori-atlet/Show', $data);
    }

    public function edit($id)
    {
        $this->repository->customProperty(__FUNCTION__, ['id' => $id]);
        $item = $this->repository->getById($id);
        $data = $this->commonData + [
            'titlePage' => 'Edit Cabor Kategori Atlet',
            'item' => $item,
        ];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customCreateEdit($data, $item);
        return inertia('modules/cabor-kategori-atlet/Edit', $data);
    }

    public function update(CaborKategoriAtletRequest $request, $id)
    {
        $data = $this->repository->validateRequest($request);
        $this->repository->update($id, $data);
        return redirect()->route('cabor-kategori-atlet.index')->with('success', 'Cabor Kategori Atlet berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->repository->delete($id);
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Data berhasil dihapus']);
        }
        return redirect()->route('cabor-kategori-atlet.index')->with('success', 'Cabor Kategori Atlet berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih data yang akan dihapus!');
        }
        $this->repository->delete_selected($ids);
        return redirect()->route('cabor-kategori-atlet.index')->with('success', 'Data berhasil dihapus!');
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);
        return response()->json([
            'data' => $data['records'],
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

    // Method untuk halaman daftar atlet per kategori
    public function atletByKategori($caborKategoriId)
    {
        $this->repository->customProperty(__FUNCTION__, ['cabor_kategori_id' => $caborKategoriId]);
        $caborKategori = app(CaborKategori::class)->with('cabor')->find($caborKategoriId);
        
        if (!$caborKategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
        }

        $data = $this->commonData + [
            'titlePage' => 'Daftar Atlet - ' . $caborKategori->nama,
            'caborKategori' => $caborKategori,
        ];
        
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        
        return inertia('modules/cabor-kategori-atlet/AtletByKategori', $data);
    }

    // Method untuk halaman tambah multiple atlet
    public function createMultiple($caborKategoriId)
    {
        $this->repository->customProperty(__FUNCTION__, ['cabor_kategori_id' => $caborKategoriId]);
        $caborKategori = app(CaborKategori::class)->with('cabor')->find($caborKategoriId);
        
        if (!$caborKategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
        }

        $data = $this->commonData + [
            'titlePage' => 'Tambah Multiple Atlet - ' . $caborKategori->nama,
            'caborKategori' => $caborKategori,
        ];
        
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        
        return inertia('modules/cabor-kategori-atlet/CreateMultiple', $data);
    }

    // Method untuk store multiple atlet
public function storeMultiple(Request $request, $caborKategoriId)
{
    try {
        Log::info('storeMultiple called', [
            'caborKategoriId' => $caborKategoriId,
            'request_data' => $request->all()
        ]);

        $caborKategori = app(CaborKategori::class)->find($caborKategoriId);
        if (!$caborKategori) {
            return redirect()->back()->with('error', 'Kategori tidak ditemukan!');
        }

        // Merge ke request sebelum validasi
        $request->merge([
            'cabor_kategori_id' => $caborKategoriId,
            'cabor_id' => $caborKategori->cabor_id,
            'posisi_atlet_id' => $request->input('posisi_atlet_id'),
        ]);

        $validatedData = $this->repository->validateRequest($request);

        // Pastikan posisi_atlet_id tetap diteruskan ke repository
        if ($request->has('posisi_atlet_id')) {
            $validatedData['posisi_atlet_id'] = $request->input('posisi_atlet_id');
        }

        Log::info('Validated data', $validatedData);

        $this->repository->batchInsert($validatedData);

        Log::info('Batch insert successful');

        return redirect()->route('cabor-kategori-atlet.atlet-by-kategori', $caborKategoriId)
            ->with('success', 'Atlet berhasil ditambahkan ke kategori!');
    } catch (\Exception $e) {
        Log::error('Error in storeMultiple', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all(),
        ]);

        return redirect()->back()->with('error', 'Gagal menambahkan atlet: ' . $e->getMessage());
    }
}
} 