<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPatientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_admin_can_create_patient_and_appointment_with_a_selected_doctor(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();
        $appointmentDate = now()->addWeek()->toDateString();
        $this->createSchedule($doctorId, $appointmentDate);

        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.create'))
            ->assertOk()
            ->assertSee('Janji temu dokter (opsional)')
            ->assertSee(route('admin.pendaftaran.store'), false);

        $response = $this->actingAs($admin)->post(route('admin.pendaftaran.store'), [
            'nama_lengkap' => 'Nadia Pasien',
            'nomor_identitas' => 'ID-10001',
            'email' => 'nadia@example.test',
            'telepon' => '08123456789',
            'tanggal_lahir' => '1990-04-15',
            'jenis_kelamin' => 'perempuan',
            'golongan_darah' => 'O+',
            'alamat' => 'Jalan Sehat No. 10',
            'doctor_id' => $doctorId,
            'appointment_date' => $appointmentDate,
            'start_time' => '09:00',
            'chief_complaint' => 'Pemeriksaan rutin',
        ]);

        $response->assertRedirect(route('admin.pendaftaran.index'));
        $this->assertDatabaseHas('pasien', [
            'nomor_identitas' => 'ID-10001',
            'nama_lengkap' => 'Nadia Pasien',
            'email' => 'nadia@example.test',
        ]);

        $patientId = DB::table('pasien')->where('nomor_identitas', 'ID-10001')->value('id');
        $this->assertDatabaseHas('janji_temu', [
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $appointmentDate,
            'keluhan_utama' => 'Pemeriksaan rutin',
            'dibuat_oleh' => $admin->id,
        ]);
        $appointment = DB::table('janji_temu')
            ->where('pasien_id', $patientId)
            ->first();
        $this->assertSame('09:00', substr($appointment->waktu_mulai, 0, 5));

        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.index'))
            ->assertOk()
            ->assertSee('Nadia Pasien')
            ->assertSee('Dokter Tes')
            ->assertSee('Daftarkan pasien');

        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.index', [
                'search' => 'Nadia',
                'tanggal_janji' => $appointmentDate,
                'status_janji' => 'terjadwal',
            ]))
            ->assertOk()
            ->assertSee('Nadia Pasien');

        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.index', ['search' => 'Tidak Ada Pasien']))
            ->assertOk()
            ->assertSee('Tidak ada pasien atau pendaftaran yang sesuai filter.');
    }

    public function test_admin_can_register_patient_without_a_doctor_or_appointment(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->post(route('admin.pendaftaran.store'), [
                'nama_lengkap' => 'Pasien Tanpa Janji',
            ])
            ->assertRedirect(route('admin.pendaftaran.index'));

        $this->assertDatabaseHas('pasien', [
            'nama_lengkap' => 'Pasien Tanpa Janji',
        ]);
        $this->assertDatabaseCount('janji_temu', 0);
    }

    public function test_invalid_or_unavailable_doctor_schedule_does_not_create_patient(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();
        $appointmentDate = now()->addWeek()->toDateString();
        $this->createSchedule($doctorId, $appointmentDate);

        $this->actingAs($admin)
            ->post(route('admin.pendaftaran.store'), [
                'nama_lengkap' => 'Pasien Jadwal Penuh',
                'doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
                'start_time' => '10:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseMissing('pasien', [
            'nama_lengkap' => 'Pasien Jadwal Penuh',
        ]);
        $this->assertDatabaseCount('janji_temu', 0);
    }

    public function test_admin_can_load_available_appointment_slots_for_a_doctor(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();
        $appointmentDate = now()->addWeek()->toDateString();
        $this->createSchedule($doctorId, $appointmentDate);

        $this->actingAs($admin)
            ->getJson(route('admin.jadwal-tersedia', [
                'doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
            ]))
            ->assertOk()
            ->assertJsonPath('slots', ['09:00', '09:30']);
    }

    public function test_admin_can_update_and_soft_delete_patient_without_deleting_their_appointments(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();
        $appointmentDate = now()->addWeek()->toDateString();
        $this->createSchedule($doctorId, $appointmentDate);
        $patientId = DB::table('pasien')->insertGetId([
            'nomor_pasien' => 'PSN-ADMIN-001',
            'nama_lengkap' => 'Pasien Sebelum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $appointmentId = $this->createAppointment($patientId, $doctorId, $appointmentDate, '09:00');

        $this->actingAs($admin)
            ->put(route('admin.pendaftaran.update', $patientId), [
                'nama_lengkap' => 'Pasien Sesudah',
                'telepon' => '0811111111',
            ])
            ->assertRedirect(route('admin.pendaftaran.index'));

        $this->assertDatabaseHas('pasien', [
            'id' => $patientId,
            'nama_lengkap' => 'Pasien Sesudah',
            'telepon' => '0811111111',
        ]);
        $this->assertDatabaseHas('janji_temu', [
            'id' => $appointmentId,
            'dokter_id' => $doctorId,
        ]);
        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.edit', $patientId))
            ->assertOk()
            ->assertSee('Buat janji temu baru (opsional)');

        $this->actingAs($admin)
            ->put(route('admin.pendaftaran.update', $patientId), [
                'nama_lengkap' => 'Pasien Sesudah',
                'telepon' => '0811111111',
                'doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
                'start_time' => '09:30',
            ])
            ->assertRedirect(route('admin.pendaftaran.index'));

        $this->assertDatabaseCount('janji_temu', 2);

        $this->from(route('admin.pendaftaran.index'))
            ->followingRedirects()
            ->actingAs($admin)
            ->delete(route('admin.pendaftaran.destroy', $patientId))
            ->assertOk()
            ->assertSee('Pasien masih memiliki janji temu mendatang.');

        $this->actingAs($admin)
            ->patch(route('admin.pendaftaran.janji-temu.cancel', $appointmentId))
            ->assertRedirect(route('admin.pendaftaran.edit', $patientId));

        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.edit', $patientId))
            ->assertOk()
            ->assertSee('Dibatalkan')
            ->assertSee('Ubah jadwal');

        $newAppointmentId = (int) DB::table('janji_temu')
            ->where('pasien_id', $patientId)
            ->where('status', 'terjadwal')
            ->value('id');
        $this->assertNotSame(0, $newAppointmentId);

        $this->actingAs($admin)
            ->put(route('admin.pendaftaran.janji-temu.update', $newAppointmentId), [
                'doctor_id' => $doctorId,
                'appointment_date' => $appointmentDate,
                'start_time' => '09:00',
            ])
            ->assertRedirect(route('admin.pendaftaran.edit', $patientId));

        $this->actingAs($admin)
            ->patch(route('admin.pendaftaran.janji-temu.cancel', $newAppointmentId))
            ->assertRedirect(route('admin.pendaftaran.edit', $patientId));

        $this->actingAs($admin)
            ->delete(route('admin.pendaftaran.destroy', $patientId))
            ->assertRedirect(route('admin.pendaftaran.index'));

        $this->assertSoftDeleted('pasien', ['id' => $patientId]);
        $this->assertDatabaseHas('janji_temu', ['id' => $appointmentId]);
        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.index'))
            ->assertDontSee('Pasien Sesudah');
        $this->actingAs($admin)
            ->get(route('admin.pendaftaran.edit', $patientId))
            ->assertNotFound();
    }

    public function test_only_admin_can_manage_patient_registrations(): void
    {
        $this->get(route('admin.pendaftaran.index'))
            ->assertRedirect(route('login'));

        $patient = $this->createUserWithRole('pasien');

        $this->actingAs($patient)
            ->get(route('admin.pendaftaran.index'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::where('kode', $role)->value('id'));

        return $user;
    }

    private function createDoctor(): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-ADMIN',
            'nama' => 'Dokter Umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-ADMIN',
            'nama_lengkap' => 'Dokter Tes',
            'nomor_izin_praktik' => 'SIP-ADMIN',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchedule(int $doctorId, string $date): void
    {
        DB::table('jadwal_dokter')->insert([
            'dokter_id' => $doctorId,
            'hari_dalam_minggu' => Carbon::parse($date)->dayOfWeek,
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '10:00:00',
            'durasi_janji_menit' => 30,
            'maksimal_pasien' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(int $patientId, int $doctorId, string $date, string $time): int
    {
        return DB::table('janji_temu')->insertGetId([
            'nomor_janji' => 'JAN-ADMIN-001',
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $date,
            'nomor_antrean' => 1,
            'waktu_mulai' => $time,
            'status' => 'terjadwal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
