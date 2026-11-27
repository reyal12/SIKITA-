<?php

namespace Tests\Feature;

use App\Models\Peran;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function test_patient_appointment_page_shows_each_doctors_consecutive_practice_days_as_ranges(): void
    {
        $patient = $this->createUserWithRole('pasien');
        $doctorId = $this->createDoctor();
        $otherDoctorId = $this->createDoctor();
        DB::table('dokter')->where('id', $doctorId)->update(['nama_lengkap' => 'Dokter Sasa']);
        DB::table('dokter')->where('id', $otherDoctorId)->update(['nama_lengkap' => 'Dokter Vivian']);

        foreach ([0, 1, 2] as $weekday) {
            DB::table('jadwal_dokter')->insert([
                'dokter_id' => $doctorId,
                'hari_dalam_minggu' => $weekday,
                'waktu_mulai' => '09:00:00',
                'waktu_selesai' => '12:00:00',
                'durasi_janji_menit' => 15,
                'maksimal_pasien' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('jadwal_dokter')->insert([
            'dokter_id' => $otherDoctorId,
            'hari_dalam_minggu' => 4,
            'waktu_mulai' => '10:00:00',
            'waktu_selesai' => '13:00:00',
            'durasi_janji_menit' => 15,
            'maksimal_pasien' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($patient)
            ->get(route('pasien.janji-temu'))
            ->assertOk()
            ->assertSee('Dokter Sasa')
            ->assertSee('Minggu–Selasa')
            ->assertSee('09:00–12:00')
            ->assertSee('Dokter Vivian')
            ->assertSee('Kamis')
            ->assertSee('10:00–13:00');
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
            ->assertDontSee('<span>Profil</span>', false)
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

    public function test_admin_payment_mutation_routes_are_removed(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->actingAs($admin)
            ->post('/admin/pembayaran')
            ->assertNotFound();
        $this->actingAs($admin)
            ->patch('/admin/pembayaran/1')
            ->assertNotFound();
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Riwayat pembayaran')
            ->assertDontSee('Catat tagihan');
        $this->assertDatabaseCount('pembayaran', 0);
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
            ->assertSee('class="patient-app-shell"', false)
            ->assertSee('class="patient-topbar"', false)
            ->assertSee('Janji Temu Pasien')
            ->assertSee('href="'.route('dokter.profil').'"', false)
            ->assertDontSee('<span>Profil</span>', false)
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

    public function test_doctor_can_manage_their_own_practice_schedule(): void
    {
        $doctorUser = $this->createUserWithRole('dokter');
        $doctorId = $this->createDoctor($doctorUser);

        $this->actingAs($doctorUser)
            ->get(route('dokter.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('dokter.jadwal-praktik.index').'"', false)
            ->assertDontSee('Tambah jadwal baru')
            ->assertDontSee('Atur waktu layanan');

        $this->actingAs($doctorUser)
            ->get(route('dokter.jadwal-praktik.index'))
            ->assertOk()
            ->assertSee('Jadwal Praktik')
            ->assertSee('Tambah jadwal baru')
            ->assertSee('Jadwal tersedia')
            ->assertSee('name="weekday_start"', false)
            ->assertSee('name="weekday_end"', false);

        $this->actingAs($doctorUser)
            ->post(route('dokter.jadwal-praktik.store'), [
                'weekday_start' => 0,
                'weekday_end' => 2,
                'start_time' => '09:00',
                'end_time' => '12:00',
                'duration' => 15,
                'max_patients' => 20,
            ])
            ->assertRedirect(route('dokter.jadwal-praktik.index'));

        $scheduleWeekdays = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctorId)
            ->orderBy('hari_dalam_minggu')
            ->pluck('hari_dalam_minggu')
            ->all();
        $this->assertSame([0, 1, 2], $scheduleWeekdays);

        $this->actingAs($doctorUser)
            ->post(route('dokter.jadwal-praktik.store'), [
                'weekday_start' => 2,
                'weekday_end' => 4,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'duration' => 15,
                'max_patients' => 20,
            ])
            ->assertSessionHasErrors('start_time');
        $this->assertSame(3, DB::table('jadwal_dokter')->where('dokter_id', $doctorId)->count());

        $this->actingAs($doctorUser)
            ->post(route('dokter.jadwal-praktik.store'), [
                'weekday_start' => 4,
                'start_time' => '14:00',
                'end_time' => '15:00',
                'duration' => 15,
                'max_patients' => 20,
            ])
            ->assertRedirect(route('dokter.jadwal-praktik.index'));
        $this->assertDatabaseHas('jadwal_dokter', [
            'dokter_id' => $doctorId,
            'hari_dalam_minggu' => 4,
            'waktu_mulai' => '14:00:00',
            'waktu_selesai' => '15:00:00',
        ]);
        $this->assertSame(4, DB::table('jadwal_dokter')->where('dokter_id', $doctorId)->count());

        $schedule = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctorId)
            ->where('hari_dalam_minggu', 0)
            ->first();
        $this->assertNotNull($schedule);
        $this->assertSame(0, $schedule->hari_dalam_minggu);
        $this->assertSame('09:00:00', $schedule->waktu_mulai);

        $this->actingAs($doctorUser)
            ->get(route('dokter.jadwal-praktik.index'))
            ->assertOk()
            ->assertSee('09:00–12:00');

        $this->actingAs($doctorUser)
            ->patch(route('dokter.jadwal-praktik.update', $schedule->id), [
                'weekday' => 0,
                'start_time' => '13:00',
                'end_time' => '16:00',
                'duration' => 30,
                'max_patients' => 10,
            ])
            ->assertRedirect(route('dokter.jadwal-praktik.index'));

        $this->assertDatabaseHas('jadwal_dokter', [
            'id' => $schedule->id,
            'hari_dalam_minggu' => 0,
            'waktu_mulai' => '13:00:00',
            'waktu_selesai' => '16:00:00',
            'durasi_janji_menit' => 30,
            'maksimal_pasien' => 10,
        ]);

        $this->actingAs($doctorUser)
            ->delete(route('dokter.jadwal-praktik.destroy', $schedule->id))
            ->assertRedirect(route('dokter.jadwal-praktik.index'));

        $this->assertDatabaseMissing('jadwal_dokter', ['id' => $schedule->id]);

        $singleDayScheduleId = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctorId)
            ->where('hari_dalam_minggu', 4)
            ->value('id');
        $this->actingAs($doctorUser)
            ->delete(route('dokter.jadwal-praktik.destroy', $singleDayScheduleId))
            ->assertRedirect(route('dokter.jadwal-praktik.index'));
        $this->assertDatabaseMissing('jadwal_dokter', ['id' => $singleDayScheduleId]);

        $otherDoctorUser = $this->createUserWithRole('dokter');
        $otherDoctorId = $this->createDoctor($otherDoctorUser);
        $otherScheduleId = DB::table('jadwal_dokter')->insertGetId([
            'dokter_id' => $otherDoctorId,
            'hari_dalam_minggu' => 2,
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '12:00:00',
            'durasi_janji_menit' => 15,
            'maksimal_pasien' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($doctorUser)
            ->delete(route('dokter.jadwal-praktik.destroy', $otherScheduleId))
            ->assertNotFound();
    }

    public function test_doctor_can_update_their_profile_and_upload_or_remove_a_photo(): void
    {
        Storage::fake('public');

        $doctorUser = $this->createUserWithRole('dokter');
        $doctorId = $this->createDoctor($doctorUser);

        $this->actingAs($doctorUser)
            ->get(route('dokter.profil'))
            ->assertOk()
            ->assertSee('Profil Dokter')
            ->assertSee('Simpan Profil');

        $this->actingAs($doctorUser)
            ->post(route('dokter.profil.update'), [
                'name' => 'Dokter Profil Baru',
                'phone' => '08123456789',
                'photo' => UploadedFile::fake()->image('doctor.png'),
            ])
            ->assertRedirect(route('dokter.profil'));

        $photoPath = DB::table('dokter')->where('id', $doctorId)->value('foto_profil');
        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);
        $this->assertDatabaseHas('dokter', [
            'id' => $doctorId,
            'nama_lengkap' => 'Dokter Profil Baru',
            'telepon' => '08123456789',
            'foto_profil' => $photoPath,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $doctorUser->id,
            'name' => 'Dokter Profil Baru',
        ]);

        $this->actingAs($doctorUser)
            ->post(route('dokter.profil.update'), [
                'name' => 'Dokter Profil Baru',
                'phone' => '08123456789',
                'remove_photo' => true,
            ])
            ->assertRedirect(route('dokter.profil'));

        $this->assertDatabaseHas('dokter', [
            'id' => $doctorId,
            'foto_profil' => null,
        ]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_doctor_can_save_an_examination_and_prescription_for_their_appointment(): void
    {
        $doctorUser = $this->createUserWithRole('dokter');
        $doctorId = $this->createDoctor($doctorUser);
        $patientId = $this->createPatientRecord($this->createUserWithRole('pasien'));
        $appointmentId = $this->createAppointment(
            $patientId,
            $doctorId,
            now()->toDateString(),
            '09:00:00',
        );
        $medicineId = DB::table('obat')->insertGetId([
            'kode' => 'OBT-EXAM',
            'nama' => 'Obat Pemeriksaan',
            'satuan' => 'tablet',
            'stok' => 50,
            'stok_minimum' => 5,
            'harga_satuan' => 2000,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($doctorUser)
            ->get(route('dokter.pasien.index'))
            ->assertOk()
            ->assertSee('Daftar Pasien')
            ->assertDontSee(DB::table('pasien')->where('id', $patientId)->value('nama_lengkap'));

        $this->actingAs($doctorUser)
            ->get(route('dokter.pemeriksaan.create', $appointmentId))
            ->assertOk()
            ->assertSee('Tanda vital')
            ->assertSee('Resep obat');

        $this->actingAs($doctorUser)
            ->post(route('dokter.pemeriksaan.store', $appointmentId), [
                'chief_complaint' => 'Demam',
                'systolic_blood_pressure' => 120,
                'diastolic_blood_pressure' => 80,
                'heart_rate' => 72,
                'temperature' => 37.5,
                'height_cm' => 165,
                'weight_kg' => 60,
                'diagnosis' => 'Demam ringan',
                'treatment_plan' => 'Istirahat cukup',
                'notes' => 'Kontrol bila tidak membaik',
                'prescriptions' => [
                    [
                        'medicine_id' => $medicineId,
                        'dosage' => '500 mg',
                        'frequency' => '3 kali sehari',
                        'duration_days' => 3,
                        'quantity' => 9,
                        'instructions' => 'Sesudah makan',
                    ],
                ],
            ])
            ->assertRedirect(route('dokter.pasien.history', $patientId));

        $record = DB::table('rekam_medis')->where('janji_temu_id', $appointmentId)->first();
        $this->assertNotNull($record);
        $this->assertSame('Demam ringan', $record->diagnosis);
        $this->assertSame('Kontrol bila tidak membaik', $record->catatan);
        $this->assertSame(120, $record->tekanan_sistolik);
        $this->assertDatabaseHas('janji_temu', [
            'id' => $appointmentId,
            'status' => 'selesai',
        ]);
        $this->assertDatabaseHas('detail_resep', [
            'obat_id' => $medicineId,
            'dosis' => '500 mg',
            'frekuensi' => '3 kali sehari',
            'durasi_hari' => 3,
            'jumlah' => 9,
            'petunjuk_pemakaian' => 'Sesudah makan',
        ]);

        $this->actingAs($doctorUser)
            ->get(route('dokter.pasien.index'))
            ->assertOk()
            ->assertSee(DB::table('pasien')->where('id', $patientId)->value('nama_lengkap'));

        $this->actingAs($doctorUser)
            ->get(route('dokter.pasien.history', $patientId))
            ->assertOk()
            ->assertSee('Demam ringan')
            ->assertSee('Obat Pemeriksaan')
            ->assertSee('Sesudah makan');

        $otherDoctorUser = $this->createUserWithRole('dokter');
        $this->createDoctor($otherDoctorUser);
        $this->actingAs($otherDoctorUser)
            ->get(route('dokter.pasien.index'))
            ->assertOk()
            ->assertDontSee(DB::table('pasien')->where('id', $patientId)->value('nama_lengkap'));
        $this->actingAs($otherDoctorUser)
            ->get(route('dokter.pasien.history', $patientId))
            ->assertNotFound();

        $this->actingAs($doctorUser)
            ->post(route('dokter.pemeriksaan.store', $appointmentId), [
                'diagnosis' => 'Diagnosis duplikat',
            ])
            ->assertNotFound();
    }

    public function test_doctor_and_admin_can_manage_medicine_inventory_but_used_medicine_cannot_be_deleted(): void
    {
        $doctor = $this->createUserWithRole('dokter');
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)->get(route('obat.index'))->assertOk();

        $this->actingAs($doctor)
            ->post(route('obat.store'), [
                'code' => 'OBT-CRUD-1',
                'name' => 'Obat CRUD',
                'category' => 'Analgesik',
                'unit' => 'tablet',
                'stock' => 25,
                'minimum_stock' => 5,
                'unit_price' => 1500,
                'expiration_date' => '2027-12-31',
                'is_active' => 1,
            ])
            ->assertRedirect(route('obat.index'));

        $medicine = DB::table('obat')->where('kode', 'OBT-CRUD-1')->first();
        $this->assertNotNull($medicine);

        $this->actingAs($doctor)
            ->get(route('obat.index'))
            ->assertOk()
            ->assertSee('<details class="doctor-medicine-card" data-medicine-item', false)
            ->assertSee('Obat CRUD')
            ->assertSee('Lihat informasi')
            ->assertSee('id="medicine-search"', false)
            ->assertSee('data-search="Obat CRUD OBT-CRUD-1 Analgesik tablet"', false)
            ->assertSee('Harga satuan (Rp)');

        $this->actingAs($doctor)
            ->patch(route('obat.update', $medicine->id), [
                'code' => 'OBT-CRUD-1',
                'name' => 'Obat CRUD Baru',
                'category' => 'Analgesik',
                'unit' => 'tablet',
                'stock' => 30,
                'minimum_stock' => 8,
                'unit_price' => 2500,
                'expiration_date' => '2028-06-30',
                'is_active' => 1,
            ])
            ->assertRedirect(route('obat.index'));

        $this->assertDatabaseHas('obat', [
            'id' => $medicine->id,
            'nama' => 'Obat CRUD Baru',
            'stok' => 30,
            'harga_satuan' => 2500,
            'tanggal_kedaluwarsa' => '2028-06-30',
        ]);

        $doctorId = $this->createDoctor($doctor);
        $patientId = $this->createPatientRecord($this->createUserWithRole('pasien'));
        $recordId = DB::table('rekam_medis')->insertGetId([
            'pasien_id' => $patientId,
            'dokter_id' => $doctorId,
            'dicatat_pada' => now(),
            'diagnosis' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $prescriptionId = DB::table('resep')->insertGetId([
            'rekam_medis_id' => $recordId,
            'nomor_resep' => 'RSP-DELETE-TEST',
            'diresepkan_pada' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_resep')->insert([
            'resep_id' => $prescriptionId,
            'obat_id' => $medicine->id,
            'dosis' => '1 tablet',
            'frekuensi' => '1x',
            'jumlah' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($doctor)
            ->delete(route('obat.destroy', $medicine->id))
            ->assertSessionHasErrors('medicine');
        $this->assertDatabaseHas('obat', ['id' => $medicine->id]);

        $otherMedicineId = DB::table('obat')->insertGetId([
            'kode' => 'OBT-CRUD-2',
            'nama' => 'Obat Hapus',
            'satuan' => 'botol',
            'stok' => 0,
            'stok_minimum' => 0,
            'harga_satuan' => 0,
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($doctor)
            ->delete(route('obat.destroy', $otherMedicineId))
            ->assertRedirect(route('obat.index'));
        $this->assertDatabaseMissing('obat', ['id' => $otherMedicineId]);
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
