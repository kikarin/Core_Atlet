<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MstJenisTenagaPendukung;

class MstJenisTenagaPendukungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Tenaga Pendukung Fisik'],
            ['nama' => 'Tenaga Pendukung Teknik'],
            ['nama' => 'Tenaga Pendukung Mental'],
        ];

        foreach ($data as $item) {
            MstJenisTenagaPendukung::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
} 