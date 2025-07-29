<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtletRequest;
use App\Imports\AtletImport;
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
        $this->request = AtletRequest::createFromBase($request);
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
            'data' => $data['atlets'],
            'meta' => [
                'total' => $data['total'],
                'current_page' => $data['currentPage'],
                'per_page' => $data['perPage'],
                'search' => $data['search'],
                'sort' => $data['sort'],
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
                'data' => $item,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching atlet detail: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data atlet',
                'error' => $e->getMessage(),
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
            $import = new AtletImport;
            Excel::import($import, $request->file('file'));

            Log::info('AtletController: import successful', [
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
            Log::error('AtletController: import failed', [
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
