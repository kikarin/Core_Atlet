<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenagaPendukungRequest;
use App\Imports\TenagaPendukungImport;
use App\Repositories\TenagaPendukungRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TenagaPendukungController extends Controller implements HasMiddleware
{
    use BaseTrait;

    private $repository;

    private $request;

    public function __construct(TenagaPendukungRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = TenagaPendukungRequest::createFromBase($request);
        $this->initialize();
        $this->commonData['kode_first_menu'] = $this->kode_menu;
        $this->commonData['kode_second_menu'] = null;
    }

    public static function middleware(): array
    {
        $className = class_basename(__CLASS__);
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
            'data' => $data['tenaga_pendukungs'],
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

    public function apiShow($id)
    {
        try {
            // Debug logging
            Log::info('Tenaga Pendukung Controller: apiShow method called', [
                'id' => $id,
            ]);

            $item = $this->repository->getDetailWithRelations($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenaga Pendukung  tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching Tenaga Pendukung  detail: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data Tenaga Pendukung ',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(TenagaPendukungRequest $request)
    {
        $data = $this->repository->validateRequest($request);
        $model = $this->repository->create($data);

        return redirect()->route('tenaga-pendukung.edit', $model->id)->with('success', 'Tenaga Pendukung berhasil ditambahkan!');
    }

    public function update(TenagaPendukungRequest $request, $id)
    {
        try {
            Log::info('TenagaPendukungController: update method called', [
                'id' => $id,
                'all_data' => $request->all(),
                'validated_data' => $request->validated(),
            ]);
            $data = $this->repository->validateRequest($request);
            if ($request->hasFile('file')) {
                $data['file'] = $request->file('file');
            }
            Log::info('TenagaPendukungController: data after validation', [
                'data' => $data,
            ]);
            $model = $this->repository->update($id, $data);

            return redirect()->route('tenaga-pendukung.edit', $model->id)->with('success', 'Tenaga Pendukung berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error updating tenaga pendukung: '.$e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data tenaga pendukung.');
        }
    }

    public function show($id)
    {
        $item = $this->repository->getDetailWithRelations($id);

        return Inertia::render('modules/tenaga-pendukung/Show', [
            'item' => $item,
        ]);
    }

    public function index()
    {
        return Inertia::render('modules/tenaga-pendukung/Index', $this->repository->customIndex([]));
    }

    public function create()
    {
        return Inertia::render('modules/tenaga-pendukung/Create', $this->repository->customCreateEdit([]));
    }

    public function edit($id)
    {
        return Inertia::render('modules/tenaga-pendukung/Edit', $this->repository->customCreateEdit([], $this->repository->getById($id)));
    }

    public function destroy($id)
    {
        $this->repository->delete($id);

        return redirect()->route('tenaga-pendukung.index')->with('success', 'Tenaga Pendukung berhasil dihapus!');
    }

    public function destroy_selected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:tenaga_pendukungs,id',
        ]);
        $this->repository->delete_selected($request->ids);

        return response()->json(['message' => 'Tenaga Pendukung terpilih berhasil dihapus!']);
    }

    public function import(Request $request)
    {
        Log::info('TenagaPendukungController: import method called', [
            'file_name' => $request->file('file')?->getClientOriginalName(),
            'file_size' => $request->file('file')?->getSize(),
        ]);
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);
        try {
            $import = new TenagaPendukungImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
            Log::info('TenagaPendukungController: import successful', [
                'rows_processed' => $import->getRowCount(),
                'success_count' => $import->getSuccessCount(),
                'error_count' => $import->getErrorCount(),
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
                'data' => [
                    'total_rows' => $import->getRowCount(),
                    'success_count' => $import->getSuccessCount(),
                    'error_count' => $import->getErrorCount(),
                    'errors' => $import->getErrors(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('TenagaPendukungController: import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal import: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
