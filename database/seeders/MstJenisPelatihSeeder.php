<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MstJenisPelatih;

class MstJenisPelatihSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama' => 'Pelatih Fisik'],
            ['nama' => 'Pelatih Teknik'],
            ['nama' => 'Pelatih Mental'],
        ];

        foreach ($data as $item) {
            MstJenisPelatih::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
