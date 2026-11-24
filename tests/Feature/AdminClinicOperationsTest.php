<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\PeranSeeder;
use Database\Seeders\SpecialtySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminClinicOperationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PeranSeeder::class);
    }

    public function test_specialty_seeder_can_run_repeatedly_without_duplicating_specialties(): void
    {
        $this->seed(SpecialtySeeder::class);

        $this->assertDatabaseCount('spesialis', 8);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('spesialis', 8);
        $this->assertDatabaseHas('spesialis', [
            'kode' => 'SP-UMUM',
            'nama' => 'Dokter Umum',
        ]);
        $this->assertDatabaseHas('spesialis', [
            'kode' => 'SP-GIGI',
            'nama' => 'Kesehatan Gigi',
        ]);

        $this->seed(SpecialtySeeder::class);

        $this->assertDatabaseCount('spesialis', 8);
    }

    public function test_admin_can_create_update_view_and_safely_delete_medicines(): void
    {
        $admin = $this->createAdmin();
        $medicineData = [
            'kode' => 'OBAT-001',
            'nama' => 'Parasetamol',
            'kategori' => 'Analgesik',
            'satuan' => 'tablet',
            'stok' => 4,
            'stok_minimum' => 5,
            'harga_satuan' => 2500,
            'tanggal_kedaluwarsa' => now()->addYear()->toDateString(),
            'aktif' => 1,
        ];

        $this->actingAs($admin)
            ->get(route('admin.obat.create'))
            ->assertOk()
            ->assertSee('Tambah Obat');

        $this->actingAs($admin)
            ->post(route('admin.obat.store'), $medicineData)
            ->assertRedirect(route('admin.obat.index'));

        $medicineId = (int) DB::table('obat')->where('kode', 'OBAT-001')->value('id');
        $this->assertDatabaseHas('obat', [
            'id' => $medicineId,
            'nama' => 'Parasetamol',
            'stok' => 4,
            'aktif' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.obat.index'))
            ->assertOk()
            ->assertSee('Parasetamol')
            ->assertSee('Stok minimum');

        $this->actingAs($admin)
            ->get(route('admin.obat.edit', $medicineId))
            ->assertOk()
            ->assertSee('Edit Obat');

        $this->actingAs($admin)
            ->put(route('admin.obat.update', $medicineId), array_replace($medicineData, [
                'kode' => 'OBAT-002',
                'stok' => 20,
            ]))
            ->assertRedirect(route('admin.obat.index'));
        $this->assertDatabaseHas('obat', ['id' => $medicineId, 'kode' => 'OBAT-002', 'stok' => 20]);

        $doctorId = $this->createDoctor();
        $patientId = $this->createPatient();
        $medicalRecordId = DB::table('rekam_medis')->insertGetId([
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => now(),
            'diagnosis' => 'Demam',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $prescriptionId = DB::table('resep')->insertGetId([
            'rekam_medis_id' => $medicalRecordId,
            'nomor_resep' => 'RSP-OBAT-001',
            'diresepkan_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_resep')->insert([
            'resep_id' => $prescriptionId,
            'obat_id' => $medicineId,
            'dosis' => '500 mg',
            'frekuensi' => '3 kali sehari',
            'jumlah' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.obat.destroy', $medicineId))
            ->assertSessionHasErrors('medicine');
        $this->assertDatabaseHas('obat', ['id' => $medicineId]);

        $this->actingAs($admin)
            ->put(route('admin.obat.update', $medicineId), array_replace($medicineData, [
                'kode' => 'OBAT-002',
                'aktif' => 0,
            ]))
            ->assertRedirect(route('admin.obat.index'));
        $this->assertDatabaseHas('obat', ['id' => $medicineId, 'aktif' => false]);
    }

    public function test_admin_can_manage_clinic_services_and_activities(): void
    {
        $admin = $this->createAdmin();
        $information = [
            'kategori' => 'kegiatan',
            'judul' => 'Pemeriksaan Gratis',
            'deskripsi' => 'Pemeriksaan kesehatan untuk masyarakat.',
            'tanggal_mulai' => now()->addDays(3)->toDateString(),
            'tanggal_selesai' => now()->addDays(4)->toDateString(),
            'aktif' => 1,
        ];

        $this->actingAs($admin)
            ->get(route('admin.informasi-klinik.create'))
            ->assertOk()
            ->assertSee('Tambah Informasi Klinik');

        $this->actingAs($admin)
            ->post(route('admin.informasi-klinik.store'), $information)
            ->assertRedirect(route('admin.informasi-klinik.index'));

        $itemId = (int) DB::table('informasi_klinik')->where('judul', 'Pemeriksaan Gratis')->value('id');
        $this->actingAs($admin)
            ->get(route('admin.informasi-klinik.index'))
            ->assertOk()
            ->assertSee('Pemeriksaan Gratis')
            ->assertSee('Kegiatan');

        $this->actingAs($admin)
            ->get(route('admin.informasi-klinik.edit', $itemId))
            ->assertOk()
            ->assertSee('Edit Informasi Klinik');

        $this->actingAs($admin)
            ->put(route('admin.informasi-klinik.update', $itemId), array_replace($information, [
                'kategori' => 'layanan',
                'judul' => 'Layanan Pemeriksaan',
            ]))
            ->assertRedirect(route('admin.informasi-klinik.index'));
        $this->assertDatabaseHas('informasi_klinik', [
            'id' => $itemId,
            'kategori' => 'layanan',
            'judul' => 'Layanan Pemeriksaan',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.informasi-klinik.update', $itemId), [
                ...$information,
                'tanggal_mulai' => now()->addDays(5)->toDateString(),
                'tanggal_selesai' => now()->addDays(4)->toDateString(),
            ])
            ->assertSessionHasErrors('tanggal_selesai');

        $this->actingAs($admin)
            ->delete(route('admin.informasi-klinik.destroy', $itemId))
            ->assertRedirect(route('admin.informasi-klinik.index'));
        $this->assertDatabaseMissing('informasi_klinik', ['id' => $itemId]);
    }

    public function test_admin_can_monitor_completed_care_and_generate_period_reports(): void
    {
        $admin = $this->createAdmin();
        $doctorId = $this->createDoctor();
        $patientId = $this->createPatient();
        $appointmentId = DB::table('janji_temu')->insertGetId([
            'nomor_janji' => 'JAN-LAP-001',
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'tanggal_janji' => today()->toDateString(),
            'nomor_antrean' => 1,
            'waktu_mulai' => '09:00',
            'status' => 'selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $medicalRecordId = DB::table('rekam_medis')->insertGetId([
            'janji_temu_id' => $appointmentId,
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => now(),
            'keluhan_utama' => 'Batuk',
            'diagnosis' => 'Infeksi saluran pernapasan',
            'rencana_perawatan' => 'Istirahat dan minum obat.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $medicineId = $this->createMedicine();
        $prescriptionId = DB::table('resep')->insertGetId([
            'rekam_medis_id' => $medicalRecordId,
            'nomor_resep' => 'RSP-LAP-001',
            'diresepkan_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_resep')->insert([
            'resep_id' => $prescriptionId,
            'obat_id' => $medicineId,
            'dosis' => '1 tablet',
            'frekuensi' => '2 kali sehari',
            'jumlah' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pembayaran')->insert([
            'nomor_pembayaran' => 'BYR-LAP-001',
            'pasien_id' => $patientId,
            'janji_temu_id' => $appointmentId,
            'jumlah' => 50000,
            'metode_pembayaran' => 'tunai',
            'status' => 'dibayar',
            'dibayar_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $period = [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ];

        $this->actingAs($admin)
            ->get(route('admin.pelayanan.index', $period + ['search' => 'Infeksi']))
            ->assertOk()
            ->assertSee('Pasien Operasional')
            ->assertSee('Infeksi saluran pernapasan')
            ->assertSee('Obat Operasional');

        $this->actingAs($admin)
            ->get(route('admin.laporan.index', $period))
            ->assertOk()
            ->assertSee('Ringkasan laporan')
            ->assertSee('Pemeriksaan tercatat')
            ->assertSee('Rp50.000')
            ->assertSee($this->doctorName($doctorId));
    }

    public function test_new_admin_sections_are_forbidden_to_non_admin_users(): void
    {
        $patient = User::factory()->create();
        $patient->peran()->attach(Peran::where('kode', 'pasien')->value('id'));

        foreach ([
            'admin.obat.index',
            'admin.informasi-klinik.index',
            'admin.pelayanan.index',
            'admin.laporan.index',
        ] as $routeName) {
            $this->actingAs($patient)->get(route($routeName))->assertForbidden();
        }
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->peran()->attach(Peran::where('kode', 'admin')->value('id'));

        return $admin;
    }

    private function createDoctor(): int
    {
        $specialtyId = DB::table('spesialis')->insertGetId([
            'kode' => 'SP-OPS',
            'nama' => 'Spesialis Operasional',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('dokter')->insertGetId([
            'spesialis_id' => $specialtyId,
            'nomor_dokter' => 'DR-OPS',
            'nama_lengkap' => 'Dokter Operasional',
            'nomor_izin_praktik' => 'SIP-OPS',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function doctorName(int $doctorId): string
    {
        return (string) DB::table('dokter')->where('id', $doctorId)->value('nama_lengkap');
    }

    private function createPatient(): int
    {
        return DB::table('pasien')->insertGetId([
            'nomor_pasien' => 'PSN-OPS-001',
            'nama_lengkap' => 'Pasien Operasional',
            'email' => 'pasien.operasional@example.test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMedicine(): int
    {
        return DB::table('obat')->insertGetId([
            'kode' => 'OBAT-OPS',
            'nama' => 'Obat Operasional',
            'kategori' => 'Umum',
            'satuan' => 'tablet',
            'stok' => 10,
            'stok_minimum' => 2,
            'harga_satuan' => 1000,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
