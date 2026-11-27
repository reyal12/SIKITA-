<?php

namespace Database\Seeders;

use App\Models\Peran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClinicDemoSeeder extends Seeder
{
    private const DOCTOR_PASSWORD = 'Dokter123!';

    private const PATIENT_PASSWORD = 'Pasien123!';

    public function run(): void
    {
        DB::transaction(function (): void {
            $doctorRoleId = (int) Peran::query()->where('kode', 'dokter')->value('id');
            $patientRoleId = (int) Peran::query()->where('kode', 'pasien')->value('id');
            $adminId = (int) User::query()
                ->where('email', config('sikita.admin.email', 'admin@sikita.test'))
                ->value('id');
            $specialtyIds = DB::table('spesialis')->pluck('id', 'kode')->all();
            $doctorIds = $this->seedDoctors($doctorRoleId, $adminId, $specialtyIds);
            $patientIds = $this->seedPatients($patientRoleId, $adminId);
            $medicineIds = $this->seedMedicines();

            $this->seedSchedules($doctorIds);
            $this->seedAppointments($adminId, $doctorIds, $patientIds, $medicineIds);
        });
    }

    /**
     * @param  array<string, int>  $specialtyIds
     * @return list<int>
     */
    private function seedDoctors(int $doctorRoleId, int $adminId, array $specialtyIds): array
    {
        $doctors = [
            ['doctor_number' => 'DR-DEMO-001', 'name' => 'dr. Aditya Pratama', 'specialty' => 'SP-UMUM'],
            ['doctor_number' => 'DR-DEMO-002', 'name' => 'dr. Maya Lestari', 'specialty' => 'SP-ANAK'],
            ['doctor_number' => 'DR-DEMO-003', 'name' => 'dr. Bima Saputra', 'specialty' => 'SP-DALAM'],
            ['doctor_number' => 'DR-DEMO-004', 'name' => 'dr. Nisa Permata', 'specialty' => 'SP-KANDUNGAN'],
            ['doctor_number' => 'DR-DEMO-005', 'name' => 'drg. Rani Putri', 'specialty' => 'SP-GIGI'],
            ['doctor_number' => 'DR-DEMO-006', 'name' => 'dr. Fajar Nugraha', 'specialty' => 'SP-MATA'],
            ['doctor_number' => 'DR-DEMO-007', 'name' => 'dr. Sinta Maharani', 'specialty' => 'SP-UMUM'],
        ];
        $doctorIds = [];

        foreach ($doctors as $index => $doctor) {
            $email = sprintf('dokter%02d@sikita.test', $index + 1);
            $account = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $doctor['name'],
                    'password' => Hash::make(self::DOCTOR_PASSWORD),
                    'email_verified_at' => now(),
                ],
            );
            $account->peran()->syncWithoutDetaching([
                $doctorRoleId => ['ditetapkan_oleh' => $adminId ?: null],
            ]);

            DB::table('dokter')->upsert([
                [
                    'pengguna_id' => $account->id,
                    'spesialis_id' => $specialtyIds[$doctor['specialty']],
                    'nomor_dokter' => $doctor['doctor_number'],
                    'nama_lengkap' => $doctor['name'],
                    'nomor_izin_praktik' => sprintf('SIP-DEMO-%03d', $index + 1),
                    'telepon' => sprintf('081200000%03d', $index + 1),
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['nomor_dokter'], [
                'pengguna_id',
                'spesialis_id',
                'nama_lengkap',
                'nomor_izin_praktik',
                'telepon',
                'aktif',
                'updated_at',
            ]);

            $doctorIds[] = (int) DB::table('dokter')
                ->where('nomor_dokter', $doctor['doctor_number'])
                ->value('id');
        }

        return $doctorIds;
    }

    /**
     * @return list<int>
     */
    private function seedPatients(int $patientRoleId, int $adminId): array
    {
        $names = [
            'Aulia Rahmawati', 'Bagas Wicaksono', 'Citra Anggraini', 'Dimas Firmansyah',
            'Eka Safitri', 'Farhan Maulana', 'Gita Puspitasari', 'Hendra Kurniawan',
            'Intan Permatasari', 'Joko Susanto', 'Kartika Dewi', 'Lukman Hakim',
            'Melati Sari', 'Naufal Ramadhan', 'Olivia Maharani', 'Putra Wijaya',
            'Qori Azzahra', 'Rizky Hidayat', 'Salsabila Putri', 'Teguh Prakoso',
            'Umar Faruq', 'Vina Oktaviani', 'Wahyu Setiawan', 'Yuni Astuti',
        ];
        $genders = ['perempuan', 'laki-laki'];
        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $patientIds = [];

        foreach ($names as $index => $name) {
            $number = $index + 1;
            $userId = null;
            $email = sprintf('pasien%02d@sikita.test', $number);

            if ($number <= 6) {
                $account = User::query()->updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Hash::make(self::PATIENT_PASSWORD),
                        'email_verified_at' => now(),
                    ],
                );
                $account->peran()->syncWithoutDetaching([
                    $patientRoleId => ['ditetapkan_oleh' => $adminId ?: null],
                ]);
                $userId = $account->id;
            } else {
                $email = sprintf('pasien%02d@example.test', $number);
            }

            $patientNumber = sprintf('PSN-%04d', $number);
            DB::table('pasien')->upsert([
                [
                    'pengguna_id' => $userId,
                    'nomor_pasien' => $patientNumber,
                    'nomor_identitas' => sprintf('317400%010d', $number),
                    'nama_lengkap' => $name,
                    'tanggal_lahir' => today()->subYears(20 + ($index % 45))->subDays($index * 9)->toDateString(),
                    'jenis_kelamin' => $genders[$index % count($genders)],
                    'telepon' => sprintf('08130000%04d', $number),
                    'email' => $email,
                    'alamat' => sprintf('Jl. Kesehatan No. %d, Jakarta', $number),
                    'golongan_darah' => $bloodTypes[$index % count($bloodTypes)],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ],
            ], ['nomor_pasien'], [
                'pengguna_id',
                'nomor_identitas',
                'nama_lengkap',
                'tanggal_lahir',
                'jenis_kelamin',
                'telepon',
                'email',
                'alamat',
                'golongan_darah',
                'updated_at',
                'deleted_at',
            ]);

            $patientIds[] = (int) DB::table('pasien')
                ->where('nomor_pasien', $patientNumber)
                ->value('id');
        }

        return $patientIds;
    }

    /**
     * @return list<int>
     */
    private function seedMedicines(): array
    {
        $medicines = [
            ['OB-DEMO-001', 'Parasetamol 500 mg', 'Analgesik', 'tablet', 240, 40, 1500],
            ['OB-DEMO-002', 'Ibuprofen 200 mg', 'Analgesik', 'tablet', 180, 30, 2500],
            ['OB-DEMO-003', 'Amoksisilin 500 mg', 'Antibiotik', 'kapsul', 120, 25, 3500],
            ['OB-DEMO-004', 'Cetirizine 10 mg', 'Antihistamin', 'tablet', 160, 30, 2000],
            ['OB-DEMO-005', 'Antasida Doen', 'Pencernaan', 'tablet', 200, 35, 1000],
            ['OB-DEMO-006', 'Omeprazole 20 mg', 'Pencernaan', 'kapsul', 100, 20, 4000],
            ['OB-DEMO-007', 'Vitamin C 500 mg', 'Vitamin', 'tablet', 300, 50, 1200],
            ['OB-DEMO-008', 'Oralit', 'Rehidrasi', 'sachet', 90, 15, 2500],
            ['OB-DEMO-009', 'Salbutamol 2 mg', 'Respirasi', 'tablet', 80, 15, 1800],
            ['OB-DEMO-010', 'Ambroxol 30 mg', 'Respirasi', 'tablet', 110, 20, 2200],
            ['OB-DEMO-011', 'Metformin 500 mg', 'Antidiabetes', 'tablet', 150, 25, 1800],
            ['OB-DEMO-012', 'Amlodipine 5 mg', 'Kardiovaskular', 'tablet', 140, 25, 2200],
            ['OB-DEMO-013', 'Losartan 50 mg', 'Kardiovaskular', 'tablet', 90, 20, 4500],
            ['OB-DEMO-014', 'Simvastatin 10 mg', 'Kardiovaskular', 'tablet', 100, 20, 2500],
            ['OB-DEMO-015', 'Dexamethasone 0,5 mg', 'Antiinflamasi', 'tablet', 75, 15, 1200],
            ['OB-DEMO-016', 'Povidone Iodine 10%', 'Antiseptik', 'botol', 35, 8, 18000],
            ['OB-DEMO-017', 'Salep kulit 10 g', 'Dermatologi', 'tube', 40, 8, 12000],
            ['OB-DEMO-018', 'Tetes mata steril 10 ml', 'Oftalmologi', 'botol', 45, 10, 16000],
            ['OB-DEMO-019', 'Kasa steril 16 x 16 cm', 'Perawatan luka', 'pak', 60, 12, 7000],
            ['OB-DEMO-020', 'Masker medis', 'Alat kesehatan', 'pcs', 500, 100, 1000],
            ['OB-DEMO-021', 'Vitamin B kompleks', 'Vitamin', 'tablet', 150, 25, 1500],
            ['OB-DEMO-022', 'Zinc 20 mg', 'Suplemen', 'tablet', 95, 20, 2500],
            ['OB-DEMO-023', 'Loratadine 10 mg', 'Antihistamin', 'tablet', 100, 20, 2200],
            ['OB-DEMO-024', 'NaCl 0,9% 500 ml', 'Cairan medis', 'botol', 40, 10, 12000],
        ];

        foreach ($medicines as $medicine) {
            DB::table('obat')->upsert([
                [
                    'kode' => $medicine[0],
                    'nama' => $medicine[1],
                    'kategori' => $medicine[2],
                    'satuan' => $medicine[3],
                    'stok' => $medicine[4],
                    'stok_minimum' => $medicine[5],
                    'harga_satuan' => $medicine[6],
                    'tanggal_kedaluwarsa' => today()->addYears(2)->toDateString(),
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['kode'], [
                'nama',
                'kategori',
                'satuan',
                'stok',
                'stok_minimum',
                'harga_satuan',
                'tanggal_kedaluwarsa',
                'aktif',
                'updated_at',
            ]);
        }

        return DB::table('obat')
            ->whereIn('kode', array_column($medicines, 0))
            ->orderBy('kode')
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * @param  list<int>  $doctorIds
     */
    private function seedSchedules(array $doctorIds): void
    {
        foreach ($doctorIds as $doctorIndex => $doctorId) {
            $startTime = Carbon::createFromTime(8, 0)->addMinutes($doctorIndex * 30)->format('H:i:s');

            for ($weekday = 0; $weekday <= 6; $weekday++) {
                DB::table('jadwal_dokter')->upsert([
                    [
                        'dokter_id' => $doctorId,
                        'hari_dalam_minggu' => $weekday,
                        'waktu_mulai' => $startTime,
                        'waktu_selesai' => '17:00:00',
                        'durasi_janji_menit' => 30,
                        'maksimal_pasien' => 20,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ], ['dokter_id', 'hari_dalam_minggu', 'waktu_mulai'], [
                    'waktu_selesai',
                    'durasi_janji_menit',
                    'maksimal_pasien',
                    'updated_at',
                ]);
            }
        }
    }

    /**
     * @param  list<int>  $doctorIds
     * @param  list<int>  $patientIds
     * @param  list<int>  $medicineIds
     */
    private function seedAppointments(int $adminId, array $doctorIds, array $patientIds, array $medicineIds): void
    {
        $statuses = ['terjadwal', 'dikonfirmasi', 'selesai', 'dibatalkan', 'tidak_hadir', 'selesai'];
        $diagnoses = [
            'Infeksi saluran pernapasan ringan',
            'Dispepsia',
            'Hipertensi terkontrol',
            'Dermatitis ringan',
            'Kontrol kesehatan rutin',
            'Alergi musiman',
            'Nyeri otot ringan',
        ];
        $referenceTime = now('Asia/Jakarta');
        $appointmentSequence = 0;
        $recordSequence = 0;

        for ($dayOffset = -3; $dayOffset <= 3; $dayOffset++) {
            $appointmentDate = $referenceTime->copy()->addDays($dayOffset);

            for ($slot = 0; $slot < 6; $slot++) {
                $appointmentSequence++;
                $doctorId = $doctorIds[$slot];
                $patientId = $patientIds[($appointmentSequence - 1) % count($patientIds)];
                $status = $dayOffset > 0
                    ? ['terjadwal', 'dikonfirmasi', 'terjadwal', 'dibatalkan', 'terjadwal', 'dikonfirmasi'][$slot]
                    : $statuses[$slot];
                $startTime = Carbon::createFromTime(8, 0)
                    ->addMinutes($slot * 30)
                    ->format('H:i:s');
                $appointmentNumber = sprintf('JAN-DEMO-%03d', $appointmentSequence);
                $createdAt = $referenceTime->copy()->subDays(50 - $appointmentSequence);

                DB::table('janji_temu')->upsert([
                    [
                        'nomor_janji' => $appointmentNumber,
                        'pasien_id' => $patientId,
                        'dokter_id' => $doctorId,
                        'tanggal_janji' => $appointmentDate->toDateString(),
                        'nomor_antrean' => $slot + 1,
                        'waktu_mulai' => $startTime,
                        'status' => $status,
                        'keluhan_utama' => 'Pemeriksaan kesehatan dan konsultasi.',
                        'catatan' => null,
                        'dibuat_oleh' => $adminId ?: null,
                        'created_at' => $createdAt,
                        'updated_at' => now(),
                    ],
                ], ['nomor_janji'], [
                    'pasien_id',
                    'dokter_id',
                    'tanggal_janji',
                    'nomor_antrean',
                    'waktu_mulai',
                    'status',
                    'keluhan_utama',
                    'dibuat_oleh',
                    'updated_at',
                ]);

                if ($status === 'selesai' && $slot === 2) {
                    $recordSequence++;
                    $appointmentId = (int) DB::table('janji_temu')
                        ->where('nomor_janji', $appointmentNumber)
                        ->value('id');
                    $recordTime = $appointmentDate->copy()->setTimeFromTimeString($startTime)->addMinutes(30);
                    $medicalRecord = [
                        'janji_temu_id' => $appointmentId,
                        'pasien_id' => $patientId,
                        'dokter_id' => $doctorId,
                        'dicatat_pada' => $recordTime,
                        'keluhan_utama' => 'Keluhan ringan sejak beberapa hari terakhir.',
                        'diagnosis' => $diagnoses[($recordSequence - 1) % count($diagnoses)],
                        'rencana_perawatan' => 'Istirahat cukup, minum air, dan kontrol bila keluhan berlanjut.',
                        'tekanan_sistolik' => 110 + ($recordSequence % 4) * 5,
                        'tekanan_diastolik' => 70 + ($recordSequence % 3) * 5,
                        'denyut_jantung' => 72 + ($recordSequence % 8),
                        'suhu_tubuh' => 36.5 + ($recordSequence % 4) * 0.1,
                        'tinggi_badan_cm' => 155 + ($recordSequence % 20),
                        'berat_badan_kg' => 50 + ($recordSequence % 25),
                        'created_at' => $recordTime,
                        'updated_at' => now(),
                    ];

                    DB::table('rekam_medis')->upsert(
                        [$medicalRecord],
                        ['janji_temu_id'],
                        [
                            'pasien_id',
                            'dokter_id',
                            'dicatat_pada',
                            'keluhan_utama',
                            'diagnosis',
                            'rencana_perawatan',
                            'tekanan_sistolik',
                            'tekanan_diastolik',
                            'denyut_jantung',
                            'suhu_tubuh',
                            'tinggi_badan_cm',
                            'berat_badan_kg',
                            'updated_at',
                        ],
                    );

                    $medicalRecordId = (int) DB::table('rekam_medis')
                        ->where('janji_temu_id', $appointmentId)
                        ->value('id');
                    $prescriptionNumber = sprintf('RSP-DEMO-%03d', $recordSequence);
                    DB::table('resep')->upsert([
                        [
                            'rekam_medis_id' => $medicalRecordId,
                            'nomor_resep' => $prescriptionNumber,
                            'diresepkan_pada' => $recordTime,
                            'catatan' => 'Gunakan obat sesuai petunjuk tenaga kesehatan.',
                            'created_at' => $recordTime,
                            'updated_at' => now(),
                        ],
                    ], ['nomor_resep'], [
                        'rekam_medis_id',
                        'diresepkan_pada',
                        'catatan',
                        'updated_at',
                    ]);

                    $prescriptionId = (int) DB::table('resep')
                        ->where('nomor_resep', $prescriptionNumber)
                        ->value('id');

                    foreach (array_slice($medicineIds, ($recordSequence - 1) % (count($medicineIds) - 1), 2) as $medicineId) {
                        DB::table('detail_resep')->updateOrInsert(
                            [
                                'resep_id' => $prescriptionId,
                                'obat_id' => $medicineId,
                            ],
                            [
                                'dosis' => '1 tablet',
                                'frekuensi' => '2 kali sehari',
                                'durasi_hari' => 3,
                                'jumlah' => 6,
                                'petunjuk_pemakaian' => 'Diminum sesudah makan.',
                                'created_at' => $recordTime,
                                'updated_at' => now(),
                            ],
                        );
                    }
                }
            }
        }
    }
}
