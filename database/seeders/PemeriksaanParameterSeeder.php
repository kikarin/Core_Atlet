<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PemeriksaanParameter;
use App\Models\Pemeriksaan;

class PemeriksaanParameterSeeder extends Seeder
{
    public function run(): void
    {
        $pemeriksaan = Pemeriksaan::first();
        if (!$pemeriksaan) {
            return;
        }
        $data = [
            [
                'pemeriksaan_id' => $pemeriksaan->id,
                'nama_parameter' => 'Tekanan Darah',
                'satuan' => 'mmHg',
            ],
            [
                'pemeriksaan_id' => $pemeriksaan->id,
                'nama_parameter' => 'Denyut Nadi',
                'satuan' => 'bpm',
            ],
            [
                'pemeriksaan_id' => $pemeriksaan->id,
                'nama_parameter' => 'Suhu Tubuh',
                'satuan' => '°C',
            ],
        ];
        foreach ($data as $item) {
            PemeriksaanParameter::create($item);
        }
    }
} 