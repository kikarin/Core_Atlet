<?php

namespace Database\Seeders;

use App\Models\Pelatih;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PelatihSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Pelatih::truncate();
        Schema::enableForeignKeyConstraints();

        $pelatihs = [
            [
                'nik'           => '9876543210123456',
                'nama'          => 'Agus Salim',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Medan',
                'tanggal_lahir' => '1980-01-01',
                'alamat'        => 'Jl. Sudirman 1',
                'no_hp'         => '081298765432',
                'email'         => 'agus@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '9876543210123457',
                'nama'          => 'Sri Rahayu',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Padang',
                'tanggal_lahir' => '1982-02-02',
                'alamat'        => 'Jl. Diponegoro 2',
                'no_hp'         => '081298765433',
                'email'         => 'sri@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '9876543210123458',
                'nama'          => 'Joko Susilo',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Solo',
                'tanggal_lahir' => '1985-03-03',
                'alamat'        => 'Jl. Slamet Riyadi 3',
                'no_hp'         => '081298765434',
                'email'         => 'joko@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '9876543210123459',
                'nama'          => 'Maya Sari',
                'jenis_kelamin' => 'P',
                'tempat_lahir'  => 'Malang',
                'tanggal_lahir' => '1988-04-04',
                'alamat'        => 'Jl. Ijen 4',
                'no_hp'         => '081298765435',
                'email'         => 'maya@example.com',
                'is_active'     => 1,
            ],
            [
                'nik'           => '9876543210123460',
                'nama'          => 'Dedi Mulyadi',
                'jenis_kelamin' => 'L',
                'tempat_lahir'  => 'Bogor',
                'tanggal_lahir' => '1990-05-05',
                'alamat'        => 'Jl. Pajajaran 5',
                'no_hp'         => '081298765436',
                'email'         => 'dedi@example.com',
                'is_active'     => 1,
            ],
        ];

        foreach ($pelatihs as $pelatih) {
            Pelatih::create($pelatih);
        }
    }
}
