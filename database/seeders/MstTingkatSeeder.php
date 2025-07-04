<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MstTingkat;
use Illuminate\Support\Facades\Schema;

class MstTingkatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tingkats = [
            ['nama' => 'Desa/Kelurahan'],
            ['nama' => 'Kecamatan'],
            ['nama' => 'Kabupaten/Kota'],
            ['nama' => 'Provinsi'],
            ['nama' => 'Nasional'],
            ['nama' => 'Internasional'],
        ];

        foreach ($tingkats as $tingkat) {
            MstTingkat::create($tingkat);
        }
    }
}
