<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\PaymentController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rute beranda diarahkan ke dashboard jika sudah login, atau ke halaman login jika belum
Route::get('/', function () {
    if (auth()->check()) {
        /** @var User $user */
        $user = auth()->user();

        return redirect()->route($user->dashboardRoute());
    }

    return redirect()->route('login');
});

// Rute khusus tamu (guest) - Pengguna yang sudah login akan otomatis dialihkan ke dashboard sesuai perannya
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Rute logout (hanya dapat diakses melalui metode POST dengan proteksi autentikasi dan CSRF)
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Rute dashboard masing-masing peran yang dilindungi oleh middleware auth dan peran
Route::middleware(['auth', 'peran:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    Route::post('/admin/pembayaran', [PaymentController::class, 'store'])->name('admin.pembayaran.store');
    Route::patch('/admin/pembayaran/{payment}', [PaymentController::class, 'updateStatus'])
        ->name('admin.pembayaran.status');
});

Route::middleware(['auth', 'peran:dokter,admin'])->group(function () {
    Route::get('/dokter/dashboard', [DashboardController::class, 'dokter'])->name('dokter.dashboard');
    Route::post('/dokter/janji-temu/{appointment}/selesai', [AppointmentController::class, 'complete'])
        ->name('dokter.janji-temu.complete');
});

Route::middleware(['auth', 'peran:pasien'])->group(function () {
    Route::get('/pasien/dashboard', [DashboardController::class, 'pasien'])->name('pasien.dashboard');
    Route::get('/pasien/janji-temu', [DashboardController::class, 'janjiTemu'])
        ->name('pasien.janji-temu');
    Route::get('/pasien/riwayat-medis', [DashboardController::class, 'riwayatMedis'])
        ->name('pasien.riwayat-medis');
    Route::get('/pasien/pembayaran', [DashboardController::class, 'pembayaran'])
        ->name('pasien.pembayaran');
    Route::get('/pasien/profil', [DashboardController::class, 'profil'])
        ->name('pasien.profil');
    Route::post('/pasien/profil', [PatientProfileController::class, 'update'])->name('pasien.profil.update');
    Route::get('/pasien/jadwal-tersedia', [AppointmentController::class, 'availableSlots'])
        ->name('pasien.jadwal-tersedia');
    Route::post('/pasien/janji-temu', [AppointmentController::class, 'store'])
        ->name('pasien.janji-temu.store');
    Route::post('/pasien/janji-temu/{appointment}/batal', [AppointmentController::class, 'cancel'])
        ->name('pasien.janji-temu.cancel');
});
