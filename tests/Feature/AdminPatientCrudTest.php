<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminPatientCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_admin_can_create_search_and_view_patient_history(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();

        $response = $this->actingAs($admin)->post(route('admin.pasien.store'), [
            'nomor_identitas' => '1234567890123456',
            'nama_lengkap' => 'Nadia Pasien',
            'tanggal_lahir' => '1990-04-15',
            'jenis_kelamin' => 'perempuan',
            'telepon' => '+62 812-3456-7890',
            'email' => 'nadia@example.test',
            'alamat' => 'Jalan Sehat No. 10',
            'golongan_darah' => 'O+',
        ]);

        $patientId = (int) DB::table('pasien')->where('nomor_identitas', '1234567890123456')->value('id');
        $response->assertRedirect(route('admin.pasien.show', $patientId));
        $this->assertDatabaseHas('pasien', [
            'id' => $patientId,
            'nomor_pasien' => 'PSN-0001',
            'nama_lengkap' => 'Nadia Pasien',
            'golongan_darah' => 'O+',
        ]);

        DB::table('janji_temu')->insert([
            'nomor_janji' => 'JAN-PASIEN-001',
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => '2026-11-25',
            'nomor_antrean' => 1,
            'waktu_mulai' => '09:00:00',
            'status' => 'selesai',
            'keluhan_utama' => 'Pemeriksaan rutin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('rekam_medis')->insert([
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => '2026-11-25 09:30:00',
            'keluhan_utama' => 'Pemeriksaan rutin',
            'diagnosis' => 'Sehat',
            'rencana_perawatan' => 'Kontrol berkala',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pasien.index', ['search' => '1234567890123456']))
            ->assertOk()
            ->assertSee('Nadia Pasien');

        $this->actingAs($admin)
            ->get(route('admin.pasien.show', $patientId))
            ->assertOk()
            ->assertSee('JAN-PASIEN-001')
            ->assertSee('Pemeriksaan rutin')
            ->assertSee('Sehat')
            ->assertSee('Kontrol berkala')
            ->assertDontSee('Ubah hasil pemeriksaan');
    }

    public function test_duplicate_or_invalid_patient_data_is_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->createPatient('Pasien Tersimpan', '1234567890123456');

        $this->actingAs($admin)
            ->from(route('admin.pasien.create'))
            ->post(route('admin.pasien.store'), [
                'nomor_identitas' => '1234567890123456',
                'nama_lengkap' => 'Pasien Duplikat',
            ])
            ->assertRedirect(route('admin.pasien.create'))
            ->assertSessionHasErrors([
                'nomor_identitas' => 'NIK sudah terdaftar.',
            ]);

        $this->actingAs($admin)
            ->post(route('admin.pasien.store'), [
                'nomor_identitas' => '12345',
                'nama_lengkap' => 'Pasien Tidak Valid',
                'tanggal_lahir' => now()->addDay()->toDateString(),
                'jenis_kelamin' => 'tidak-valid',
                'telepon' => 'nomor-salah',
                'golongan_darah' => 'X+',
            ])
            ->assertSessionHasErrors([
                'nomor_identitas',
                'tanggal_lahir',
                'jenis_kelamin',
                'telepon',
                'golongan_darah',
            ]);

        $this->assertDatabaseMissing('pasien', ['nama_lengkap' => 'Pasien Duplikat']);
        $this->assertDatabaseMissing('pasien', ['nama_lengkap' => 'Pasien Tidak Valid']);
    }

    public function test_generated_patient_number_skips_an_existing_number(): void
    {
        $admin = $this->createUserWithRole('admin');
        DB::table('pasien')->insert([
            'nomor_pasien' => 'PSN-0002',
            'nama_lengkap' => 'Pasien Nomor Lama',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pasien.store'), [
                'nama_lengkap' => 'Pasien Nomor Baru',
            ])
            ->assertRedirect(route('admin.pasien.show', 2));

        $this->assertDatabaseHas('pasien', [
            'id' => 2,
            'nomor_pasien' => 'PSN-0003',
            'nama_lengkap' => 'Pasien Nomor Baru',
        ]);
    }

    public function test_patient_with_any_clinic_history_cannot_be_deleted(): void
    {
        $admin = $this->createUserWithRole('admin');
        $doctorId = $this->createDoctor();

        $appointmentPatientId = $this->createPatient('Pasien Janji', '1234567890123456');
        DB::table('janji_temu')->insert([
            'nomor_janji' => 'JAN-PASIEN-002',
            'pasien_id' => $appointmentPatientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => today()->toDateString(),
            'status' => 'dibatalkan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recordPatientId = $this->createPatient('Pasien Pemeriksaan', '2234567890123456');
        DB::table('rekam_medis')->insert([
            'pasien_id' => $recordPatientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => now(),
            'diagnosis' => 'Demam',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $paymentPatientId = $this->createPatient('Pasien Pembayaran', '3234567890123456');
        DB::table('pembayaran')->insert([
            'nomor_pembayaran' => 'PAY-PASIEN-001',
            'pasien_id' => $paymentPatientId,
            'jumlah' => 50000,
            'metode_pembayaran' => 'tunai',
            'status' => 'lunas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([$appointmentPatientId, $recordPatientId, $paymentPatientId] as $patientId) {
            $this->actingAs($admin)
                ->from(route('admin.pasien.index'))
                ->delete(route('admin.pasien.destroy', $patientId))
                ->assertRedirect(route('admin.pasien.index'))
                ->assertSessionHasErrors('patient');

            $this->assertDatabaseHas('pasien', ['id' => $patientId, 'deleted_at' => null]);
        }
    }

    public function test_unreferenced_patient_can_be_updated_and_soft_deleted(): void
    {
        $admin = $this->createUserWithRole('admin');
        $patientId = $this->createPatient('Sebelum Diubah', '4234567890123456');

        $this->actingAs($admin)
            ->put(route('admin.pasien.update', $patientId), [
                'nomor_identitas' => '4234567890123456',
                'nama_lengkap' => 'Sesudah Diubah',
                'jenis_kelamin' => 'perempuan',
            ])
            ->assertRedirect(route('admin.pasien.show', $patientId));

        $this->assertDatabaseHas('pasien', [
            'id' => $patientId,
            'nomor_identitas' => '4234567890123456',
            'nama_lengkap' => 'Sesudah Diubah',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.pasien.destroy', $patientId))
            ->assertRedirect(route('admin.pasien.index'));

        $this->assertSoftDeleted('pasien', ['id' => $patientId]);
    }

    public function test_only_admin_can_manage_patient_profiles(): void
    {
        $this->get(route('admin.pasien.index'))
            ->assertRedirect(route('login'));

        $patient = $this->createUserWithRole('pasien');

        $this->actingAs($patient)
            ->get(route('admin.pasien.index'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->peran()->attach(Peran::where('kode', $role)->value('id'));

        return $user;
    }

    private function createPatient(string $name, string $nationalId): int
    {
        return DB::table('pasien')->insertGetId([
            'nomor_pasien' => 'PSN-TEST-'.$nationalId,
            'nomor_identitas' => $nationalId,
            'nama_lengkap' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createDoctor(): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-PASIEN',
            'nama' => 'Dokter Umum',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-PASIEN',
            'nama_lengkap' => 'Dokter Pasien',
            'nomor_izin_praktik' => 'SIP-PASIEN',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
