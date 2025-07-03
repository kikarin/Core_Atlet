<?php

namespace App\Http\Controllers;

use App\Models\Atlet;
use Illuminate\Http\Request;
use App\Http\Requests\AtletRequest;
use App\Repositories\AtletRepository;
use App\Traits\BaseTrait;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

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

    public function store(AtletRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        $model = $this->repository->create($data);
        return redirect()->route('atlet.edit', $model->id)->with('success', 'Atlet berhasil ditambahkan!');
    }

    public function update(AtletRequest $request, $id)
    {
        try {
            // Debug logging
            Log::info('AtletController: update method called', [
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
            
            Log::info('AtletController: data after validation', [
                'data' => $data
            ]);
            
            // Update the record
            $model = $this->repository->update($id, $data);
            
            return redirect()->route('atlet.edit', $model->id)->with('success', 'Atlet berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('Error updating atlet: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data atlet.');
        }
    }

    public function show($id)
{
    $item = $this->repository->getDetailWithRelations($id);
    return Inertia::render('modules/atlet/Show', [
        'item' => $item,
    ]);
}
}
