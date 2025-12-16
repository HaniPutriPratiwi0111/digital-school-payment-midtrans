<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\API\AuthMobileController;
use App\Http\Controllers\API\SiswaMobileController;
use App\Http\Controllers\API\TagihanMobileController;

// Login Admin/Bendahara
Route::post('/mobile-admin/login', [AuthMobileController::class, 'loginAdmin']);

    // Middleware Sanctum untuk mobile-admin
    Route::middleware('auth:sanctum')->prefix('mobile-admin')->group(function () {
        Route::post('/logout', [AuthMobileController::class, 'logout']);

        // Siswa
        Route::get('/siswa', [SiswaMobileController::class, 'index']);
        Route::get('/siswa/{id}', [SiswaMobileController::class, 'show']);
        
        // 🌟 ROUTE BARU: Detail Tagihan per Siswa
        Route::get('/siswa/{id_siswa}/tagihan', [TagihanMobileController::class, 'getTagihansBySiswa']);


        // Tagihan
        Route::get('/tagihan', [TagihanMobileController::class, 'index']);
        Route::get('/tagihan/{id}', [TagihanMobileController::class, 'show']);
        Route::post('/tagihan', [TagihanMobileController::class, 'store']);            // tunggal
        Route::post('/tagihan/massal', [TagihanMobileController::class, 'storeMassal']); // massal
        Route::put('/tagihan/{id}', [TagihanMobileController::class, 'update']);
        Route::delete('/tagihan/{id}', [TagihanMobileController::class, 'destroy']);

        // Preview nominal
        Route::get('/tagihan/nominal/{id_siswa}/{id_jenis}/{id_tahun}', [TagihanMobileController::class, 'getNominal']); // tunggal
        Route::get('/tagihan/nominal-massal/{id_kelas}/{id_jenis}/{id_tahun}', [TagihanMobileController::class, 'getNominalMassal']); // massal

        // Tambahkan route ini untuk data master dropdown
        Route::get('/master-data', [TagihanMobileController::class, 'getMasterData']);
    });

// Midtrans
Route::post('/midtrans/notification', [MidtransController::class, 'handleMidtransNotification'])
    ->name('api.midtrans.notification');

// Info user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});