<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClinicManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_admin_can_create_doctor_account_and_manage_fixed_weekly_schedule_with_quota(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = $this->createSpecialty();
        $doctorData = [
            'name' => 'Dokter Akun',
            'email' => 'dokter.akun@example.test',
            'doctor_number' => 'DR-AKUN-001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'specialty_id' => $specialtyId,
            'license_number' => 'SIP-AKUN-001',
            'phone' => '08123456789',
        ];

        $this->actingAs($admin)
            ->get(route('admin.dokter.create'))
            ->assertOk()
            ->assertSee('Tambah Dokter');

        $response = $this->actingAs($admin)
            ->post(route('admin.dokter.store'), $doctorData);
        $response->assertRedirect(route('admin.dokter.index'));
        $account = User::where('email', $doctorData['email'])->firstOrFail();
        $this->assertTrue(Hash::check($doctorData['password'], $account->password));
        $this->assertTrue($account->punyaPeran('dokter'));

        $doctorId = (int) DB::table('dokter')->where('pengguna_id', $account->id)->value('id');
        $this->assertDatabaseHas('dokter', [
            'id' => $doctorId,
            'spesialis_id' => $specialtyId,
            'nama_lengkap' => $doctorData['name'],
            'aktif' => true,
        ]);

        $weekday = now()->addWeek()->dayOfWeek;
        $this->actingAs($admin)
            ->post(route('admin.jadwal-dokter.store'), [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'appointment_duration' => 30,
                'max_patients' => 12,
            ])
            ->assertRedirect(route('admin.jadwal-dokter.index'));

        $scheduleId = (int) DB::table('jadwal_dokter')->where('dokter_id', $doctorId)->value('id');
        $this->assertDatabaseHas('jadwal_dokter', [
            'id' => $scheduleId,
            'hari_dalam_minggu' => $weekday,
            'maksimal_pasien' => 12,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.jadwal-dokter.update', $scheduleId), [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'appointment_duration' => 30,
                'max_patients' => 15,
            ])
            ->assertRedirect(route('admin.jadwal-dokter.index'));
        $this->assertDatabaseHas('jadwal_dokter', [
            'id' => $scheduleId,
            'maksimal_pasien' => 15,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dokter.edit', $doctorId))
            ->assertOk()
            ->assertSee('Nomor dokter')
            ->assertSee('Kelola jadwal dokter');

        $this->actingAs($admin)
            ->get(route('admin.jadwal-dokter.index'))
            ->assertOk()
            ->assertSee('Jadwal praktik mingguan')
            ->assertSee('Dokter Akun')
            ->assertSee('Kuota pasien');

        $this->actingAs($admin)
            ->get(route('admin.jadwal-dokter.index', [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
            ]))
            ->assertOk()
            ->assertSee('Dokter Akun');

        $this->actingAs($admin)
            ->delete(route('admin.jadwal-dokter.destroy', $scheduleId))
            ->assertRedirect(route('admin.jadwal-dokter.index'));
        $this->assertDatabaseMissing('jadwal_dokter', ['id' => $scheduleId]);

        $this->actingAs($admin)
            ->get(route('admin.akun.index'))
            ->assertOk()
            ->assertSee($doctorData['email'])
            ->assertSee('Dokter');
    }

    public function test_doctor_schedule_conflicts_and_changes_that_invalidate_bookings_are_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = $this->createSpecialty();
        $doctorId = $this->createDoctor($specialtyId);
        $appointmentDate = now()->addWeek()->toDateString();
        $weekday = Carbon::parse($appointmentDate)->dayOfWeek;
        $this->createSchedule($doctorId, $weekday, '09:00', '11:00', 5);

        $this->actingAs($admin)
            ->post(route('admin.jadwal-dokter.store'), [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
                'start_time' => '10:00',
                'end_time' => '12:00',
                'appointment_duration' => 30,
                'max_patients' => 5,
            ])
            ->assertSessionHasErrors('start_time');

        $patientId = $this->createPatient('Pasien Jadwal');
        $this->createAppointment($patientId, $doctorId, $appointmentDate, '09:00', 1);
        $this->createAppointment($patientId, $doctorId, $appointmentDate, '09:30', 2);

        $scheduleId = (int) DB::table('jadwal_dokter')->where('dokter_id', $doctorId)->value('id');
        $this->actingAs($admin)
            ->put(route('admin.jadwal-dokter.update', $scheduleId), [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
                'start_time' => '09:30',
                'end_time' => '11:00',
                'appointment_duration' => 30,
                'max_patients' => 5,
            ])
            ->assertSessionHasErrors('start_time');

        $this->actingAs($admin)
            ->put(route('admin.jadwal-dokter.update', $scheduleId), [
                'doctor_id' => $doctorId,
                'weekday' => $weekday,
                'start_time' => '09:00',
                'end_time' => '11:00',
                'appointment_duration' => 30,
                'max_patients' => 1,
            ])
            ->assertSessionHasErrors('max_patients');

        $this->actingAs($admin)
            ->delete(route('admin.jadwal-dokter.destroy', $scheduleId))
            ->assertSessionHasErrors('schedule');
        $this->assertDatabaseHas('jadwal_dokter', ['id' => $scheduleId]);
    }

    public function test_specialty_crud_preserves_specialties_used_by_doctors(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->actingAs($admin)
            ->post(route('admin.dokter.spesialisasi.store'), [
                'kode' => 'SP-BUAT',
                'nama' => 'Spesialis Dibuat',
                'deskripsi' => 'Spesialis melalui halaman admin.',
            ])
            ->assertRedirect(route('admin.dokter.index'));
        $specialtyId = (int) DB::table('spesialis')
            ->where('kode', 'SP-BUAT')
            ->value('id');

        $this->actingAs($admin)
            ->get(route('admin.dokter.index'))
            ->assertOk()
            ->assertSee('Kelola spesialisasi')
            ->assertSee('Spesialis Dibuat');

        $this->actingAs($admin)
            ->put(route('admin.dokter.spesialisasi.update', $specialtyId), [
                'kode' => 'SP-UPDATE',
                'nama' => 'Spesialis Diperbarui',
                'deskripsi' => 'Deskripsi baru.',
            ])
            ->assertRedirect(route('admin.dokter.index'));

        $this->assertDatabaseHas('spesialis', [
            'id' => $specialtyId,
            'nama' => 'Spesialis Diperbarui',
        ]);

        $this->createDoctor($specialtyId);

        $this->actingAs($admin)
            ->delete(route('admin.dokter.spesialisasi.destroy', $specialtyId))
            ->assertSessionHasErrors('specialty');
        $this->assertDatabaseHas('spesialis', ['id' => $specialtyId]);

        $newSpecialtyId = $this->createSpecialty('SP-BARU', 'Spesialis Baru');
        $this->actingAs($admin)
            ->delete(route('admin.dokter.spesialisasi.destroy', $newSpecialtyId))
            ->assertRedirect(route('admin.dokter.index'));
        $this->assertDatabaseMissing('spesialis', ['id' => $newSpecialtyId]);
    }

    public function test_doctor_cannot_be_deactivated_while_future_appointments_are_scheduled(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = $this->createSpecialty();
        $doctorId = $this->createDoctor($specialtyId);
        $appointmentDate = now()->addWeek()->toDateString();
        $this->createAppointment($this->createPatient('Pasien Dokter'), $doctorId, $appointmentDate, '09:00');

        $this->actingAs($admin)
            ->delete(route('admin.dokter.destroy', $doctorId))
            ->assertSessionHasErrors('doctor');

        $this->actingAs($admin)
            ->patch(route('admin.dokter.deactivate', $doctorId))
            ->assertSessionHasErrors('doctor');

        $this->assertDatabaseHas('dokter', ['id' => $doctorId, 'aktif' => true]);
    }

    public function test_admin_can_update_doctor_profile_and_deactivate_doctor_without_upcoming_appointments(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = $this->createSpecialty();
        $doctorId = $this->createDoctor($specialtyId);
        $doctor = DB::table('dokter')->where('id', $doctorId)->first();
        $newSpecialtyId = $this->createSpecialty('SP-BARU', 'Spesialis Baru');

        $this->actingAs($admin)
            ->put(route('admin.dokter.update', $doctorId), [
                'doctor_number' => 'DR-DIPERBARUI',
                'name' => 'Dokter Diperbarui',
                'email' => 'dokter.diperbarui@example.test',
                'specialty_id' => $newSpecialtyId,
                'license_number' => 'SIP-DIPERBARUI',
                'phone' => '08111112222',
                'aktif' => true,
            ])
            ->assertRedirect(route('admin.dokter.edit', $doctorId));

        $this->assertDatabaseHas('users', [
            'id' => $doctor->pengguna_id,
            'name' => 'Dokter Diperbarui',
            'email' => 'dokter.diperbarui@example.test',
        ]);
        $this->assertDatabaseHas('dokter', [
            'id' => $doctorId,
            'nomor_dokter' => 'DR-DIPERBARUI',
            'spesialis_id' => $newSpecialtyId,
            'nomor_izin_praktik' => 'SIP-DIPERBARUI',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.dokter.deactivate', $doctorId))
            ->assertRedirect(route('admin.dokter.index'));

        $this->assertDatabaseHas('dokter', ['id' => $doctorId, 'aktif' => false]);
        $this->actingAs(User::findOrFail($doctor->pengguna_id))
            ->get(route('dokter.dashboard'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.dokter.destroy', $doctorId))
            ->assertRedirect(route('admin.dokter.index'));
        $this->assertDatabaseMissing('dokter', ['id' => $doctorId]);
        $this->assertDatabaseMissing('users', ['id' => $doctor->pengguna_id]);
    }

    public function test_admin_can_manage_legacy_doctor_without_account_and_open_schedule_menu(): void
    {
        $admin = $this->createUserWithRole('admin');
        $specialtyId = $this->createSpecialty();
        $doctorId = DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-TANPA-AKUN',
            'nama_lengkap' => 'Dokter Lama',
            'nomor_izin_praktik' => 'SIP-TANPA-AKUN',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $weekday = now()->addWeek()->dayOfWeek;
        $this->createSchedule($doctorId, $weekday, '08:00', '11:00', 10);

        $this->actingAs($admin)
            ->get(route('admin.dokter.index'))
            ->assertOk()
            ->assertSee('Dokter Lama')
            ->assertSee('Belum ada akun tertaut');

        $this->actingAs($admin)
            ->get(route('admin.dokter.edit', $doctorId))
            ->assertOk()
            ->assertSee('Dokter ini belum terhubung ke akun pengguna.')
            ->assertSee('Kelola jadwal dokter')
            ->assertSee('Dokter Lama');

        $this->actingAs($admin)
            ->put(route('admin.dokter.update', $doctorId), [
                'doctor_number' => 'DR-LAMA-UPDATED',
                'name' => 'Dokter Lama Diperbarui',
                'specialty_id' => $specialtyId,
                'license_number' => 'SIP-TANPA-AKUN',
                'phone' => '08123456789',
                'aktif' => true,
            ])
            ->assertRedirect(route('admin.dokter.edit', $doctorId));
        $this->assertDatabaseHas('dokter', [
            'id' => $doctorId,
            'nama_lengkap' => 'Dokter Lama Diperbarui',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.jadwal-dokter.index'))
            ->assertOk()
            ->assertSee('Dokter Lama Diperbarui')
            ->assertSee('08:00');
    }

    public function test_only_admin_can_manage_doctors_specialties_and_accounts(): void
    {
        $patient = $this->createUserWithRole('pasien');

        $this->actingAs($patient)
            ->get(route('admin.dokter.index'))
            ->assertForbidden();
        $this->actingAs($patient)
            ->post(route('admin.dokter.spesialisasi.store'), [
                'kode' => 'SP-TOLAK',
                'nama' => 'Spesialis Ditolak',
            ])
            ->assertForbidden();
        $this->actingAs($patient)
            ->get(route('admin.akun.index'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::where('kode', $role)->value('id'));

        return $user;
    }

    private function createSpecialty(string $code = 'SP-UJI', string $name = 'Spesialis Uji'): int
    {
        return DB::table('spesialis')->insertGetId([
            'kode' => $code,
            'nama' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDoctor(int $specialtyId): int
    {
        $account = $this->createUserWithRole('dokter');

        return DB::table('dokter')->insertGetId([
            'pengguna_id' => $account->id,
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-'.strtoupper(fake()->unique()->bothify('########')),
            'nama_lengkap' => $account->name,
            'nomor_izin_praktik' => 'SIP-'.strtoupper(fake()->unique()->bothify('########')),
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchedule(
        int $doctorId,
        int $weekday,
        string $startTime,
        string $endTime,
        int $maxPatients,
    ): int {
        return DB::table('jadwal_dokter')->insertGetId([
            'dokter_id' => $doctorId,
            'hari_dalam_minggu' => $weekday,
            'waktu_mulai' => $startTime,
            'waktu_selesai' => $endTime,
            'durasi_janji_menit' => 30,
            'maksimal_pasien' => $maxPatients,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPatient(string $name): int
    {
        return DB::table('pasien')->insertGetId([
            'nomor_pasien' => 'PSN-'.strtoupper(fake()->unique()->bothify('########')),
            'nama_lengkap' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(
        int $patientId,
        int $doctorId,
        string $date,
        string $startTime,
        int $queueNumber = 1,
    ): int {
        return DB::table('janji_temu')->insertGetId([
            'nomor_janji' => 'JAN-'.strtoupper(fake()->unique()->bothify('########')),
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $date,
            'nomor_antrean' => $queueNumber,
            'waktu_mulai' => $startTime,
            'status' => 'terjadwal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
