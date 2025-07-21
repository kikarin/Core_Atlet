<?php

namespace App\Http\Controllers;

use App\Http\Requests\KecamatanRequest;
use App\Repositories\KecamatanRepository;
use App\Traits\BaseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class KecamatanController extends Controller implements HasMiddleware
{
    use BaseTrait;
    private $repository;
    private $request;

    public function __construct(KecamatanRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request    = KecamatanRequest::createFromBase($request);
        $this->initialize();
        $this->commonData['kode_first_menu']  = $this->kode_menu;
        $this->commonData['kode_second_menu'] = null;
    }

    public static function middleware(): array
    {
        $prefix = 'Mst Kecamatan';
        return [
            new Middleware("can:$prefix Add", only: ['create', 'store']),
            new Middleware("can:$prefix Detail", only: ['show']),
            new Middleware("can:$prefix Edit", only: ['edit', 'update']),
            new Middleware("can:$prefix Delete", only: ['destroy', 'destroy_selected']),
            new Middleware("can:$prefix Show", only: ['index', 'apiIndex']),
        ];
    }

    public function apiIndex()
    {
        $data = $this->repository->customIndex([]);
        return response()->json([
            'data' => $data['kecamatans'] ?? $data['data'] ?? $data,
            'meta' => [
                'total'        => $data['total']       ?? 0,
                'current_page' => $data['currentPage'] ?? 1,
                'per_page'     => $data['perPage']     ?? 10,
                'search'       => $data['search']      ?? '',
                'sort'         => $data['sort']        ?? '',
                'order'        => $data['order']       ?? 'asc',
            ],
        ]);
    }

    public function index()
    {
        $this->repository->customProperty(__FUNCTION__);
        $data = $this->commonData + [];
        if ($this->check_permission == true) {
            $data = array_merge($data, $this->getPermission());
        }
        $data = $this->repository->customIndex($data);
        return inertia('modules/data-master/kecamatan/Index', $data);
    }

    public function show($id)
    {
        $item      = $this->repository->getById($id);
        $itemArray = $item->toArray();
        return inertia('modules/data-master/kecamatan/Show', [
            'item' => $itemArray,
        ]);
    }
}
