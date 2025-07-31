<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\CaborKategori;
use App\Models\Pemeriksaan;
use App\Models\TenagaPendukung;
use Illuminate\Database\Seeder;

class PemeriksaanSeeder extends Seeder
{
    public function run(): void
    {
        $cabor    = Cabor::first();
        $kategori = CaborKategori::first();
        $tenaga   = TenagaPendukung::first();
        if (! $cabor || ! $kategori || ! $tenaga) {
            return;
        }
        $data = [
            [
                'cabor_id'            => $cabor->id,
                'cabor_kategori_id'   => $kategori->id,
                'tenaga_pendukung_id' => $tenaga->id,
                'nama_pemeriksaan'    => 'Pemeriksaan Fisik Awal',
                'tanggal_pemeriksaan' => '2025-08-01',
                'status'              => 'belum',
            ],
            [
                'cabor_id'            => $cabor->id,
                'cabor_kategori_id'   => $kategori->id,
                'tenaga_pendukung_id' => $tenaga->id,
                'nama_pemeriksaan'    => 'Pemeriksaan Lanjutan',
                'tanggal_pemeriksaan' => '2025-09-01',
                'status'              => 'sebagian',
            ],
        ];
        foreach ($data as $item) {
            Pemeriksaan::create($item);
        }
    }
}
