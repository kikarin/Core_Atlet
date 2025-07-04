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
                'name' => 'Users',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Users Menu',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Role',
                'permission' => 'CRUD',
                'permission_common' => ['Role Set Permission'],
            ],
            [
                'name' => 'Permission',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Category Permission',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Activity Log',
                'permission' => ['Activity Log Show', 'Activity Log Detail', 'Activity Log Delete'],
            ],
            [
                'name' => 'Dashboard',
                'permission' => ['Dashboard Show'],
            ],
            [
                'name' => 'Atlet',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Atlet Orang Tua',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Atlet Sertifikat',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Atlet Prestasi',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Atlet Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Atlet Kesehatan',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Pelatih',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Pelatih Sertifikat',
                'permission' => 'CRUD',
            ]
            ,
            [
                'name' => 'Pelatih Prestasi',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Pelatih Dokumen',
                'permission' => 'CRUD',
            ],
            [
                'name' => 'Pelatih Kesehatan',
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
                        'name' => $permissionName,
                    ]
                );
            }
        }

        $this->command->info('CategoryPermissionSeeder table seeded!');
    }
}
