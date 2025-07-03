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
use App\Http\Controllers\AtletSertifikatController;
use App\Http\Controllers\AtletPrestasiController;
use App\Http\Controllers\AtletDokumenController;
use App\Http\Controllers\AtletKesehatanController;

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

        // Atlet Sertifikat Routes (Nested under Atlet)
        Route::get('sertifikat', [AtletSertifikatController::class, 'index'])->name('atlet.sertifikat.index');
        Route::get('sertifikat/create', [AtletSertifikatController::class, 'create'])->name('atlet.sertifikat.create');
        Route::post('sertifikat', [AtletSertifikatController::class, 'store'])->name('atlet.sertifikat.store');
        Route::get('sertifikat/{id}/edit', [AtletSertifikatController::class, 'edit'])->name('atlet.sertifikat.edit');
        Route::put('sertifikat/{id}', [AtletSertifikatController::class, 'update'])->name('atlet.sertifikat.update');
        Route::delete('sertifikat/{id}', [AtletSertifikatController::class, 'destroy'])->name('atlet.sertifikat.destroy');
        Route::post('sertifikat/destroy-selected', [AtletSertifikatController::class, 'destroy_selected'])->name('atlet.sertifikat.destroy_selected');
        Route::get('sertifikat/{id}', [AtletSertifikatController::class, 'show'])->name('atlet.sertifikat.show');

        // Atlet Prestasi Routes (Nested under Atlet)
        Route::get('prestasi', [AtletPrestasiController::class, 'index'])->name('atlet.prestasi.index');
        Route::get('prestasi/create', [AtletPrestasiController::class, 'create'])->name('atlet.prestasi.create');
        Route::post('prestasi', [AtletPrestasiController::class, 'store'])->name('atlet.prestasi.store');
        Route::get('prestasi/{id}/edit', [AtletPrestasiController::class, 'edit'])->name('atlet.prestasi.edit');
        Route::put('prestasi/{id}', [AtletPrestasiController::class, 'update'])->name('atlet.prestasi.update');
        Route::delete('prestasi/{id}', [AtletPrestasiController::class, 'destroy'])->name('atlet.prestasi.destroy');
        Route::post('prestasi/destroy-selected', [AtletPrestasiController::class, 'destroy_selected'])->name('atlet.prestasi.destroy_selected');
        Route::get('prestasi/{id}', [AtletPrestasiController::class, 'show'])->name('atlet.prestasi.show');

        // Atlet Dokumen Routes (Nested under Atlet)
        Route::get('dokumen', [AtletDokumenController::class, 'index'])->name('atlet.dokumen.index');
        Route::get('dokumen/create', [AtletDokumenController::class, 'create'])->name('atlet.dokumen.create');
        Route::post('dokumen', [AtletDokumenController::class, 'store'])->name('atlet.dokumen.store');
        Route::get('dokumen/{id}/edit', [AtletDokumenController::class, 'edit'])->name('atlet.dokumen.edit');
        Route::put('dokumen/{id}', [AtletDokumenController::class, 'update'])->name('atlet.dokumen.update');
        Route::delete('dokumen/{id}', [AtletDokumenController::class, 'destroy'])->name('atlet.dokumen.destroy');
        Route::post('dokumen/destroy-selected', [AtletDokumenController::class, 'destroy_selected'])->name('atlet.dokumen.destroy_selected');
        Route::get('dokumen/{id}', [AtletDokumenController::class, 'show'])->name('atlet.dokumen.show');

        // Atlet Kesehatan Routes (Nested under Atlet)
        Route::get('kesehatan', [AtletKesehatanController::class, 'getByAtletId'])->name('atlet.kesehatan.show');
        Route::post('kesehatan', [AtletKesehatanController::class, 'store'])->name('atlet.kesehatan.store');
        Route::put('kesehatan/{id}', [AtletKesehatanController::class, 'update'])->name('atlet.kesehatan.update');
        Route::delete('kesehatan/{id}', [AtletKesehatanController::class, 'destroy'])->name('atlet.kesehatan.destroy');
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

// API untuk Sertifikat per Atlet
Route::get('/api/atlet/{atlet_id}/sertifikat', [App\Http\Controllers\AtletSertifikatController::class, 'apiIndex']);

// API untuk Prestasi per Atlet
Route::get('/api/atlet/{atlet_id}/prestasi', [App\Http\Controllers\AtletPrestasiController::class, 'apiIndex']);

// API untuk Dokumen per Atlet
Route::get('/api/atlet/{atlet_id}/dokumen', [App\Http\Controllers\AtletDokumenController::class, 'apiIndex']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
