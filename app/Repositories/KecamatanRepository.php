<?php

namespace App\Repositories;

use App\Models\MstKecamatan;
use App\Traits\RepositoryTrait;

class KecamatanRepository
{
    use RepositoryTrait;

    protected $model;

    public function __construct(MstKecamatan $model)
    {
        $this->model            = $model;
    }
}
