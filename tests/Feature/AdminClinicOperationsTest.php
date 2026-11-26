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

        $this->assertDatabaseCount('spesialis', 6);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('spesialis', 6);
        $this->assertDatabaseHas('spesialis', [
            'kode' => 'SP-UMUM',
            'nama' => 'Dokter Umum',
        ]);
        $this->assertDatabaseHas('spesialis', [
            'kode' => 'SP-GIGI',
            'nama' => 'Kesehatan Gigi',
        ]);

        $this->seed(SpecialtySeeder::class);

        $this->assertDatabaseCount('spesialis', 6);
    }

    public function test_demo_database_seeder_is_repeatable_and_populates_clinic_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            'spesialis' => 6,
            'dokter' => 7,
            'jadwal_dokter' => 49,
            'pasien' => 24,
            'obat' => 24,
            'janji_temu' => 42,
            'rekam_medis' => 4,
            'resep' => 4,
            'detail_resep' => 8,
        ] as $table => $count) {
            $this->assertDatabaseCount($table, $count);
        }

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 14);
        $this->assertDatabaseCount('pasien', 24);
        $this->assertDatabaseCount('dokter', 7);
        $this->assertDatabaseCount('janji_temu', 42);
        $this->assertDatabaseCount('rekam_medis', 4);
        $this->assertDatabaseHas('users', ['email' => 'admin@sikita.test']);
        $todayInJakarta = now('Asia/Jakarta')->toDateString();
        $this->assertDatabaseHas('janji_temu', ['tanggal_janji' => $todayInJakarta]);
        $this->assertSame(
            ['dibatalkan', 'dikonfirmasi', 'selesai', 'terjadwal', 'tidak_hadir'],
            DB::table('janji_temu')
                ->whereDate('tanggal_janji', $todayInJakarta)
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->all(),
        );
        $this->assertSame(7, DB::table('janji_temu')->distinct()->count('tanggal_janji'));
    }

    public function test_admin_can_only_search_and_view_medicines(): void
    {
        $admin = $this->createAdmin();
        $medicineId = DB::table('obat')->insertGetId([
            'kode' => 'OBAT-001',
            'nama' => 'Parasetamol',
            'kategori' => 'Analgesik',
            'satuan' => 'tablet',
            'stok' => 12,
            'stok_minimum' => 5,
            'harga_satuan' => 15000,
            'tanggal_kedaluwarsa' => now()->addYear()->toDateString(),
            'aktif' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.obat.index'))
            ->assertOk()
            ->assertSee('Parasetamol')
            ->assertSee('12')
            ->assertSee('Rp 15.000')
            ->assertSee('tablet')
            ->assertDontSee('Tambah obat')
            ->assertDontSee('Edit')
            ->assertDontSee('Hapus');

        $this->actingAs($admin)
            ->get(route('admin.obat.index', ['search' => 'Parasetamol']))
            ->assertOk()
            ->assertSee('Parasetamol');

        $this->actingAs($admin)
            ->get(route('admin.obat.index', ['search' => 'Tidak ditemukan']))
            ->assertOk()
            ->assertSee('Belum ada obat yang sesuai pencarian.');

        $this->actingAs($admin)->get('/admin/obat/create')->assertNotFound();
        $this->actingAs($admin)->get('/admin/obat/'.$medicineId.'/edit')->assertNotFound();
        $this->actingAs($admin)->post('/admin/obat')->assertMethodNotAllowed();
        $this->actingAs($admin)->put('/admin/obat/'.$medicineId)->assertNotFound();
        $this->actingAs($admin)->delete('/admin/obat/'.$medicineId)->assertNotFound();

        $this->assertDatabaseHas('obat', [
            'id' => $medicineId,
            'stok' => 12,
            'harga_satuan' => 15000,
        ]);
    }

    public function test_removed_admin_sections_have_no_routes(): void
    {
        $admin = $this->createAdmin();

        foreach ([
            ['GET', '/admin/pendaftaran'],
            ['GET', '/admin/spesialis'],
            ['GET', '/admin/informasi-klinik'],
            ['GET', '/admin/pelayanan'],
            ['GET', '/admin/laporan'],
            ['GET', '/admin/pembayaran'],
            ['GET', '/admin/riwayat-pembayaran'],
            ['GET', '/admin/catat-tagihan'],
            ['POST', '/admin/pembayaran'],
        ] as [$method, $uri]) {
            $this->actingAs($admin)->call($method, $uri)->assertNotFound();
        }
    }

    public function test_admin_can_monitor_completed_care_read_only(): void
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
            ->get(route('admin.pemantauan.pelayanan.index', $period + ['search' => 'Pasien Operasional']))
            ->assertOk()
            ->assertSee('Pasien Operasional')
            ->assertSee('Infeksi saluran pernapasan')
            ->assertSee('Obat Operasional');

    }

    public function test_new_admin_sections_are_forbidden_to_non_admin_users(): void
    {
        $patient = User::factory()->create();
        $patient->peran()->attach(Peran::where('kode', 'pasien')->value('id'));

        foreach ([
            'admin.obat.index',
            'admin.pemantauan.pendaftaran.index',
            'admin.pemantauan.pelayanan.index',
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
