<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AtletSertifikatRequest;
use App\Repositories\AtletSertifikatRepository;
use App\Traits\BaseTrait;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class AtletSertifikatController extends Controller implements HasMiddleware
{
    use BaseTrait;
    private $repository;
    private $request;

    public function __construct(AtletSertifikatRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request    = $request;
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
            new Middleware("can:$permission Add", only: ['store']),
            new Middleware("can:$permission Detail", only: ['getByAtletId']),
            new Middleware("can:$permission Edit", only: ['update']),
            new Middleware("can:$permission Delete", only: ['destroy']),
        ];
    }

    public function getByAtletId($atletId)
    {
        $sertifikat = $this->repository->getByAtletId($atletId);
        return response()->json($sertifikat);
    }

    public function store(AtletSertifikatRequest $request, $atlet_id)
    {
        $data = $request->validated();
        $data['atlet_id'] = $atlet_id;
        $model = $this->repository->create($data);
        if ($model) {
            return redirect()->back()->with('success', 'Sertifikat berhasil ditambahkan!')->with('sertifikatId', $model->id);
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah sertifikat.');
        }
    }

    public function update(AtletSertifikatRequest $request, $atlet_id, $id)
    {
        $data = $request->validated();
        $data['atlet_id'] = $atlet_id;
        $model = $this->repository->update($id, $data);
        if ($model) {
            return redirect()->back()->with('success', 'Sertifikat berhasil diperbarui!')->with('sertifikatId', $model->id);
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui sertifikat.');
        }
    }

    public function destroy($atlet_id, $id)
    {
        $this->repository->delete($id);
        return redirect()->back()->with('success', 'Sertifikat berhasil dihapus!');
    }
} 