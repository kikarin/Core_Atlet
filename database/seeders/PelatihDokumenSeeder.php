<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PelatihDokumen;
use App\Models\Pelatih;
use App\Models\MstJenisDokumen;
use Illuminate\Support\Facades\Schema;

class PelatihDokumenSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        PelatihDokumen::truncate();
        Schema::enableForeignKeyConstraints();

        $pelatihIds = Pelatih::pluck('id')->toArray();
        $jenisDokumenId = MstJenisDokumen::first()->id ?? 1;
        $data = [
            [
                'pelatih_id' => $pelatihIds[0] ?? 1,
                'jenis_dokumen_id' => $jenisDokumenId,
                'nomor' => 'PDOK001',
            ],
            [
                'pelatih_id' => $pelatihIds[1] ?? 2,
                'jenis_dokumen_id' => $jenisDokumenId,
                'nomor' => 'PDOK002',
            ],
            [
                'pelatih_id' => $pelatihIds[2] ?? 3,
                'jenis_dokumen_id' => $jenisDokumenId,
                'nomor' => 'PDOK003',
            ],
            [
                'pelatih_id' => $pelatihIds[3] ?? 4,
                'jenis_dokumen_id' => $jenisDokumenId,
                'nomor' => 'PDOK004',
            ],
            [
                'pelatih_id' => $pelatihIds[4] ?? 5,
                'jenis_dokumen_id' => $jenisDokumenId,
                'nomor' => 'PDOK005',
            ],
        ];
        foreach ($data as $item) {
            PelatihDokumen::create($item);
        }
    }
} 