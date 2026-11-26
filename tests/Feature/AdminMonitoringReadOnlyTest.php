<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminMonitoringReadOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_registration_list_supports_combined_filters_and_read_only_detail(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor('A');
        $otherDoctorId = $this->createDoctor('B');
        $appointmentDate = today()->subDay()->toDateString();
        $this->createSchedule($doctorId, $appointmentDate);

        $matchingPatientId = $this->createPatient('Sari Pemeriksaan', 'PSN-MON-001');
        $matchingAppointmentId = $this->createAppointment(
            $matchingPatientId,
            $doctorId,
            $appointmentDate,
            '10:00',
            'selesai',
            'JAN-MON-001',
        );
        $otherPatientId = $this->createPatient('Budi Pemeriksaan', 'PSN-MON-002');
        $this->createAppointment(
            $otherPatientId,
            $otherDoctorId,
            today()->toDateString(),
            '10:00',
            'dibatalkan',
            'JAN-MON-002',
        );

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pendaftaran.index', [
                'start_date' => $appointmentDate,
                'end_date' => today()->toDateString(),
                'doctor_id' => $doctorId,
                'status' => 'selesai',
                'search' => 'Sari',
            ]))
            ->assertOk()
            ->assertSee('JAN-MON-001')
            ->assertSee('Sari Pemeriksaan')
            ->assertSee('Dokter A')
            ->assertSee('09:00 - 12:00')
            ->assertDontSee('JAN-MON-002')
            ->assertDontSee('Budi Pemeriksaan');

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pendaftaran.show', $matchingAppointmentId))
            ->assertOk()
            ->assertSee('Nomor antrean')
            ->assertSee('Keluhan Pendaftaran')
            ->assertSee('Jadwal praktik')
            ->assertDontSee('Simpan')
            ->assertDontSee('Edit');

        $this->actingAs($admin)
            ->get('/admin/pemantauan/pendaftaran/'.$matchingAppointmentId.'/edit')
            ->assertNotFound();
        $this->assertDatabaseHas('janji_temu', [
            'id' => $matchingAppointmentId,
            'status' => 'selesai',
        ]);
    }

    public function test_service_monitoring_filters_patient_and_shows_vitals_and_prescription_details(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor('C');
        $otherDoctorId = $this->createDoctor('D');
        $patientId = $this->createPatient('Nadia Pelayanan', 'PSN-MON-003');
        $appointmentDate = today()->subDay()->toDateString();
        $appointmentId = $this->createAppointment(
            $patientId,
            $doctorId,
            $appointmentDate,
            '09:00',
            'selesai',
            'JAN-MON-003',
        );

        $recordId = DB::table('rekam_medis')->insertGetId([
            'janji_temu_id' => $appointmentId,
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => $appointmentDate.' 09:30:00',
            'keluhan_utama' => 'Batuk',
            'diagnosis' => 'Infeksi saluran pernapasan',
            'rencana_perawatan' => 'Istirahat dan kontrol',
            'tekanan_sistolik' => 120,
            'tekanan_diastolik' => 80,
            'denyut_jantung' => 72,
            'suhu_tubuh' => 36.5,
            'tinggi_badan_cm' => 165,
            'berat_badan_kg' => 60,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createPrescription($recordId);

        $otherPatientId = $this->createPatient('Rudi Pelayanan', 'PSN-MON-004');
        $otherAppointmentId = $this->createAppointment(
            $otherPatientId,
            $otherDoctorId,
            today()->toDateString(),
            '11:00',
            'selesai',
            'JAN-MON-004',
        );
        DB::table('rekam_medis')->insert([
            'janji_temu_id' => $otherAppointmentId,
            'pasien_id' => $otherPatientId,
            'dokter_id' => $otherDoctorId,
            'dicatat_pada' => today()->toDateString().' 11:30:00',
            'diagnosis' => 'Pemeriksaan umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pelayanan.index', [
                'start_date' => $appointmentDate,
                'end_date' => today()->toDateString(),
                'doctor_id' => $doctorId,
                'search' => 'Nadia',
            ]))
            ->assertOk()
            ->assertSee('Nadia Pelayanan')
            ->assertSee('Infeksi saluran pernapasan')
            ->assertSee('Obat Monitoring')
            ->assertSee('120/80 mmHg')
            ->assertDontSee('Rudi Pelayanan');

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pelayanan.show', $recordId))
            ->assertOk()
            ->assertSee('Nadia Pelayanan')
            ->assertSee('Istirahat dan kontrol')
            ->assertSee('120/80 mmHg')
            ->assertSee('72 bpm')
            ->assertSee('36.5')
            ->assertSee('165 cm')
            ->assertSee('60 kg')
            ->assertSee('3 hari')
            ->assertSee('Sesudah makan')
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus');

        $this->actingAs($admin)
            ->get('/admin/pemantauan/pelayanan/'.$recordId.'/edit')
            ->assertNotFound();
        $this->actingAs($admin)
            ->post('/admin/pemantauan/pelayanan')
            ->assertMethodNotAllowed();
        $this->assertDatabaseHas('rekam_medis', ['id' => $recordId, 'diagnosis' => 'Infeksi saluran pernapasan']);
    }

    public function test_monitoring_pages_show_a_clear_empty_state(): void
    {
        $admin = $this->createUserWithRole('admin');
        $emptyDate = now()->subYears(5)->toDateString();

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pendaftaran.index', [
                'start_date' => $emptyDate,
                'end_date' => $emptyDate,
            ]))
            ->assertOk()
            ->assertSee('Belum ada data pendaftaran');

        $this->actingAs($admin)
            ->get(route('admin.pemantauan.pelayanan.index', [
                'start_date' => $emptyDate,
                'end_date' => $emptyDate,
            ]))
            ->assertOk()
            ->assertSee('Belum ada data pelayanan');
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::where('kode', $role)->value('id'));

        return $user;
    }

    private function createDoctor(string $suffix): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-MON-'.$suffix,
            'nama' => 'Spesialis '.$suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-MON-'.$suffix,
            'nama_lengkap' => 'Dokter '.$suffix,
            'nomor_izin_praktik' => 'SIP-MON-'.$suffix,
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
            'waktu_selesai' => '12:00:00',
            'durasi_janji_menit' => 30,
            'maksimal_pasien' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPatient(string $name, string $patientNumber): int
    {
        return DB::table('pasien')->insertGetId([
            'nomor_pasien' => $patientNumber,
            'nama_lengkap' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(
        int $patientId,
        int $doctorId,
        string $date,
        string $time,
        string $status,
        string $number,
    ): int {
        return DB::table('janji_temu')->insertGetId([
            'nomor_janji' => $number,
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $date,
            'nomor_antrean' => 1,
            'waktu_mulai' => $time,
            'status' => $status,
            'keluhan_utama' => 'Keluhan Pendaftaran',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createPrescription(int $recordId): void
    {
        $medicineId = DB::table('obat')->insertGetId([
            'kode' => 'OBAT-MON-001',
            'nama' => 'Obat Monitoring',
            'satuan' => 'tablet',
            'stok' => 20,
            'harga_satuan' => 5000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $prescriptionId = DB::table('resep')->insertGetId([
            'rekam_medis_id' => $recordId,
            'nomor_resep' => 'RSP-MON-001',
            'diresepkan_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('detail_resep')->insert([
            'resep_id' => $prescriptionId,
            'obat_id' => $medicineId,
            'dosis' => '500 mg',
            'frekuensi' => '2 kali sehari',
            'durasi_hari' => 3,
            'jumlah' => 6,
            'petunjuk_pemakaian' => 'Sesudah makan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
