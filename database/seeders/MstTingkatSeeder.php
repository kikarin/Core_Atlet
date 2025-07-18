<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MstTingkat;

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
