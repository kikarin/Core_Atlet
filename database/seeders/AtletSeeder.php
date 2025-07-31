<?php

namespace Database\Seeders;

use App\Models\Atlet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AtletSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Atlet::truncate();
        Schema::enableForeignKeyConstraints();

        $atlets = [
            [
                'nik'           => '1234567890123456',
                'nama'          => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Jakarta',
                'tanggal_lahir' => '2000-01-01',
                'alamat'        => 'Jl. Merdeka 1',
                'no_hp'         => '081234567890',
                'email'         => 'budi@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1234567890123457',
                'nama'          => 'Siti Aminah',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Bandung',
                'tanggal_lahir' => '2001-02-02',
                'alamat'        => 'Jl. Melati 2',
                'no_hp'         => '081234567891',
                'email'         => 'siti@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1234567890123458',
                'nama'          => 'Andi Wijaya',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Surabaya',
                'tanggal_lahir' => '2002-03-03',
                'alamat'        => 'Jl. Kenanga 3',
                'no_hp'         => '081234567892',
                'email'         => 'andi@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1234567890123459',
                'nama'          => 'Dewi Lestari',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Semarang',
                'tanggal_lahir' => '2003-04-04',
                'alamat'        => 'Jl. Mawar 4',
                'no_hp'         => '081234567893',
                'email'         => 'dewi@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '1234567890123460',
                'nama'          => 'Rizky Pratama',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Yogyakarta',
                'tanggal_lahir' => '2004-05-05',
                'alamat'        => 'Jl. Anggrek 5',
                'no_hp'         => '081234567894',
                'email'         => 'rizky@example.com',
                'is_active'     => 1,
            ],
        ];

        foreach ($atlets as $atlet) {
            Atlet::create($atlet);
        }
    }
}
