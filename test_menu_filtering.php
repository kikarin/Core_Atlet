<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing menu filtering...\n";

// List all users
$users = \App\Models\User::with('role')->get();
echo "Available users:\n";
foreach ($users as $user) {
    $roleName = $user->role ? $user->role->name : 'No role';
    echo "  - {$user->name} ({$user->email}) - Role: {$roleName}\n";
}

// Get first user with role
$user = \App\Models\User::with('role')->whereNotNull('current_role_id')->first();
if (!$user || !$user->role) {
    echo "\nNo user with role found\n";
    exit;
}

echo "\nTesting with user: {$user->name} (Role: {$user->role->name})\n";

// Check role permissions
$role = $user->role;
echo "\nRole permissions:\n";
$permissions = $role->permissions;
foreach ($permissions as $permission) {
    echo "  - {$permission->name}\n";
}

// Get menus using the app container
$repository = app(\App\Repositories\UsersMenuRepository::class);
$menus = $repository->getMenus();

echo "\nAvailable menus:\n";
foreach ($menus as $menu) {
    echo "  - {$menu->nama}\n";
    if ($menu->children && $menu->children->count() > 0) {
        foreach ($menu->children as $child) {
            echo "    - {$child->nama}\n";
        }
    }
} 