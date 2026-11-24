<?php

use App\Http\Controllers\AdminClinicInformationController;
use App\Http\Controllers\AdminDoctorController;
use App\Http\Controllers\AdminMedicineController;
use App\Http\Controllers\AdminPatientController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminServiceMonitoringController;
use App\Http\Controllers\AdminSpecialtyController;
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
    Route::get('/admin/pendaftaran', [AdminPatientController::class, 'index'])
        ->name('admin.pendaftaran.index');
    Route::get('/admin/pendaftaran/create', [AdminPatientController::class, 'create'])
        ->name('admin.pendaftaran.create');
    Route::post('/admin/pendaftaran', [AdminPatientController::class, 'store'])
        ->name('admin.pendaftaran.store');
    Route::get('/admin/pendaftaran/{patient}/edit', [AdminPatientController::class, 'edit'])
        ->whereNumber('patient')
        ->name('admin.pendaftaran.edit');
    Route::get('/admin/pendaftaran/janji-temu/{appointment}/edit', [AdminPatientController::class, 'editAppointment'])
        ->whereNumber('appointment')
        ->name('admin.pendaftaran.janji-temu.edit');
    Route::put('/admin/pendaftaran/janji-temu/{appointment}', [AdminPatientController::class, 'reschedule'])
        ->whereNumber('appointment')
        ->name('admin.pendaftaran.janji-temu.update');
    Route::patch('/admin/pendaftaran/janji-temu/{appointment}/batal', [AdminPatientController::class, 'cancelAppointment'])
        ->whereNumber('appointment')
        ->name('admin.pendaftaran.janji-temu.cancel');
    Route::put('/admin/pendaftaran/{patient}', [AdminPatientController::class, 'update'])
        ->whereNumber('patient')
        ->name('admin.pendaftaran.update');
    Route::delete('/admin/pendaftaran/{patient}', [AdminPatientController::class, 'destroy'])
        ->whereNumber('patient')
        ->name('admin.pendaftaran.destroy');
    Route::get('/admin/jadwal-tersedia', [AdminPatientController::class, 'availableSlots'])
        ->name('admin.jadwal-tersedia');
    Route::get('/admin/dokter', [AdminDoctorController::class, 'index'])->name('admin.dokter.index');
    Route::get('/admin/jadwal-dokter', [AdminDoctorController::class, 'schedules'])->name('admin.jadwal-dokter.index');
    Route::get('/admin/dokter/create', [AdminDoctorController::class, 'create'])->name('admin.dokter.create');
    Route::post('/admin/dokter', [AdminDoctorController::class, 'store'])->name('admin.dokter.store');
    Route::get('/admin/dokter/{doctor}/edit', [AdminDoctorController::class, 'edit'])
        ->whereNumber('doctor')
        ->name('admin.dokter.edit');
    Route::put('/admin/dokter/{doctor}', [AdminDoctorController::class, 'update'])
        ->whereNumber('doctor')
        ->name('admin.dokter.update');
    Route::delete('/admin/dokter/{doctor}', [AdminDoctorController::class, 'destroy'])
        ->whereNumber('doctor')
        ->name('admin.dokter.destroy');
    Route::post('/admin/dokter/{doctor}/jadwal', [AdminDoctorController::class, 'storeSchedule'])
        ->whereNumber('doctor')
        ->name('admin.dokter.jadwal.store');
    Route::put('/admin/jadwal-dokter/{schedule}', [AdminDoctorController::class, 'updateSchedule'])
        ->whereNumber('schedule')
        ->name('admin.dokter.jadwal.update');
    Route::delete('/admin/jadwal-dokter/{schedule}', [AdminDoctorController::class, 'destroySchedule'])
        ->whereNumber('schedule')
        ->name('admin.dokter.jadwal.destroy');
    Route::get('/admin/spesialis', [AdminSpecialtyController::class, 'index'])->name('admin.spesialis.index');
    Route::get('/admin/spesialis/create', [AdminSpecialtyController::class, 'create'])
        ->name('admin.spesialis.create');
    Route::post('/admin/spesialis', [AdminSpecialtyController::class, 'store'])
        ->name('admin.spesialis.store');
    Route::get('/admin/spesialis/{specialty}/edit', [AdminSpecialtyController::class, 'edit'])
        ->whereNumber('specialty')
        ->name('admin.spesialis.edit');
    Route::put('/admin/spesialis/{specialty}', [AdminSpecialtyController::class, 'update'])
        ->whereNumber('specialty')
        ->name('admin.spesialis.update');
    Route::delete('/admin/spesialis/{specialty}', [AdminSpecialtyController::class, 'destroy'])
        ->whereNumber('specialty')
        ->name('admin.spesialis.destroy');
    Route::get('/admin/akun', [AdminDoctorController::class, 'accounts'])->name('admin.akun.index');
    Route::get('/admin/akun/dokter/create', [AdminDoctorController::class, 'create'])
        ->name('admin.akun.dokter.create');
    Route::post('/admin/akun/dokter', [AdminDoctorController::class, 'store'])
        ->name('admin.akun.dokter.store');
    Route::get('/admin/obat', [AdminMedicineController::class, 'index'])->name('admin.obat.index');
    Route::get('/admin/obat/create', [AdminMedicineController::class, 'create'])->name('admin.obat.create');
    Route::post('/admin/obat', [AdminMedicineController::class, 'store'])->name('admin.obat.store');
    Route::get('/admin/obat/{medicine}/edit', [AdminMedicineController::class, 'edit'])
        ->whereNumber('medicine')
        ->name('admin.obat.edit');
    Route::put('/admin/obat/{medicine}', [AdminMedicineController::class, 'update'])
        ->whereNumber('medicine')
        ->name('admin.obat.update');
    Route::delete('/admin/obat/{medicine}', [AdminMedicineController::class, 'destroy'])
        ->whereNumber('medicine')
        ->name('admin.obat.destroy');
    Route::get('/admin/informasi-klinik', [AdminClinicInformationController::class, 'index'])
        ->name('admin.informasi-klinik.index');
    Route::get('/admin/informasi-klinik/create', [AdminClinicInformationController::class, 'create'])
        ->name('admin.informasi-klinik.create');
    Route::post('/admin/informasi-klinik', [AdminClinicInformationController::class, 'store'])
        ->name('admin.informasi-klinik.store');
    Route::get('/admin/informasi-klinik/{item}/edit', [AdminClinicInformationController::class, 'edit'])
        ->whereNumber('item')
        ->name('admin.informasi-klinik.edit');
    Route::put('/admin/informasi-klinik/{item}', [AdminClinicInformationController::class, 'update'])
        ->whereNumber('item')
        ->name('admin.informasi-klinik.update');
    Route::delete('/admin/informasi-klinik/{item}', [AdminClinicInformationController::class, 'destroy'])
        ->whereNumber('item')
        ->name('admin.informasi-klinik.destroy');
    Route::get('/admin/pelayanan', [AdminServiceMonitoringController::class, 'index'])
        ->name('admin.pelayanan.index');
    Route::get('/admin/laporan', [AdminReportController::class, 'index'])->name('admin.laporan.index');
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
