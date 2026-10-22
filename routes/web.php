<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Rute beranda diarahkan ke dashboard jika sudah login, atau ke halaman login jika belum
Route::get('/', function () {
    if (auth()->check()) {
        /** @var \App\Models\User $user */
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
});

Route::middleware(['auth', 'peran:dokter'])->group(function () {
    Route::get('/dokter/dashboard', [DashboardController::class, 'dokter'])->name('dokter.dashboard');
});

Route::middleware(['auth', 'peran:pasien'])->group(function () {
    Route::get('/pasien/dashboard', [DashboardController::class, 'pasien'])->name('pasien.dashboard');
});
