<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    MasterJenjangController,
    KelasController,
    SiswaController,
    TahunAjaranController,
    TagihanController,
    JenisPembayaranController,
    PembayaranController,
    NotifikasiLogController,
    AturNominalController,
    DetailTagihanController,
    UserController,
    GuruController,
    Auth\LoginController,
    RoleController,
    MidtransController,
    PendaftaranController,
    ChatbotController,
    LanjutkanPembayaranController,
};

/*
|--------------------------------------------------------------------------
| AUTENTIKASI LOGIN & LOGOUT
|--------------------------------------------------------------------------
*/

// === LOGIN ADMIN / BENDAHARA ===
Route::get('/login/admin', [LoginController::class, 'showAdminLogin'])->name('login.admin');
Route::post('/login/admin', [LoginController::class, 'loginAdmin'])->name('login.admin.post');

// === LOGIN WALI / ORANG TUA ===
Route::get('/login/wali', [LoginController::class, 'showWaliLogin'])->name('login.wali');
Route::post('/login/wali', [LoginController::class, 'loginWali'])->name('login.wali.post');

// === LOGOUT ===
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/pendaftaran', [PendaftaranController::class, 'showRegistrationForm'])->name('pendaftaran.form');
Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

Route::get('/pendaftaran/sukses/{order_id}', [PendaftaranController::class, 'success'])
    ->name('pendaftaran.sukses');

Route::get('/lanjutkan-pembayaran', [LanjutkanPembayaranController::class, 'index'])
    ->name('pembayaran.lanjut');

Route::post('/lanjutkan-pembayaran', [LanjutkanPembayaranController::class, 'check'])
    ->name('pembayaran.check');


Route::get('/pay/{tagihan}/midtrans', [MidtransController::class, 'payWithMidtrans'])
    ->name('midtrans.payWithMidtrans');

    // Redirect dari Midtrans (Finish, Unfinish, Error)
    Route::get('/finish', [MidtransController::class, 'finish'])->name('midtrans.finish');
    Route::get('/unfinish', [MidtransController::class, 'unfinish'])->name('midtrans.unfinish');
    Route::get('/error', [MidtransController::class, 'error'])->name('midtrans.error');


/*
|--------------------------------------------------------------------------
| ROUTE YANG MEMBUTUHKAN AUTENTIKASI
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // === DASHBOARD UTAMA ===
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->hasRole('Super Administrator')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('Bendahara')) {
            return redirect()->route('bendahara.dashboard');
        } elseif ($user->hasRole('Orang Tua')) {
            return redirect()->route('wali.dashboard');
        } else {
            abort(403);
        }
    })->name('dashboard');

    // ========================================
    // 1️⃣ ADMIN / SUPER ADMIN
    // ========================================
    Route::middleware(['role:Super Administrator'])->group(function () {
        Route::get('/admin/dashboard', fn() => view('dashboard.admin'))->name('admin.dashboard');

        // Master Data
        Route::resources([
            'master-jenjang' => MasterJenjangController::class,
            'users' => UserController::class,
            'kelas' => KelasController::class,
            'siswa' => SiswaController::class,
            'tahun-ajaran' => TahunAjaranController::class,
            'guru' => GuruController::class,
        ]);

        // Pendaftar
        Route::resource('pendaftar', PendaftaranController::class)->only(['index','show','destroy']);

        // Naik kelas
        Route::get('/siswa-manage/naik-kelas', [SiswaController::class, 'formNaikKelas'])->name('siswa.naikKelas.form');
        Route::post('/siswa-manage/naik-kelas', [SiswaController::class, 'prosesNaikKelas'])->name('siswa.naikKelas.proses');
        Route::post('/siswa-manage/{siswa}/naik-kelas', [SiswaController::class, 'naikKelas'])->name('siswa.naik-kelas');
        Route::get('/siswa/daftarSiswa/{id_kelas}', [SiswaController::class, 'daftarSiswa']);
        Route::get('/siswa/kelasDenganSiswa/{id_tahun}', [SiswaController::class, 'kelasDenganSiswa']);
        Route::get('/siswa/create/{calonId}', [SiswaController::class, 'createFromCalon'])->name('siswa.create-from-calon');
        Route::post('/siswa/store_fromcalon', [SiswaController::class, 'storeFromCalon'])->name('siswa.store_from_calon');

        // Pembayaran manual
        Route::get('/pembayaran/create', [PembayaranController::class, 'create'])->name('pembayaran.create');
        Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
    });

    // ========================================
    // 2️⃣ BENDAHARA
    // ========================================
    Route::middleware(['role:Bendahara'])->group(function () {
        Route::get('/bendahara/dashboard', fn() => view('dashboard.bendahara'))->name('bendahara.dashboard');

        // Jenis pembayaran & atur nominal
        Route::resource('jenis-pembayaran', JenisPembayaranController::class);
        Route::resource('atur-nominal', AturNominalController::class);
        Route::post('atur-nominal/duplicate', [AturNominalController::class, 'duplicateRules'])->name('atur-nominal.duplicate');

        // Tagihan
        Route::resource('tagihan', TagihanController::class)->where(['tagihan' => '[0-9]+']);
        Route::get('/tagihan/get-nominal/{id_siswa}/{id_jenis}/{id_tahun}', [TagihanController::class, 'getNominal']);
        Route::post('/tagihan/store-massal', [TagihanController::class, 'storeMassal'])->name('tagihan.storeMassal');
        Route::get('/tagihan/get-nominal-massal/{kelas}/{jenis}/{tahun}', [TagihanController::class, 'getNominalMassal']);

        // Detail tagihan
        Route::resource('detail-tagihan', DetailTagihanController::class)->except(['index','create']);

        // Pembayaran
        Route::resource('pembayaran', PembayaranController::class)->only(['index','show','create','store']);
        Route::get('/midtrans/payPage/{tagihan}', [MidtransController::class, 'payWithMidtrans'])->name('midtrans.payPage');
    });

    // ========================================
    // 3️⃣ ORANG TUA / SISWA
    // ========================================
    Route::middleware(['role:Orang Tua|Siswa'])->group(function () {
        Route::get('/wali/dashboard', fn() => view('dashboard.wali'))->name('wali.dashboard')->middleware('payment.check');
        Route::get('/tagihan/anak', [TagihanController::class, 'tagihanAnak'])->name('tagihan.anak');
        Route::get('/tagihan/show-wali/{tagihan}', [TagihanController::class, 'showWali'])->name('tagihan.showWali');
        Route::get('/midtrans/payPage/{tagihan}', [MidtransController::class, 'payWithMidtrans'])->name('midtrans.payPage');

        // Chatbot
        Route::get('/chatbot/dialog', [ChatbotController::class, 'showChatbotDialog'])->name('chatbot.dialog');
        Route::post('/chatbot-api', [ChatbotController::class, 'processMessage'])->name('chatbot.send');
    });

    // ========================================
    // 4️⃣ PROFILE & PENGATURAN UMUM
    // ========================================
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'editProfile'])->name('profile.edit');
        Route::post('/update', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::post('/update-password', [UserController::class, 'updatePassword'])->name('profile.updatePassword');
        Route::post('/header/update', [UserController::class, 'updateHeader'])->name('profile.header.update');
    });

    // Notifikasi log & role management (hanya Super Admin)
    Route::middleware(['role:Super Administrator'])->group(function () {
        Route::resource('notifikasi-log', NotifikasiLogController::class)->except(['create','store','edit','update']);
        Route::resource('roles', RoleController::class);
    });

});

