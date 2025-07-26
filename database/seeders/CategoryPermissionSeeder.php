<?php

namespace Database\Seeders;

use App\Models\CategoryPermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class CategoryPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $categoryPermissions = [
            [
                'name'       => 'Users',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Users Menu',
                'permission' => 'CRUD',
            ],
            [
                'name'              => 'Role',
                'permission'        => 'CRUD',
                'permission_common' => ['Role Set Permission'],
            ],
            [
                'name'       => 'Permission',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Category Permission',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Activity Log',
                'permission' => ['Activity Log Show', 'Activity Log Detail', 'Activity Log Delete'],
            ],
            [
                'name'       => 'Dashboard',
                'permission' => ['Dashboard Show'],
            ],
            [
                'name'       => 'Atlet',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Atlet Orang Tua',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Atlet Sertifikat',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Atlet Prestasi',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Atlet Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Atlet Kesehatan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pelatih',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pelatih Sertifikat',
                'permission' => 'CRUD',
            ]
            ,
            [
                'name'       => 'Pelatih Prestasi',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pelatih Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pelatih Kesehatan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Tenaga Pendukung',
                'permission' => 'CRUD',
            ],
            [
                'name'      => 'Tenaga Pendukung Sertifikat',
                'permission'=> 'CRUD',
            ],
            [
                'name'      => 'Tenaga Pendukung Prestasi',
                'permission'=> 'CRUD',
            ],
            [
                'name'       => 'Tenaga Pendukung Kesehatan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Tenaga Pendukung Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Mst Tingkat',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Mst Jenis Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Mst Kecamatan',
                'permission' => ['Mst Kecamatan Show', 'Mst Kecamatan Detail'],
            ],
            [
                'name'       => 'Mst Desa',
                'permission' => ['Mst Desa Show', 'Mst Desa Detail'],
            ],
            [
                'name'       => 'Mst Posisi Atlet',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Mst Jenis Pelatih',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Mst Jenis Tenaga Pendukung',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Cabor',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Cabor Kategori',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Cabor Kategori Atlet',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Cabor Kategori Pelatih',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Cabor Kategori Tenaga Pendukung',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Program Latihan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Target Latihan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Rencana Latihan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pemeriksaan',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pemeriksaan Parameter',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pemeriksaan Peserta',
                'permission' => 'CRUD',
            ],
            [
                'name'       => 'Pemeriksaan Peserta Parameter',
                'permission' => 'CRUD',
            ],
        ];

        $listCrud = ['Show', 'Add', 'Edit', 'Detail', 'Delete'];

        foreach ($categoryPermissions as $category) {
            $existingCategoryPermission = CategoryPermission::firstOrCreate(
                ['name' => $category['name']],
                ['name' => $category['name']]
            );

            $listPermission = [];

            if ($category['permission'] === 'CRUD') {
                foreach ($listCrud as $action) {
                    $listPermission[] = "{$category['name']} {$action}";
                }
            } else {
                foreach ($category['permission'] as $value) {
                    $listPermission[] = $value;
                }
            }

            if (isset($category['permission_common'])) {
                foreach ($category['permission_common'] as $value) {
                    $listPermission[] = $value;
                }
            }

            foreach ($listPermission as $permissionName) {
                Permission::updateOrCreate(
                    ['name' => $permissionName],
                    [
                        'category_permission_id' => $existingCategoryPermission->id,
                        'name'                   => $permissionName,
                    ]
                );
            }
        }

        $this->command->info('CategoryPermissionSeeder table seeded!');
    }
}
