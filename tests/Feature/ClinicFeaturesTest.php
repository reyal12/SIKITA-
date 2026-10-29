<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClinicFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_patient_can_book_a_free_slot_and_receive_a_queue_number(): void
    {
        $date = now()->addDay()->toDateString();
        $doctorId = $this->createDoctor();
        $this->createSchedule($doctorId, $date);
        $firstPatient = $this->createUserWithRole('pasien');
        $secondPatient = $this->createUserWithRole('pasien');

        $firstResponse = $this->actingAs($firstPatient)->post(route('pasien.janji-temu.store'), [
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'chief_complaint' => 'Demam sejak kemarin',
        ]);
        $firstResponse->assertRedirect(route('pasien.dashboard'));
        $firstAppointment = DB::table('janji_temu')->where('nomor_antrean', 1)->first();

        $this->assertNotNull($firstAppointment);
        $this->assertSame('terjadwal', $firstAppointment->status);
        $this->assertDatabaseHas('pasien', ['pengguna_id' => $firstPatient->id]);
        $this->assertSame('Demam sejak kemarin', $firstAppointment->keluhan_utama);

        $this->actingAs($secondPatient)
            ->post(route('pasien.janji-temu.store'), [
                'doctor_id' => $doctorId,
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertSessionHasErrors('start_time');

        $secondResponse = $this->actingAs($secondPatient)->post(route('pasien.janji-temu.store'), [
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'start_time' => '09:30',
        ]);
        $secondResponse->assertRedirect(route('pasien.dashboard'));
        $this->assertDatabaseHas('janji_temu', [
            'dokter_id' => $doctorId,
            'tanggal_janji' => $date,
            'nomor_antrean' => 2,
            'status' => 'terjadwal',
        ]);

        $this->actingAs($firstPatient)
            ->post(route('pasien.janji-temu.cancel', $firstAppointment->id))
            ->assertRedirect(route('pasien.dashboard'));
        $this->assertDatabaseHas('janji_temu', [
            'id' => $firstAppointment->id,
            'status' => 'dibatalkan',
        ]);

        $this->actingAs($secondPatient)
            ->post(route('pasien.janji-temu.cancel', $firstAppointment->id))
            ->assertNotFound();
    }

    public function test_available_slots_exclude_booked_times_and_full_schedules(): void
    {
        $date = now()->addDay()->toDateString();
        $doctorId = $this->createDoctor();
        $this->createSchedule($doctorId, $date, maxPatients: 2);
        $patient = $this->createUserWithRole('pasien');
        $patientId = $this->createPatientRecord($patient);
        $this->createAppointment($patientId, $doctorId, $date, '09:00:00');

        $response = $this->actingAs($patient)->getJson(route('pasien.jadwal-tersedia', [
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
        ]));

        $response->assertOk()
            ->assertJsonPath('slots', ['09:30']);

        $this->createAppointment($patientId, $doctorId, $date, '09:30:00');
        $this->actingAs($patient)->getJson(route('pasien.jadwal-tersedia', [
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
        ]))->assertOk()->assertJsonPath('slots', []);
    }

    public function test_patient_dashboard_shows_profile_examination_prescriptions_and_payment_history(): void
    {
        $patient = $this->createUserWithRole('pasien');
        $patientId = $this->createPatientRecord($patient);
        $doctorId = $this->createDoctor();
        $appointmentId = $this->createAppointment($patientId, $doctorId, now()->subDay()->toDateString(), '09:00:00', 'selesai');
        $recordId = DB::table('rekam_medis')->insertGetId([
            'janji_temu_id' => $appointmentId,
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => now()->subDay(),
            'keluhan_utama' => 'Batuk',
            'diagnosis' => 'Flu ringan',
            'rencana_perawatan' => 'Istirahat dan minum air putih',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $medicineId = DB::table('obat')->insertGetId([
            'kode' => 'OBT-001',
            'nama' => 'Parasetamol',
            'satuan' => 'tablet',
            'stok' => 25,
            'stok_minimum' => 5,
            'harga_satuan' => 1500,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $prescriptionId = DB::table('resep')->insertGetId([
            'rekam_medis_id' => $recordId,
            'nomor_resep' => 'RSP-001',
            'diresepkan_pada' => now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_resep')->insert([
            'resep_id' => $prescriptionId,
            'obat_id' => $medicineId,
            'dosis' => '500 mg',
            'frekuensi' => '3 kali sehari',
            'durasi_hari' => 3,
            'jumlah' => 9,
            'petunjuk_pemakaian' => 'Sesudah makan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pembayaran')->insert([
            'nomor_pembayaran' => 'BYR-TEST',
            'pasien_id' => $patientId,
            'janji_temu_id' => $appointmentId,
            'jumlah' => 125000,
            'metode_pembayaran' => 'tunai',
            'status' => 'dibayar',
            'dibayar_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pembayaran')->insert([
            'nomor_pembayaran' => 'BYR-UNPAID',
            'pasien_id' => $patientId,
            'jumlah' => 50000,
            'metode_pembayaran' => 'transfer_bank',
            'status' => 'menunggu',
            'created_at' => now()->addSecond(),
            'updated_at' => now()->addSecond(),
        ]);
        $this->createAppointment(
            $patientId,
            $doctorId,
            now()->addDay()->toDateString(),
            now()->addHour()->format('H:i:s'),
        );

        $this->actingAs($patient)
            ->get(route('pasien.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Pasien')
            ->assertSee('Patient')
            ->assertSee('Selamat Datang, '.$patient->name)
            ->assertSee('Janji Temu Aktif')
            ->assertSee('Bayar Sekarang')
            ->assertSee('50.000')
            ->assertSee('href="'.route('pasien.dashboard').'"', false)
            ->assertSee('href="'.route('pasien.janji-temu').'"', false)
            ->assertSee('href="'.route('pasien.riwayat-medis').'"', false)
            ->assertSee('href="'.route('pasien.pembayaran').'"', false)
            ->assertSee('href="'.route('pasien.profil').'"', false)
            ->assertSee('Riwayat Kunjungan &amp; Medis Terakhir', false)
            ->assertDontSee('Buat janji temu baru')
            ->assertDontSee('Profil Pasien')
            ->assertDontSee('BYR-TEST');

        $this->actingAs($patient)
            ->get(route('pasien.janji-temu'))
            ->assertOk()
            ->assertSee('Semua Janji Temu')
            ->assertSee('Buat janji temu baru')
            ->assertSee('JAN-', false);

        $this->actingAs($patient)
            ->get(route('pasien.riwayat-medis'))
            ->assertOk()
            ->assertSee('Hasil Pemeriksaan')
            ->assertSee('Flu ringan')
            ->assertSee('RSP-001')
            ->assertSee('Parasetamol');

        $this->actingAs($patient)
            ->get(route('pasien.pembayaran'))
            ->assertOk()
            ->assertSee('BYR-TEST')
            ->assertSee('BYR-UNPAID');

        $this->actingAs($patient)
            ->get(route('pasien.profil'))
            ->assertOk()
            ->assertSee('Profil Pasien')
            ->assertSee('Simpan Profil');
    }

    public function test_patient_can_update_their_profile_without_creating_a_record_at_registration(): void
    {
        $patient = $this->createUserWithRole('pasien');

        $this->assertDatabaseMissing('pasien', ['pengguna_id' => $patient->id]);

        $this->actingAs($patient)->post(route('pasien.profil.update'), [
            'nama_lengkap' => 'Nama Pasien Baru',
            'telepon' => '08123456789',
            'tanggal_lahir' => '1990-05-10',
            'jenis_kelamin' => 'perempuan',
            'alamat' => 'Bandung',
        ])->assertRedirect(route('pasien.dashboard'));

        $this->assertDatabaseHas('pasien', [
            'pengguna_id' => $patient->id,
            'nama_lengkap' => 'Nama Pasien Baru',
            'telepon' => '08123456789',
            'jenis_kelamin' => 'perempuan',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'name' => 'Nama Pasien Baru',
        ]);
    }

    public function test_admin_can_record_and_update_a_payment_status(): void
    {
        $admin = $this->createUserWithRole('admin');
        $patientId = $this->createPatientRecord($this->createUserWithRole('pasien'));
        $doctorId = $this->createDoctor();
        $appointmentId = $this->createAppointment(
            $patientId,
            $doctorId,
            now()->addDay()->toDateString(),
            '09:00:00',
        );

        $this->actingAs($admin)->post(route('admin.pembayaran.store'), [
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'amount' => '125000',
            'payment_method' => 'transfer_bank',
            'status' => 'menunggu',
            'notes' => 'Konsultasi',
        ])->assertRedirect(route('admin.dashboard'));

        $payment = DB::table('pembayaran')->first();
        $this->assertSame('menunggu', $payment->status);
        $this->assertNull($payment->dibayar_pada);

        $this->actingAs($admin)
            ->patch(route('admin.pembayaran.status', $payment->id), ['status' => 'dibayar'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('pembayaran', [
            'id' => $payment->id,
            'status' => 'dibayar',
        ]);
        $this->assertNotNull(DB::table('pembayaran')->where('id', $payment->id)->value('dibayar_pada'));
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Data pendaftaran')
            ->assertSee('Riwayat pembayaran')
            ->assertSee('BYR-');

        $otherPatientId = $this->createPatientRecord($this->createUserWithRole('pasien'));
        $this->actingAs($admin)
            ->post(route('admin.pembayaran.store'), [
                'patient_id' => $otherPatientId,
                'appointment_id' => $appointmentId,
                'amount' => '10000',
                'payment_method' => 'tunai',
                'status' => 'menunggu',
            ])
            ->assertSessionHasErrors('appointment_id');

        $this->assertSame(1, DB::table('pembayaran')->count());
    }

    public function test_doctor_can_complete_only_their_own_scheduled_appointments(): void
    {
        $doctorUser = $this->createUserWithRole('dokter');
        $doctorId = $this->createDoctor($doctorUser);
        $patientId = $this->createPatientRecord($this->createUserWithRole('pasien'));
        $appointmentId = $this->createAppointment(
            $patientId,
            $doctorId,
            now()->addDay()->toDateString(),
            '09:00:00',
        );

        $this->actingAs($doctorUser)
            ->get(route('dokter.dashboard'))
            ->assertOk()
            ->assertSee('Pendaftaran pasien')
            ->assertSee(DB::table('pasien')->where('id', $patientId)->value('nama_lengkap'));

        $this->actingAs($doctorUser)
            ->post(route('dokter.janji-temu.complete', $appointmentId))
            ->assertRedirect(route('dokter.dashboard'));

        $this->assertDatabaseHas('janji_temu', [
            'id' => $appointmentId,
            'status' => 'selesai',
        ]);

        $otherDoctorUser = $this->createUserWithRole('dokter');
        $otherDoctorId = $this->createDoctor($otherDoctorUser);
        $otherAppointmentId = $this->createAppointment(
            $patientId,
            $otherDoctorId,
            now()->addDay()->toDateString(),
            '09:30:00',
        );

        $this->actingAs($doctorUser)
            ->post(route('dokter.janji-temu.complete', $otherAppointmentId))
            ->assertNotFound();
        $this->assertDatabaseHas('janji_temu', [
            'id' => $otherAppointmentId,
            'status' => 'terjadwal',
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::where('kode', $role)->value('id'));

        return $user;
    }

    private function createPatientRecord(User $user): int
    {
        return DB::table('pasien')->insertGetId([
            'nomor_pasien' => 'PSN-'.strtoupper(fake()->unique()->bothify('##########')),
            'nama_lengkap' => $user->name,
            'email' => $user->email,
            'pengguna_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDoctor(?User $user = null): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-'.strtoupper(fake()->unique()->bothify('####')),
            'nama' => fake()->unique()->word(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'pengguna_id' => $user?->id,
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-'.strtoupper(fake()->unique()->bothify('######')),
            'nama_lengkap' => $user?->name ?? fake()->name(),
            'nomor_izin_praktik' => 'SIP-'.strtoupper(fake()->unique()->bothify('########')),
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSchedule(int $doctorId, string $date, int $maxPatients = 5): void
    {
        DB::table('jadwal_dokter')->insert([
            'dokter_id' => $doctorId,
            'hari_dalam_minggu' => Carbon::parse($date)->dayOfWeek,
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '10:00:00',
            'durasi_janji_menit' => 30,
            'maksimal_pasien' => $maxPatients,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(
        int $patientId,
        int $doctorId,
        string $date,
        string $time,
        string $status = 'terjadwal',
    ): int {
        return DB::table('janji_temu')->insertGetId([
            'nomor_janji' => 'JAN-'.strtoupper(fake()->unique()->bothify('##########')),
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $date,
            'nomor_antrean' => ((int) DB::table('janji_temu')
                ->where('dokter_id', $doctorId)
                ->where('tanggal_janji', $date)
                ->max('nomor_antrean')) + 1,
            'waktu_mulai' => $time,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
