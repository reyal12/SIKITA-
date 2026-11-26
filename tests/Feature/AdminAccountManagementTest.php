<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_admin_can_create_patient_account_with_linked_patient_profile(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.akun.create'))
            ->assertOk()
            ->assertSee('Buat Akun Pengguna')
            ->assertSee('name="role"', false);

        $response = $this->actingAs($admin)->post(route('admin.akun.store'), [
            'name' => 'Pasien Akun',
            'email' => 'pasien.akun@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pasien',
        ]);

        $response->assertRedirect(route('admin.akun.index'));

        $account = User::query()->where('email', 'pasien.akun@example.test')->firstOrFail();
        $this->assertTrue($account->punyaPeran('pasien'));
        $this->assertTrue(Hash::check('password123', $account->password));
        $this->assertDatabaseHas('pasien', [
            'pengguna_id' => $account->id,
            'nama_lengkap' => 'Pasien Akun',
            'email' => 'pasien.akun@example.test',
        ]);
        $this->assertDatabaseHas('peran_pengguna', [
            'pengguna_id' => $account->id,
            'peran_id' => Peran::query()->where('kode', 'pasien')->value('id'),
            'ditetapkan_oleh' => $admin->id,
        ]);
    }

    public function test_admin_can_create_doctor_account_with_doctor_profile(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-AKUN',
            'nama' => 'Spesialis Akun',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.akun.store'), [
            'name' => 'Dokter Akun',
            'email' => 'dokter.akun@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dokter',
            'specialty_id' => $specialtyId,
            'license_number' => 'SIP-AKUN-001',
            'phone' => '08123456789',
        ]);

        $response->assertRedirect(route('admin.akun.index'));
        $account = User::query()->where('email', 'dokter.akun@example.test')->firstOrFail();

        $this->assertTrue($account->punyaPeran('dokter'));
        $this->assertDatabaseHas('dokter', [
            'pengguna_id' => $account->id,
            'spesialis_id' => $specialtyId,
            'nama_lengkap' => 'Dokter Akun',
            'nomor_izin_praktik' => 'SIP-AKUN-001',
            'aktif' => true,
        ]);
    }

    public function test_admin_can_create_admin_account_and_reset_an_account_password(): void
    {
        $admin = $this->createUserWithRole('admin');
        $response = $this->actingAs($admin)->post(route('admin.akun.store'), [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.akun.index'));
        $account = User::query()->where('email', 'admin.baru@example.test')->firstOrFail();
        $this->assertTrue($account->punyaPeran('admin'));

        $this->actingAs($admin)
            ->put(route('admin.akun.password.update', $account->id), [
                'password' => 'password456',
                'password_confirmation' => 'password456',
            ])
            ->assertRedirect(route('admin.akun.index'));

        $this->assertTrue(Hash::check('password456', $account->fresh()->password));
    }

    public function test_only_admin_can_manage_accounts_and_role_is_limited_to_three_actors(): void
    {
        $patient = $this->createUserWithRole('pasien');

        $this->actingAs($patient)
            ->get(route('admin.akun.create'))
            ->assertForbidden();

        $this->actingAs($patient)
            ->put(route('admin.akun.password.update', $patient->id), [
                'password' => 'password456',
                'password_confirmation' => 'password456',
            ])
            ->assertForbidden();

        $admin = $this->createUserWithRole('admin');
        $this->actingAs($admin)
            ->from(route('admin.akun.create'))
            ->post(route('admin.akun.store'), [
                'name' => 'Akun Kasir',
                'email' => 'kasir@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'kasir',
            ])
            ->assertRedirect(route('admin.akun.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'kasir@example.test']);
    }

    public function test_account_password_reset_requires_matching_confirmation(): void
    {
        $admin = $this->createUserWithRole('admin');
        $account = $this->createUserWithRole('pasien');

        $this->actingAs($admin)
            ->put(route('admin.akun.password.update', $account->id), [
                'password' => 'password456',
                'password_confirmation' => 'password789',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $account->fresh()->password));
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::query()->where('kode', $role)->value('id'));

        return $user;
    }
}
