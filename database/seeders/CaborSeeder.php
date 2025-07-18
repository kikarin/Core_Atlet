<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabor;

class CaborSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nama' => 'Sepak Bola', 'deskripsi' => 'Olahraga tim populer di dunia.'],
            ['nama' => 'Bulu Tangkis', 'deskripsi' => 'Olahraga raket cepat dan dinamis.'],
            ['nama' => 'Basket', 'deskripsi' => 'Olahraga bola berkelompok dengan ring.'],
        ];
        foreach ($data as $item) {
            Cabor::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
