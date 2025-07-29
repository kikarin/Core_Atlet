<?php

namespace Database\Seeders;

use App\Models\ProgramLatihan;
use App\Models\TargetLatihan;
use Illuminate\Database\Seeder;

class TargetLatihanSeeder extends Seeder
{
    public function run(): void
    {
        $program = ProgramLatihan::first();
        if (! $program) {
            return;
        }

        $data = [
            [
                'program_latihan_id' => $program->id,
                'jenis_target' => 'individu',
                'deskripsi' => 'Menguasai teknik smash',
                'satuan' => 'kali',
                'nilai_target' => '10',
            ],
            [
                'program_latihan_id' => $program->id,
                'jenis_target' => 'kelompok',
                'deskripsi' => 'Kerjasama tim dalam latihan passing',
                'satuan' => 'set',
                'nilai_target' => '5',
            ],
            [
                'program_latihan_id' => $program->id,
                'jenis_target' => 'individu',
                'deskripsi' => 'Meningkatkan kecepatan lari',
                'satuan' => 'detik',
                'nilai_target' => '12',
            ],
        ];
        foreach ($data as $item) {
            TargetLatihan::firstOrCreate([
                'program_latihan_id' => $item['program_latihan_id'],
                'jenis_target' => $item['jenis_target'],
                'deskripsi' => $item['deskripsi'],
            ], $item);
        }
    }
}
