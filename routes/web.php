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
use App\Http\Controllers\PelatihController;
use App\Http\Controllers\PelatihSertifikatController;
use App\Http\Controllers\PelatihPrestasiController;
use App\Http\Controllers\PelatihKesehatanController;
use App\Http\Controllers\PelatihDokumenController;

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

    // START - Pelatih Routes
    Route::resource('/pelatih', PelatihController::class)->names('pelatih');
    Route::get('/api/pelatih', [PelatihController::class, 'apiIndex']);
    Route::post('/pelatih/destroy-selected', [PelatihController::class, 'destroy_selected'])->name('pelatih.destroy_selected');

    // Pelatih Sertifikat Routes (Nested under Pelatih)
    Route::prefix('pelatih/{pelatih_id}')->group(function () {
        Route::get('sertifikat', [PelatihSertifikatController::class, 'index'])->name('pelatih.sertifikat.index');
        Route::get('sertifikat/create', [PelatihSertifikatController::class, 'create'])->name('pelatih.sertifikat.create');
        Route::post('sertifikat', [PelatihSertifikatController::class, 'store'])->name('pelatih.sertifikat.store');
        Route::get('sertifikat/{id}/edit', [PelatihSertifikatController::class, 'edit'])->name('pelatih.sertifikat.edit');
        Route::put('sertifikat/{id}', [PelatihSertifikatController::class, 'update'])->name('pelatih.sertifikat.update');
        Route::delete('sertifikat/{id}', [PelatihSertifikatController::class, 'destroy'])->name('pelatih.sertifikat.destroy');
        Route::post('sertifikat/destroy-selected', [PelatihSertifikatController::class, 'destroy_selected'])->name('pelatih.sertifikat.destroy_selected');
        Route::get('sertifikat/{id}', [PelatihSertifikatController::class, 'show'])->name('pelatih.sertifikat.show');

    // Pelatih Prestasi Routes (Nested under Pelatih)
        Route::get('prestasi', [PelatihPrestasiController::class, 'index'])->name('pelatih.prestasi.index');
        Route::get('prestasi/create', [PelatihPrestasiController::class, 'create'])->name('pelatih.prestasi.create');
        Route::post('prestasi', [PelatihPrestasiController::class, 'store'])->name('pelatih.prestasi.store');
        Route::get('prestasi/{id}/edit', [PelatihPrestasiController::class, 'edit'])->name('pelatih.prestasi.edit');
        Route::put('prestasi/{id}', [PelatihPrestasiController::class, 'update'])->name('pelatih.prestasi.update');
        Route::delete('prestasi/{id}', [PelatihPrestasiController::class, 'destroy'])->name('pelatih.prestasi.destroy');
        Route::post('prestasi/destroy-selected', [PelatihPrestasiController::class, 'destroy_selected'])->name('pelatih.prestasi.destroy_selected');
        Route::get('prestasi/{id}', [PelatihPrestasiController::class, 'show'])->name('pelatih.prestasi.show');

    // Pelatih Kesehatan Routes (Nested under Pelatih)
        Route::get('kesehatan', [PelatihKesehatanController::class, 'getByPelatihId'])->name('pelatih.kesehatan.show');
        Route::post('kesehatan', [PelatihKesehatanController::class, 'store'])->name('pelatih.kesehatan.store');
        Route::put('kesehatan/{id}', [PelatihKesehatanController::class, 'update'])->name('pelatih.kesehatan.update');
        Route::delete('kesehatan/{id}', [PelatihKesehatanController::class, 'destroy'])->name('pelatih.kesehatan.destroy');

    // Pelatih Dokumen Routes (Nested under Pelatih)
        Route::get('dokumen', [PelatihDokumenController::class, 'index'])->name('pelatih.dokumen.index');
        Route::get('dokumen/create', [PelatihDokumenController::class, 'create'])->name('pelatih.dokumen.create');
        Route::post('dokumen', [PelatihDokumenController::class, 'store'])->name('pelatih.dokumen.store');
        Route::get('dokumen/{id}/edit', [PelatihDokumenController::class, 'edit'])->name('pelatih.dokumen.edit');
        Route::put('dokumen/{id}', [PelatihDokumenController::class, 'update'])->name('pelatih.dokumen.update');
        Route::delete('dokumen/{id}', [PelatihDokumenController::class, 'destroy'])->name('pelatih.dokumen.destroy');
        Route::post('dokumen/destroy-selected', [PelatihDokumenController::class, 'destroy_selected'])->name('pelatih.dokumen.destroy_selected');
        Route::get('dokumen/{id}', [PelatihDokumenController::class, 'show'])->name('pelatih.dokumen.show');
    });
    // END - Pelatih Routes
});

// API Kecamatan & Kelurahan 
Route::get('/api/kecamatan', function() {
    return MstKecamatan::select('id', 'nama')->orderBy('nama')->get();
});
Route::get('/api/kelurahan-by-kecamatan/{id_kecamatan}', function($id_kecamatan) {
    return MstDesa::where('id_kecamatan', $id_kecamatan)->select('id', 'nama')->orderBy('nama')->get();
});

// API ATLET
// API untuk Sertifikat per Atlet
Route::get('/api/atlet/{atlet_id}/sertifikat', [AtletSertifikatController::class, 'apiIndex']);

// API untuk Prestasi per Atlet
Route::get('/api/atlet/{atlet_id}/prestasi', [AtletPrestasiController::class, 'apiIndex']);

// API untuk Dokumen per Atlet
Route::get('/api/atlet/{atlet_id}/dokumen', [AtletDokumenController::class, 'apiIndex']);

// API PELATIH
// API untuk Sertifikat per Pelatih
Route::get('/api/pelatih/{pelatih_id}/sertifikat', [PelatihSertifikatController::class, 'apiIndex']);

// API untuk Prestasi per Pelatih
Route::get('/api/pelatih/{pelatih_id}/prestasi', [PelatihPrestasiController::class, 'apiIndex']);

// API untuk Dokumen per Pelatih
Route::get('/api/pelatih/{pelatih_id}/dokumen', [PelatihDokumenController::class, 'apiIndex']);



// API untuk Master Tingkat
Route::get('/api/tingkat', function() {
    return App\Models\MstTingkat::select('id', 'nama')->orderBy('nama')->get();
});

// API untuk Master Jenis Dokumen
Route::get('/api/jenis-dokumen', function() {
    return App\Models\MstJenisDokumen::select('id', 'nama')->orderBy('nama')->get();
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
