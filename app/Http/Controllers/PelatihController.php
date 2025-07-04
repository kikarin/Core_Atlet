<?php

namespace App\Http\Controllers;

use App\Models\Pelatih;
use Illuminate\Http\Request;
use App\Http\Requests\PelatihRequest;
use App\Repositories\PelatihRepository;
use App\Traits\BaseTrait;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

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

    public function store(PelatihRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        $model = $this->repository->create($data);
        return redirect()->route('pelatih.edit', $model->id)->with('success', 'Pelatih berhasil ditambahkan!');
    }

    public function update(PelatihRequest $request, $id)
    {
        try {
            // Debug logging
            Log::info('PelatihController: update method called', [
                'id' => $id,
                'all_data' => $request->all(),
                'validated_data' => $request->validated()
            ]);
            
            // Use the same validation as store method
            $data = $this->repository->validateRequest($request);
            
            // Handle file upload if exists
            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file');
            }
            
            Log::info('PelatihController: data after validation', [
                'data' => $data
            ]);
            
            // Update the record
            $model = $this->repository->update($id, $data);
            
            return redirect()->route('pelatih.edit', $model->id)->with('success', 'Pelatih berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('Error updating pelatih: ' . $e->getMessage());
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
} 