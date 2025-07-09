<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cabor;
use App\Models\CaborKategori;

class CaborKategoriSeeder extends Seeder
{
    public function run(): void
    {
        $cabors = Cabor::pluck('id', 'nama');
        $data = [
            // Sepak Bola
            ['cabor_id' => $cabors['Sepak Bola'] ?? null, 'nama' => 'Putra', 'deskripsi' => 'Sepak bola putra'],
            ['cabor_id' => $cabors['Sepak Bola'] ?? null, 'nama' => 'Putri', 'deskripsi' => 'Sepak bola putri'],
            // Bulu Tangkis
            ['cabor_id' => $cabors['Bulu Tangkis'] ?? null, 'nama' => 'Tunggal', 'deskripsi' => 'Bulu tangkis tunggal'],
            ['cabor_id' => $cabors['Bulu Tangkis'] ?? null, 'nama' => 'Ganda', 'deskripsi' => 'Bulu tangkis ganda'],
            // Basket
            ['cabor_id' => $cabors['Basket'] ?? null, 'nama' => '3x3', 'deskripsi' => 'Basket 3 lawan 3'],
            ['cabor_id' => $cabors['Basket'] ?? null, 'nama' => '5x5', 'deskripsi' => 'Basket 5 lawan 5'],
        ];
        foreach ($data as $item) {
            if ($item['cabor_id']) {
                CaborKategori::firstOrCreate([
                    'cabor_id' => $item['cabor_id'],
                    'nama' => $item['nama'],
                ], $item);
            }
        }
    }
} 