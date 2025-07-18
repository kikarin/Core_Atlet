<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\UsersMenu;
use Illuminate\Support\Facades\DB;

class UsersMenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        UsersMenu::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $usersMenus = [
                [
                    'nama'          => 'Dashboard',
                    'kode'          => 'DASHBOARD',
                    'url'           => '/dashboard',
                    'icon'          => 'LayoutGrid',
                    'rel'           => 0,
                    'urutan'        => 1,
                    'permission_id' => 'Dashboard Show',
                ],
                [
                    'nama'          => 'Atlet',
                    'kode'          => 'ATLET',
                    'url'           => '/atlet',
                    'icon'          => 'UserCircle2',
                    'rel'           => 0,
                    'urutan'        => 11,
                    'permission_id' => 'Atlet Show',
                ],
                [
                    'nama'          => 'Pelatih',
                    'kode'          => 'PELATIH',
                    'url'           => '/pelatih',
                    'icon'          => 'UserCircle2',
                    'rel'           => 0,
                    'urutan'        => 21,
                    'permission_id' => 'Pelatih Show',
                ],
                [
                    'nama'          => 'Tenaga Pendukung',
                    'kode'          => 'TENAGA-PENDUKUNG',
                    'url'           => '/tenaga-pendukung',
                    'icon'          => 'UserCircle2',
                    'rel'           => 0,
                    'urutan'        => 31,
                    'permission_id' => 'Tenaga Pendukung Show',
                ],
                [
                    'nama'          => 'Cabor',
                    'kode'          => '',
                    'url'           => '/cabor',
                    'icon'          => 'Flag',
                    'rel'           => 0,
                    'urutan'        => 41,
                    'permission_id' => '',
                    'children'      => [
                        [
                            'nama'          => 'Cabor',
                            'kode'          => 'CABOR',
                            'url'           => '/cabor',
                            'urutan'        => 1,
                            'permission_id' => 'Cabor Show',
                        ],
                        [
                            'nama'          => 'Kategori',
                            'kode'          => 'CABOR-KATEGORI',
                            'url'           => '/cabor-kategori',
                            'urutan'        => 2,
                            'permission_id' => 'Kategori Show',
                        ],
                    ],
                ],
                [
                    'nama'          => 'Program Latihan',
                    'kode'          => 'PROGRAM-LATIHAN',
                    'url'           => '/program-latihan',
                    'icon'          => 'CalendarSync',
                    'rel'           => 0,
                    'urutan'        => 51,
                    'permission_id' => 'Program Latihan Show',
                ],
                [
                    'nama'          => 'Data Master',
                    'kode'          => 'DATA-MASTER',
                    'url'           => '/data-master',
                    'icon'          => 'FileStack',
                    'rel'           => 0,
                    'urutan'        => 101,
                    'permission_id' => '',
                    'children'      => [
                        [
                            'nama'          => 'Tingkat',
                            'kode'          => 'DATA-MASTER-TINGKAT',
                            'url'           => '/data-master/tingkat',
                            'urutan'        => 1,
                            'permission_id' => 'Master Tingkat Show',
                        ],
                        [
                            'nama'          => 'Jenis Dokumen',
                            'kode'          => 'DATA-MASTER-JENIS-DOKUMEN',
                            'url'           => '/data-master/jenis-dokumen',
                            'urutan'        => 2,
                            'permission_id' => 'Master Jenis Dokumen Show',
                        ],
                        [
                            'nama'          => 'Posisi Atlet',
                            'kode'          => 'DATA-MASTER-POSISI-ATLET',
                            'url'           => '/data-master/posisi-atlet',
                            'urutan'        => 1,
                            'permission_id' => 'Mst Posisi Atlet Show',
                        ],
                        [
                            'nama'          => 'Jenis Pelatih',
                            'kode'          => 'DATA-MASTER-JENIS-PELATIH',
                            'url'           => '/data-master/jenis-pelatih',
                            'urutan'        => 3,
                            'permission_id' => 'Mst Jenis Pelatih Show',
                        ],
                        [
                            'nama'          => 'Jenis Tenaga Pendukung',
                            'kode'          => 'DATA-MASTER-JENIS-TENAGA-PENDUKUNG',
                            'url'           => '/data-master/jenis-tenaga-pendukung',
                            'urutan'        => 4,
                            'permission_id' => 'Mst Jenis Tenaga Pendukung Show',
                        ],
                        [
                            'nama'          => 'Kecamatan',
                            'kode'          => 'DATA-MASTER-KECAMATAN',
                            'url'           => '/data-master/kecamatan',
                            'urutan'        => 5,
                            'permission_id' => 'Mst Kecamatan Show',
                        ],
                        [
                            'nama'          => 'Desa/Kelurahan',
                            'kode'          => 'DATA-MASTER-DESA',
                            'url'           => '/data-master/desa',
                            'urutan'        => 6,
                            'permission_id' => 'Mst Desa Show',
                        ],
                    ],
                ],
                [
                    'nama'          => 'Users',
                    'kode'          => 'USERS',
                    'url'           => '/users',
                    'icon'          => 'Users',
                    'rel'           => 0,
                    'urutan'        => 102,
                    'permission_id' => 'Users Show',
                ],
                [
                    'nama'          => 'Menu & Permissions',
                    'kode'          => 'USERS-MANAGEMENT',
                    'url'           => '/menu-permissions',
                    'icon'          => 'ShieldCheck',
                    'rel'           => 0,
                    'urutan'        => 103,
                    'permission_id' => '',
                    'children'      => [
                        [
                            'nama'          => 'Menu',
                            'kode'          => 'USERS-MENU',
                            'url'           => '/menu-permissions/menus',
                            'urutan'        => 1,
                            'permission_id' => 'Users Menu Show',
                        ],
                        [
                            'nama'          => 'Role',
                            'kode'          => 'USERS-ROLE',
                            'url'           => '/menu-permissions/roles',
                            'urutan'        => 2,
                            'permission_id' => 'Role Show',
                        ],
                        [
                            'nama'          => 'Permission',
                            'kode'          => 'USERS-PERMISSION',
                            'url'           => '/menu-permissions/permissions',
                            'urutan'        => 3,
                            'permission_id' => 'Permission Show',
                        ],
                        [
                            'nama'          => 'Activity Log',
                            'kode'          => 'USERS-LOG',
                            'url'           => '/menu-permissions/logs',
                            'urutan'        => 4,
                            'permission_id' => 'Activity Log Show',
                        ],
                    ],
                ],
            ];

        $this->insertMenus($usersMenus);
    }

    private function insertMenus(array $menus, $parentId = 0)
    {
        foreach ($menus as $menuData) {
            if (!isset($menuData['nama'])) {
                continue;
            }

            $children = $menuData['children'] ?? null;
            unset($menuData['children']);

            // Generate kode kalau belum ada
            if (!isset($menuData['kode'])) {
                $menuData['kode'] = str_replace(' ', '-', strtoupper($menuData['nama']));
            }

            // Generate URL kalau belum ada
            if (!isset($menuData['url'])) {
                $menuData['url'] = str_replace(' ', '-', strtolower($menuData['nama']));
            }

            $menuData['rel'] = $parentId;

            // Cek permission_id
            if (isset($menuData['permission_id'])) {
                if (is_string($menuData['permission_id'])) {
                    $permission                = Permission::where('name', $menuData['permission_id'])->first();
                    $menuData['permission_id'] = $permission?->id;
                }
            } else {
                $permission                = Permission::where('name', $menuData['nama'] . ' Show')->first();
                $menuData['permission_id'] = $permission?->id;
            }

            // Cek apakah sudah ada, kalau ada update, kalau belum insert
            $existingMenu = UsersMenu::where('kode', $menuData['kode'])->first();

            if ($existingMenu) {
                $existingMenu->update($menuData);
                $menuId = $existingMenu->id;
            } else {
                $newMenu = UsersMenu::create($menuData);
                $menuId  = $newMenu->id;
            }

            // Recursive ke children kalau ada
            if ($children) {
                $this->insertMenus($children, $menuId);
            }
        }
    }
}
