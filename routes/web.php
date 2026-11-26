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
use App\Http\Controllers\PatientProfileController;
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

// Semua halaman dan aksi administrasi wajib login dan memiliki peran admin.
Route::middleware(['auth', 'peran:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');

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
