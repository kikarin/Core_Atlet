<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersMenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CategoryPermissionController;
use App\Http\Controllers\AtletController;
use App\Http\Controllers\AtletOrangTuaController;
use App\Models\MstKecamatan;
use App\Models\MstDesa;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Users Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('/users', UsersController::class)->names('users');
    Route::get('/users/{id}/login-as', [UsersController::class, 'login_as'])->name('users.login-as');
    Route::post('/users/switch-role', [UsersController::class, 'switchRole'])->name('users.switch-role');
    Route::get('/api/users', [UsersController::class, 'apiIndex']);
    Route::post('/users/destroy-selected', [UsersController::class, 'destroy_selected'])->name('users.destroy_selected');
});

// Menus Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('/menu-permissions/menus', UsersMenuController::class)
        ->names('menus');
    Route::get('/api/users-menu', [UsersMenuController::class, 'getMenus'])->name('api.users-menu');
    Route::get('/api/menus', [UsersMenuController::class, 'apiIndex'])->name('api.menus');
    Route::post('/menu-permissions/menus/destroy-selected', [UsersMenuController::class, 'destroy_selected'])->name('menus.destroy-selected');
});

// Roles Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('/menu-permissions/roles', RoleController::class)->names('roles');
    Route::get('/api/roles', [RoleController::class, 'apiIndex']);
    Route::get('/menu-permissions/roles/set-permissions/{id}', [RoleController::class, 'set_permission'])->name('roles.set-permission');
    Route::post('/menu-permissions/roles/set-permissions/{id}', [RoleController::class, 'set_permission_action'])->name('roles.set-permission-action');
    Route::post('/menu-permissions/roles/destroy-selected', [RoleController::class, 'destroy_selected'])->name('roles.destroy_selected');
});

// Permissions & Category Permissions Routes
Route::middleware(['auth', 'verified'])->prefix('menu-permissions')->group(function () {
    Route::get('/permissions/create-permission', [PermissionController::class, 'create'])->name('permissions.create-permission');
    Route::post('/permissions/store-permission', [PermissionController::class, 'store'])->name('permissions.store-permission');
    Route::get('/permissions/{id}/edit-permission', [PermissionController::class, 'edit'])->name('permissions.edit-permission');
    Route::put('/permissions/update-permission/{id}', [PermissionController::class, 'update'])->name('permissions.update-permission');
    Route::get('/permissions/{id}/detail', [PermissionController::class, 'show'])->name('permissions.detail');
    Route::delete('/permissions/delete-permission/{id}', [PermissionController::class, 'destroy'])->name('permissions.delete-permission');

    // Kategori Permission (Category)
    Route::resource('/permissions', CategoryPermissionController::class)->names('permissions');
    // Custom: Show/Edit kategori by /category/{id}
    Route::get('/permissions/category/{id}', [CategoryPermissionController::class, 'show'])->name('permissions.category.show');
    Route::get('/permissions/category/{id}/edit', [CategoryPermissionController::class, 'edit'])->name('permissions.category.edit');
});

// Activity Logs Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/menu-permissions/logs', fn () => Inertia::render('modules/activity-logs/Index'))->name('access-control.logs.index');
    Route::get('/menu-permissions/logs/{id}', [ActivityLogController::class, 'show'])->name('access-control.logs.show');
    Route::get('/api/activity-logs', [ActivityLogController::class, 'apiIndex']);
    Route::delete('/menu-permissions/logs/{id}', [ActivityLogController::class, 'destroy'])->name('access-control.logs.destroy');
    Route::post('/menu-permissions/logs/destroy-selected', [ActivityLogController::class, 'destroy_selected'])->name('access-control.logs.destroy-selected');
});

// Atlet Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('/atlet', AtletController::class)->names('atlet');
    Route::get('/api/atlet', [AtletController::class, 'apiIndex']);
    Route::post('/atlet/destroy-selected', [AtletController::class, 'destroy_selected'])->name('atlet.destroy_selected');

    // START - Atlet Orang Tua Routes (Nested under Atlet)
    Route::prefix('atlet/{atlet_id}')->group(function () {
        Route::get('orang-tua', [AtletOrangTuaController::class, 'getByAtletId'])->name('atlet.orang-tua.show');
        Route::post('orang-tua', [AtletOrangTuaController::class, 'store'])->name('atlet.orang-tua.store');
        Route::put('orang-tua/{id}', [AtletOrangTuaController::class, 'update'])->name('atlet.orang-tua.update');
        Route::delete('orang-tua/{id}', [AtletOrangTuaController::class, 'destroy'])->name('atlet.orang-tua.destroy');
    });
    // END - Atlet Orang Tua Routes
});

// API Kecamatan & Kelurahan 
Route::get('/api/kecamatan', function() {
    return MstKecamatan::select('id', 'nama')->orderBy('nama')->get();
});
Route::get('/api/kelurahan-by-kecamatan/{id_kecamatan}', function($id_kecamatan) {
    return MstDesa::where('id_kecamatan', $id_kecamatan)->select('id', 'nama')->orderBy('nama')->get();
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
