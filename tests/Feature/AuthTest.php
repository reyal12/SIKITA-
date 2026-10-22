<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_halaman_login_dan_registrasi_dapat_dibuka_oleh_tamu(): void
    {
        $responseLogin = $this->get('/login');
        $responseLogin->assertStatus(200);
        $responseLogin->assertSee('Masuk ke Akun');

        $responseRegister = $this->get('/register');
        $responseRegister->assertStatus(200);
        $responseRegister->assertSee('Daftar Akun Pasien');
    }

    public function test_registrasi_pasien_baru_berhasil_langsung_login_dan_masuk_ke_dashboard_pasien(): void
    {
        $payload = [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $payload);

        // Setelah daftar, langsung diarahkan ke dashboard pasien
        $response->assertRedirect(route('pasien.dashboard'));

        // Akun terdaftar di tabel users
        $this->assertDatabaseHas('users', [
            'email' => 'budi@example.com',
            'name' => 'Budi Santoso',
        ]);

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);

        // Pengguna langsung terautentikasi
        $this->assertAuthenticatedAs($user);

        // Peran pasien otomatis ditugaskan
        $this->assertTrue($user->punyaPeran('pasien'));

        // Tidak boleh membuat baris di tabel pasien (sesuai instruksi)
        $this->assertEquals(0, DB::table('pasien')->count());
    }

    public function test_login_admin_dari_seeder_berhasil_dan_masuk_ke_dashboard_admin(): void
    {
        $this->seed(AdminSeeder::class);

        $adminEmail = config('sikita.admin.email', 'admin@sikita.test');
        $adminPassword = config('sikita.admin.password', 'admin12345');

        $response = $this->post('/login', [
            'email' => $adminEmail,
            'password' => $adminPassword,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        // Admin dapat membuka dashboard admin
        $dashboardResponse = $this->get('/admin/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Panel Administrator Klinik');
        $dashboardResponse->assertSee($adminEmail);
    }

    public function test_pasien_membuka_dashboard_admin_menghasilkan_error_403_berbahasa_indonesia(): void
    {
        $pasien = User::create([
            'name' => 'Pasien Uji',
            'email' => 'pasien@example.com',
            'password' => Hash::make('password123'),
        ]);
        $peranPasien = Peran::where('kode', 'pasien')->first();
        $pasien->peran()->attach($peranPasien->id);

        $response = $this->actingAs($pasien)->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');
        $response->assertSee('Anda tidak memiliki hak akses untuk membuka halaman ini.');
    }

    public function test_pengguna_yang_sudah_login_diarahkan_ke_dashboard_saat_membuka_halaman_login_atau_register(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@example.com',
            'password' => Hash::make('password123'),
        ]);
        $peranAdmin = Peran::where('kode', 'admin')->first();
        $admin->peran()->attach($peranAdmin->id);

        $responseLogin = $this->actingAs($admin)->get('/login');
        $responseLogin->assertRedirect(route('admin.dashboard'));

        $responseRegister = $this->actingAs($admin)->get('/register');
        $responseRegister->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_hanya_bisa_lewat_post_dan_menghapus_sesi(): void
    {
        $user = User::create([
            'name' => 'User Keluar',
            'email' => 'keluar@example.com',
            'password' => Hash::make('password123'),
        ]);
        $peranPasien = Peran::where('kode', 'pasien')->first();
        $user->peran()->attach($peranPasien->id);

        // GET /logout tidak diizinkan
        $this->actingAs($user)->get('/logout')->assertStatus(405);

        // POST /logout berhasil
        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // Setelah logout, tidak dapat membuka dashboard pasien lagi
        $this->get('/pasien/dashboard')->assertRedirect(route('login'));
    }

    public function test_prioritas_redirect_dashboard_admin_lebih_tinggi_dari_dokter_dan_pasien(): void
    {
        $multiUser = User::create([
            'name' => 'Dokter dan Admin',
            'email' => 'multirole@example.com',
            'password' => Hash::make('password123'),
        ]);
        $peranAdmin = Peran::where('kode', 'admin')->first();
        $peranDokter = Peran::where('kode', 'dokter')->first();

        // Pengguna memiliki peran admin dan dokter
        $multiUser->peran()->attach([$peranAdmin->id, $peranDokter->id]);

        $this->assertEquals('admin.dashboard', $multiUser->dashboardRoute());

        $response = $this->post('/login', [
            'email' => 'multirole@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_rate_limiting_login_maksimal_5_kali_percobaan(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'salah@example.com',
                'password' => 'passwordsalah',
            ]);
        }

        // Percobaan ke-6 harus ditolak oleh rate limiter (throttling)
        $responseKeenam = $this->post('/login', [
            'email' => 'salah@example.com',
            'password' => 'passwordsalah',
        ]);

        $responseKeenam->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan login', session('errors')->first('email'));
    }
}
