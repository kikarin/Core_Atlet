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
            ['nama' => 'Tenaga Pendukung Medis'],
            ['nama' => 'Tenaga Pendukung Psikologi'],
            ['nama' => 'Tenaga Pendukung Nutrisi'],
            ['nama' => 'Tenaga Pendukung Teknologi'],
            ['nama' => 'Tenaga Pendukung Administrasi'],
            ['nama' => 'Tenaga Pendukung Lainnya'],
        ];

        foreach ($data as $item) {
            MstJenisTenagaPendukung::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
