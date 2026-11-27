<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminDoctorController;
use App\Http\Controllers\AdminMedicineController;
use App\Http\Controllers\AdminPatientManagementController;
use App\Http\Controllers\AdminRegistrationMonitoringController;
use App\Http\Controllers\AdminServiceMonitoringController;
use App\Http\Controllers\AdminSpecialtyController;
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

// Semua halaman dan aksi administrasi wajib login dan memiliki peran admin.
Route::middleware(['auth', 'peran:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::post('pembayaran', [PaymentController::class, 'store'])->name('pembayaran.store');
        Route::patch('pembayaran/{payment}', [PaymentController::class, 'updateStatus'])
            ->name('pembayaran.status');

        Route::resource('pasien', AdminPatientManagementController::class)
            ->parameters(['pasien' => 'patient'])
            ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

        Route::resource('dokter', AdminDoctorController::class)
            ->parameters(['dokter' => 'doctor'])
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::patch('dokter/{doctor}/nonaktifkan', [AdminDoctorController::class, 'deactivate'])
            ->whereNumber('doctor')
            ->name('dokter.deactivate');
        Route::post('dokter/spesialisasi', [AdminSpecialtyController::class, 'store'])
            ->name('dokter.spesialisasi.store');
        Route::put('dokter/spesialisasi/{specialty}', [AdminSpecialtyController::class, 'update'])
            ->whereNumber('specialty')
            ->name('dokter.spesialisasi.update');
        Route::delete('dokter/spesialisasi/{specialty}', [AdminSpecialtyController::class, 'destroy'])
            ->whereNumber('specialty')
            ->name('dokter.spesialisasi.destroy');

        Route::get('jadwal-dokter', [AdminDoctorController::class, 'schedules'])
            ->name('jadwal-dokter.index');
        Route::post('jadwal-dokter', [AdminDoctorController::class, 'storeSchedule'])
            ->name('jadwal-dokter.store');
        Route::put('jadwal-dokter/{schedule}', [AdminDoctorController::class, 'updateSchedule'])
            ->whereNumber('schedule')
            ->name('jadwal-dokter.update');
        Route::delete('jadwal-dokter/{schedule}', [AdminDoctorController::class, 'destroySchedule'])
            ->whereNumber('schedule')
            ->name('jadwal-dokter.destroy');

        Route::resource('obat', AdminMedicineController::class)->only(['index']);

        Route::resource('akun', AdminAccountController::class)->only(['index', 'create', 'store']);
        Route::put('akun/{account}/password', [AdminAccountController::class, 'resetPassword'])
            ->whereNumber('account')
            ->name('akun.password.update');

        Route::resource('pemantauan/pendaftaran', AdminRegistrationMonitoringController::class)
            ->parameters(['pendaftaran' => 'appointment'])
            ->only(['index', 'show'])
            ->names([
                'index' => 'pemantauan.pendaftaran.index',
                'show' => 'pemantauan.pendaftaran.show',
            ]);
        Route::resource('pemantauan/pelayanan', AdminServiceMonitoringController::class)
            ->parameters(['pelayanan' => 'record'])
            ->only(['index', 'show'])
            ->names([
                'index' => 'pemantauan.pelayanan.index',
                'show' => 'pemantauan.pelayanan.show',
            ]);
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
