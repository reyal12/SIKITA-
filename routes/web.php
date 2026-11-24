<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorPatientController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientProfileController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/dokter/login', [LoginController::class, 'showDoctorLoginForm'])->name('login.dokter');
    Route::post('/dokter/login', [LoginController::class, 'loginDoctor'])->name('login.dokter.store');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/dokter/daftar', [RegisterController::class, 'showDoctorRegistrationForm'])
        ->name('register.dokter');
    Route::post('/dokter/daftar', [RegisterController::class, 'registerDoctor'])
        ->name('register.dokter.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

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

Route::middleware(['auth', 'peran:dokter'])->group(function () {
    Route::get('/dokter/profil', [DoctorProfileController::class, 'edit'])->name('dokter.profil');
    Route::post('/dokter/profil', [DoctorProfileController::class, 'update'])->name('dokter.profil.update');
    Route::get('/dokter/pasien', [DoctorPatientController::class, 'index'])->name('dokter.pasien.index');
    Route::get('/dokter/pasien/{patient}/riwayat', [DoctorPatientController::class, 'history'])
        ->name('dokter.pasien.history');
    Route::get('/dokter/janji-temu/{appointment}/pemeriksaan', [DoctorPatientController::class, 'examination'])
        ->name('dokter.pemeriksaan.create');
    Route::post('/dokter/janji-temu/{appointment}/pemeriksaan', [DoctorPatientController::class, 'storeExamination'])
        ->name('dokter.pemeriksaan.store');
    Route::get('/dokter/jadwal-praktik', [DoctorScheduleController::class, 'index'])
        ->name('dokter.jadwal-praktik.index');
    Route::post('/dokter/jadwal-praktik', [DoctorScheduleController::class, 'store'])
        ->name('dokter.jadwal-praktik.store');
    Route::patch('/dokter/jadwal-praktik/{schedule}', [DoctorScheduleController::class, 'update'])
        ->name('dokter.jadwal-praktik.update');
    Route::delete('/dokter/jadwal-praktik/{schedule}', [DoctorScheduleController::class, 'destroy'])
        ->name('dokter.jadwal-praktik.destroy');
});

Route::middleware(['auth', 'peran:dokter,admin'])->group(function () {
    Route::get('/obat', [MedicineController::class, 'index'])->name('obat.index');
    Route::post('/obat', [MedicineController::class, 'store'])->name('obat.store');
    Route::patch('/obat/{medicine}', [MedicineController::class, 'update'])->name('obat.update');
    Route::delete('/obat/{medicine}', [MedicineController::class, 'destroy'])->name('obat.destroy');
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
