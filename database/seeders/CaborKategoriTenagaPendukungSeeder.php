<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaborKategori;
use App\Models\TenagaPendukung;
use App\Models\MstJenisTenagaPendukung;
use Illuminate\Support\Facades\DB;

class CaborKategoriTenagaPendukungSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriList = CaborKategori::all();
        $tenagaList = TenagaPendukung::all();
        $jenisList = MstJenisTenagaPendukung::pluck('id')->toArray();

        foreach ($kategoriList as $kategori) {
            $randomTenaga = $tenagaList->random(rand(1, min(3, $tenagaList->count())));
            foreach ($randomTenaga as $tenaga) {
                DB::table('cabor_kategori_tenaga_pendukung')->updateOrInsert([
                    'cabor_id' => $kategori->cabor_id,
                    'cabor_kategori_id' => $kategori->id,
                    'tenaga_pendukung_id' => $tenaga->id,
                ], [
                    'is_active' => rand(0, 1),
                    'jenis_tenaga_pendukung_id' => $jenisList ? $jenisList[array_rand($jenisList)] : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
} 