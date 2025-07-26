<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RefStatusPemeriksaan;

class RefStatusPemeriksaanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['nama' => 'Normal'],
            ['nama' => 'Tidak Normal'],
            ['nama' => 'Cedera Ringan'],
            ['nama' => 'Cedera Berat'],
            ['nama' => 'Perlu Tindak Lanjut'],
        ];

        foreach ($statuses as $status) {
            RefStatusPemeriksaan::create($status);
        }
    }
}
