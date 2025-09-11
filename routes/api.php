<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\CsrfController;
use App\Http\Controllers\Api\ProgramLatihanController;
use App\Http\Controllers\Api\RencanaLatihanController as ApiRencanaLatihanController;
use App\Http\Controllers\Api\PemeriksaanController;
use App\Http\Controllers\Api\PemeriksaanParameterController as ApiPemeriksaanParameterController;
use App\Http\Controllers\Api\TurnamenController;
use App\Http\Controllers\Api\TargetLatihanController as ApiTargetLatihanController;
use App\Http\Controllers\Api\PemeriksaanPesertaController as ApiPemeriksaanPesertaController;
use App\Http\Controllers\RefStatusPemeriksaanController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\MstParameterController;
use App\Http\Controllers\UsersMenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// CSRF Cookie route (harus di atas semua routes)
Route::get('/sanctum/csrf-cookie', [CsrfController::class, 'getCsrfCookie']);

// Public routes (tidak perlu authentication)
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (perlu authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'getSettings']);
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/reset-password', [SettingsController::class, 'resetPassword']);
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount']);

    // Program Latihan (Mobile)
    Route::get('/program-latihan/mobile', [ProgramLatihanController::class, 'index']);
    Route::get('/program-latihan/{id}', [ProgramLatihanController::class, 'show']);
    Route::get('/program-latihan/cabor/list', [ProgramLatihanController::class, 'getCaborList']);
    Route::get('/program-latihan/cabor/list-for-create', [ProgramLatihanController::class, 'getCaborListForCreate']);
    Route::get('/program-latihan/cabor/{caborId}/kategori', [ProgramLatihanController::class, 'getCaborKategoriByCabor']);

    // Program Latihan CRUD (Restricted to Superadmin and Pelatih)
    Route::middleware('program.latihan.permission')->group(function () {
        Route::post('/program-latihan', [ProgramLatihanController::class, 'store']);
        Route::put('/program-latihan/{id}', [ProgramLatihanController::class, 'update']);
        Route::delete('/program-latihan/{id}', [ProgramLatihanController::class, 'destroy']);
    });

    // Rencana Latihan (Mobile)
    Route::get('/program-latihan/{programId}/rencana-latihan', [ApiRencanaLatihanController::class, 'index']);
    Route::get('/rencana-latihan/{id}', [ApiRencanaLatihanController::class, 'show']);
    Route::get('/rencana-latihan/{rencanaId}/peserta', [ApiRencanaLatihanController::class, 'participants']);

    // Rencana Latihan - Form Support Endpoints
    Route::get('/program-latihan/{programId}/target-latihan-list', [ApiRencanaLatihanController::class, 'getTargetLatihanList']);
    Route::get('/cabor-kategori/{caborKategoriId}/atlet-list', [ApiRencanaLatihanController::class, 'getAtletList']);
    Route::get('/cabor-kategori/{caborKategoriId}/pelatih-list', [ApiRencanaLatihanController::class, 'getPelatihList']);
    Route::get('/cabor-kategori/{caborKategoriId}/tenaga-pendukung-list', [ApiRencanaLatihanController::class, 'getTenagaPendukungList']);

    // Rencana Latihan CRUD (Restricted to Superadmin, Admin, and Pelatih)
    Route::middleware('program.latihan.permission')->group(function () {
        Route::post('/rencana-latihan', [ApiRencanaLatihanController::class, 'store']);
        Route::put('/rencana-latihan/{id}', [ApiRencanaLatihanController::class, 'update']);
        Route::delete('/rencana-latihan/{id}', [ApiRencanaLatihanController::class, 'destroy']);
    });

    // Target Latihan (Mobile)
    Route::get('/rencana-latihan/{rencanaId}/targets', [ApiRencanaLatihanController::class, 'targets']);
    Route::get('/rencana-latihan/{rencanaId}/targets/{targetId}', [ApiRencanaLatihanController::class, 'targetDetail']);

    // Target Latihan Peserta (Mobile)
    Route::get('/program-latihan/{programId}/rencana/{rencanaId}/peserta/{pesertaId}/targets/{pesertaType?}', [ApiRencanaLatihanController::class, 'participantTargets']);
    Route::get('/program-latihan/{programId}/rencana/{rencanaId}/peserta/{pesertaId}/target/{targetId}/grafik/{pesertaType?}', [ApiRencanaLatihanController::class, 'participantTargetChart']);

    // Target Latihan (Mobile + CRUD)
    Route::get('/program-latihan/{programId}/target-latihan', [ApiTargetLatihanController::class, 'index']);
    Route::get('/target-latihan/{id}', [ApiTargetLatihanController::class, 'show']);

    // Target Latihan CRUD (Restricted to Superadmin, Admin, and Pelatih)
    Route::middleware('target.latihan.permission')->group(function () {
        Route::post('/target-latihan', [ApiTargetLatihanController::class, 'store']);
        Route::put('/target-latihan/{id}', [ApiTargetLatihanController::class, 'update']);
        Route::delete('/target-latihan/{id}', [ApiTargetLatihanController::class, 'destroy']);
    });

    // Pemeriksaan (Mobile)
    Route::get('/pemeriksaan/mobile', [PemeriksaanController::class, 'index']);
    Route::get('/pemeriksaan/{id}', [PemeriksaanController::class, 'show']);
    Route::get('/pemeriksaan/cabor/list', [PemeriksaanController::class, 'getCaborList']);
    Route::get('/pemeriksaan/cabor/list-for-create', [PemeriksaanController::class, 'getCaborListForCreate']);
    Route::get('/pemeriksaan/cabor/{caborId}/kategori', [PemeriksaanController::class, 'getCaborKategoriByCabor']);
    Route::get('/pemeriksaan/cabor-kategori/{caborKategoriId}/tenaga-pendukung', [PemeriksaanController::class, 'getTenagaPendukungByCaborKategori']);
    Route::get('/pemeriksaan/{pemeriksaanId}/peserta', [PemeriksaanController::class, 'peserta']);
    Route::get('/pemeriksaan/{pemeriksaanId}/parameter', [PemeriksaanController::class, 'parameter']);
    Route::get('/pemeriksaan/{pemeriksaanId}/parameter/{parameterId}', [PemeriksaanController::class, 'parameterDetail']);
    // Ref Status Pemeriksaan (read-only)
    Route::get('/pemeriksaan/ref-status/list', [RefStatusPemeriksaanController::class, 'index']);

    // Pemeriksaan Parameter CRUD (Restricted to Superadmin, Admin, Tenaga Pendukung)
    Route::middleware('pemeriksaan.permission')->group(function () {
        Route::post('/pemeriksaan/{pemeriksaanId}/parameter', [ApiPemeriksaanParameterController::class, 'store']);
        Route::put('/pemeriksaan/{pemeriksaanId}/parameter/{id}', [ApiPemeriksaanParameterController::class, 'update']);
        Route::delete('/pemeriksaan/{pemeriksaanId}/parameter/{id}', [ApiPemeriksaanParameterController::class, 'destroy']);
    });
    Route::get('/pemeriksaan/{pemeriksaanId}/peserta/{pesertaId}/parameter', [PemeriksaanController::class, 'pesertaParameterList']);
    Route::get('/pemeriksaan/{pemeriksaanId}/peserta/{pesertaId}/parameter/{parameterId}/grafik', [PemeriksaanController::class, 'pesertaParameterChart']);

    // Pemeriksaan CRUD (Restricted to Superadmin, Admin, and Tenaga Pendukung)
    Route::middleware('pemeriksaan.permission')->group(function () {
        Route::post('/pemeriksaan', [PemeriksaanController::class, 'store']);
        Route::put('/pemeriksaan/{id}', [PemeriksaanController::class, 'update']);
        Route::delete('/pemeriksaan/{id}', [PemeriksaanController::class, 'destroy']);

        // Pemeriksaan Peserta CRUD (Superadmin, Admin, Tenaga Pendukung)
        Route::post('/pemeriksaan/{pemeriksaanId}/peserta', [ApiPemeriksaanPesertaController::class, 'store']);
        Route::put('/pemeriksaan/{pemeriksaanId}/peserta/{id}', [ApiPemeriksaanPesertaController::class, 'update']);
        Route::delete('/pemeriksaan/{pemeriksaanId}/peserta/{id}', [ApiPemeriksaanPesertaController::class, 'destroy']);
    });

    // Pemeriksaan Peserta - Form Support (Mobile)
    Route::get('/pemeriksaan/{pemeriksaanId}/peserta/list', [ApiPemeriksaanPesertaController::class, 'index']);
    Route::get('/pemeriksaan/{pemeriksaanId}/kandidat/atlet', [ApiPemeriksaanPesertaController::class, 'availableAtlet']);
    Route::get('/pemeriksaan/{pemeriksaanId}/kandidat/pelatih', [ApiPemeriksaanPesertaController::class, 'availablePelatih']);
    Route::get('/pemeriksaan/{pemeriksaanId}/kandidat/tenaga-pendukung', [ApiPemeriksaanPesertaController::class, 'availableTenagaPendukung']);

    // Turnamen (Mobile)
    Route::get('/turnamen/mobile', [TurnamenController::class, 'index']);
    Route::get('/turnamen/{id}/mobile', [TurnamenController::class, 'show']);
    Route::get('/turnamen/cabor/list', [TurnamenController::class, 'getCaborList']);
    Route::get('/turnamen/{turnamenId}/peserta', [TurnamenController::class, 'peserta']);

    // Home (Mobile)
    Route::get('/home', [HomeController::class, 'index']);

    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/users-menu', [UsersMenuController::class, 'getMenus']);
    Route::get('/users', [UsersController::class, 'apiIndex']);

    // Master Parameter (Reference list for dropdown)
    Route::get('/mst-parameter', [MstParameterController::class, 'apiIndex']);
});
