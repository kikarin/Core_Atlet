<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\MstTingkat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Exception;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $this->call(CategoryIdentitySeeder::class);
        $this->call(IdentitySeeder::class);
        $this->call(CategoryPermissionSeeder::class);
        $this->call(UsersMenuSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(SetRolePermissionSeeder::class);
        $this->call(ImportSqlSeeder::class);
        $this->call(MstJenisDokumenSeeder::class);
        $this->call(MstTingkatSeeder::class);
        $this->call(AtletSeeder::class);
        $this->call(AtletOrangTuaSeeder::class);
        $this->call(AtletSertifikatSeeder::class);
        $this->call(AtletPrestasiSeeder::class);
        $this->call(AtletDokumenSeeder::class);
        $this->call(AtletKesehatanSeeder::class);
        $this->call(PelatihSeeder::class);
        $this->call(PelatihSertifikatSeeder::class);
        $this->call(PelatihPrestasiSeeder::class);
        $this->call(PelatihKesehatanSeeder::class);
        $this->call(PelatihDokumenSeeder::class);
        $this->call(MstJenisPelatihSeeder::class);
        $this->call(CaborSeeder::class);
        $this->call(CaborKategoriSeeder::class);
    }

}
