<?php

namespace Database\Seeders;

use App\Models\TenagaPendukung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TenagaPendukungSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        TenagaPendukung::truncate();
        Schema::enableForeignKeyConstraints();

        $tenagaPendukungs = [
            [
                'nik'           => '1111222233334444',
                'nama'          => 'Slamet Raharjo',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Jakarta',
                'tanggal_lahir' => '1990-01-01',
                'alamat'        => 'Jl. Melati 10',
                'no_hp'         => '081111111111',
                'email'         => 'slamet@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1111222233334445',
                'nama'          => 'Dewi Sartika',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Bandung',
                'tanggal_lahir' => '1992-02-02',
                'alamat'        => 'Jl. Mawar 20',
                'no_hp'         => '082222222222',
                'email'         => 'dewi@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1111222233334446',
                'nama'          => 'Bambang Sutrisno',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Surabaya',
                'tanggal_lahir' => '1988-03-03',
                'alamat'        => 'Jl. Kenanga 30',
                'no_hp'         => '083333333333',
                'email'         => 'bambang@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1111222233334447',
                'nama'          => 'Siti Nurhaliza',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Semarang',
                'tanggal_lahir' => '1995-04-04',
                'alamat'        => 'Jl. Anggrek 40',
                'no_hp'         => '084444444444',
                'email'         => 'siti@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1111222233334448',
                'nama'          => 'Rudi Hartono',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Yogyakarta',
                'tanggal_lahir' => '1993-05-05',
                'alamat'        => 'Jl. Dahlia 50',
                'no_hp'         => '085555555555',
                'email'         => 'rudi@example.com',
                'is_active'     => 1,
            ],
        ];

        foreach ($tenagaPendukungs as $tp) {
            TenagaPendukung::create($tp);
        }
    }
}
