<?php

namespace App\Repositories;

use App\Models\Desa;
use App\Models\MstDesa;
use App\Models\MstKecamatan;
use App\Traits\RepositoryTrait;

class DesaRepository
{
    use RepositoryTrait;

    protected $model;
    protected $kecamatanRepository;

    public function __construct(MstDesa $model, KecamatanRepository $kecamatanRepository)
    {
        $this->model               = $model;
        $this->orderByColumnsArray = ["id_kecamatan" => "asc", "nama" => "asc"];
        $this->with                = ["kecamatan"];

        $this->kecamatanRepository = $kecamatanRepository;
    }

    public function customCreateEdit($data, $item = null)
    {
        $data += [
            "listKecamatan"   => $this->kecamatanRepository->getAll()->pluck('nama', 'id')->toArray(),
        ];
        return $data;
    }

    public function getByIdKecamatan($id_kecamatan)
    {
        $record = $this->model::where("id_kecamatan", $id_kecamatan)->get();
        return $record;
    }
}
