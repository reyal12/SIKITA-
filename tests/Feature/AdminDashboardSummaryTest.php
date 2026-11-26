<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_dashboard_counts_today_in_jakarta_and_limits_newest_appointments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-02 17:30:00', 'UTC'));

        try {
            $admin = $this->createAdmin();
            $patientId = $this->createPatient('Pasien Dashboard', 'PSN-DASH-001');
            $deletedPatientId = $this->createPatient('Pasien Terhapus', 'PSN-DASH-002');
            DB::table('pasien')->where('id', $deletedPatientId)->update(['deleted_at' => now()]);

            $activeDoctorId = $this->createDoctor('Aktif', true);
            $this->createDoctor('Nonaktif', false);

            $oldestAppointmentId = null;

            for ($number = 0; $number < 10; $number++) {
                $appointmentId = $this->createAppointment(
                    $patientId,
                    $activeDoctorId,
                    '2026-05-03',
                    sprintf('JAN-DASH-%03d', $number),
                    Carbon::parse('2026-05-02 08:00:00', 'UTC')->addMinutes($number),
                    $number + 1,
                );

                if ($number === 0) {
                    $oldestAppointmentId = $appointmentId;
                }
            }

            $newestAppointmentId = $this->createAppointment(
                $patientId,
                $activeDoctorId,
                '2026-05-02',
                'JAN-DASH-OLD-NEW',
                Carbon::parse('2026-05-02 09:00:00', 'UTC'),
                1,
            );

            $response = $this->actingAs($admin)
                ->get(route('admin.dashboard'))
                ->assertOk()
                ->assertViewHas('stats', [
                    'patients' => 1,
                    'active_doctors' => 1,
                    'appointments_today' => 10,
                ])
                ->assertSee('Pasien terdaftar')
                ->assertSee('Dokter aktif')
                ->assertSee('Pendaftaran hari ini')
                ->assertSee(route('admin.pemantauan.pendaftaran.index'), false)
                ->assertSee(route('admin.pemantauan.pendaftaran.show', $newestAppointmentId), false);

            $appointments = $response->viewData('appointments');

            $this->assertSame(10, $appointments->count());
            $this->assertSame($newestAppointmentId, $appointments->first()->id);
            $this->assertNotContains($oldestAppointmentId, $appointments->pluck('id')->all());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_dashboard_shows_empty_state_without_payment_or_billing_sections(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pasien terdaftar')
            ->assertSee('Dokter aktif')
            ->assertSee('Pendaftaran hari ini')
            ->assertSee('Belum ada data pendaftaran terbaru.')
            ->assertDontSee('Tagihan menunggu')
            ->assertDontSee('Catat tagihan')
            ->assertDontSee('Riwayat pembayaran');
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->peran()->attach(Peran::where('kode', 'admin')->value('id'));

        return $admin;
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

    private function createDoctor(string $suffix, bool $isActive): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-DASH-'.$suffix,
            'nama' => 'Spesialis '.$suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-DASH-'.$suffix,
            'nama_lengkap' => 'Dokter '.$suffix,
            'nomor_izin_praktik' => 'SIP-DASH-'.$suffix,
            'aktif' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createAppointment(
        int $patientId,
        int $doctorId,
        string $appointmentDate,
        string $appointmentNumber,
        Carbon $createdAt,
        int $queueNumber,
    ): int {
        return DB::table('janji_temu')->insertGetId([
            'nomor_janji' => $appointmentNumber,
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => $appointmentDate,
            'nomor_antrean' => $queueNumber,
            'waktu_mulai' => '09:00:00',
            'status' => 'terjadwal',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
