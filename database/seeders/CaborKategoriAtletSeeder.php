<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CaborKategori;
use App\Models\Atlet;
use App\Models\MstPosisiAtlet;
use Illuminate\Support\Facades\DB;

class CaborKategoriAtletSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriList = CaborKategori::all();
        $atletList    = Atlet::all();
        $posisiList   = MstPosisiAtlet::pluck('id')->toArray();

        foreach ($kategoriList as $kategori) {
            $randomAtlets = $atletList->random(rand(2, min(5, $atletList->count())));
            foreach ($randomAtlets as $atlet) {
                DB::table('cabor_kategori_atlet')->updateOrInsert([
                    'cabor_id'          => $kategori->cabor_id,
                    'cabor_kategori_id' => $kategori->id,
                    'atlet_id'          => $atlet->id,
                ], [
                    'is_active'       => rand(0, 1),
                    'posisi_atlet_id' => $posisiList ? $posisiList[array_rand($posisiList)] : null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }
}
