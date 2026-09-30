<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = [
            'specialties' => 'spesialis',
            'patients' => 'pasien',
            'doctors' => 'dokter',
            'doctor_schedules' => 'jadwal_dokter',
            'appointments' => 'janji_temu',
            'medical_records' => 'rekam_medis',
            'medicines' => 'obat',
            'prescriptions' => 'resep',
            'prescription_items' => 'detail_resep',
            'payments' => 'pembayaran',
            'roles' => 'peran',
            'role_user' => 'peran_pengguna',
        ];

        foreach ($tableNames as $oldName => $newName) {
            Schema::rename($oldName, $newName);
        }

        $this->renameColumns([
            'spesialis' => [
                'code' => 'kode',
                'name' => 'nama',
                'description' => 'deskripsi',
            ],
            'pasien' => [
                'patient_number' => 'nomor_pasien',
                'national_id' => 'nomor_identitas',
                'full_name' => 'nama_lengkap',
                'birth_date' => 'tanggal_lahir',
                'gender' => 'jenis_kelamin',
                'phone' => 'telepon',
                'address' => 'alamat',
                'blood_type' => 'golongan_darah',
                'user_id' => 'pengguna_id',
            ],
            'dokter' => [
                'user_id' => 'pengguna_id',
                'specialty_id' => 'spesialis_id',
                'doctor_number' => 'nomor_dokter',
                'full_name' => 'nama_lengkap',
                'license_number' => 'nomor_izin_praktik',
                'phone' => 'telepon',
                'is_active' => 'aktif',
            ],
            'jadwal_dokter' => [
                'doctor_id' => 'dokter_id',
                'weekday' => 'hari_dalam_minggu',
                'start_time' => 'waktu_mulai',
                'end_time' => 'waktu_selesai',
                'appointment_duration' => 'durasi_janji_menit',
                'max_patients' => 'maksimal_pasien',
            ],
            'janji_temu' => [
                'appointment_number' => 'nomor_janji',
                'patient_id' => 'pasien_id',
                'doctor_id' => 'dokter_id',
                'appointment_date' => 'tanggal_janji',
                'queue_number' => 'nomor_antrean',
                'start_time' => 'waktu_mulai',
                'chief_complaint' => 'keluhan_utama',
                'notes' => 'catatan',
                'created_by' => 'dibuat_oleh',
            ],
            'rekam_medis' => [
                'appointment_id' => 'janji_temu_id',
                'patient_id' => 'pasien_id',
                'doctor_id' => 'dokter_id',
                'recorded_at' => 'dicatat_pada',
                'chief_complaint' => 'keluhan_utama',
                'treatment_plan' => 'rencana_perawatan',
                'systolic_blood_pressure' => 'tekanan_sistolik',
                'diastolic_blood_pressure' => 'tekanan_diastolik',
                'heart_rate' => 'denyut_jantung',
                'temperature' => 'suhu_tubuh',
                'height_cm' => 'tinggi_badan_cm',
                'weight_kg' => 'berat_badan_kg',
            ],
            'obat' => [
                'code' => 'kode',
                'name' => 'nama',
                'category' => 'kategori',
                'unit' => 'satuan',
                'stock' => 'stok',
                'minimum_stock' => 'stok_minimum',
                'unit_price' => 'harga_satuan',
                'expiration_date' => 'tanggal_kedaluwarsa',
                'is_active' => 'aktif',
            ],
            'resep' => [
                'medical_record_id' => 'rekam_medis_id',
                'prescription_number' => 'nomor_resep',
                'prescribed_at' => 'diresepkan_pada',
                'notes' => 'catatan',
            ],
            'detail_resep' => [
                'prescription_id' => 'resep_id',
                'medicine_id' => 'obat_id',
                'dosage' => 'dosis',
                'frequency' => 'frekuensi',
                'duration_days' => 'durasi_hari',
                'quantity' => 'jumlah',
                'instructions' => 'petunjuk_pemakaian',
            ],
            'pembayaran' => [
                'payment_number' => 'nomor_pembayaran',
                'patient_id' => 'pasien_id',
                'appointment_id' => 'janji_temu_id',
                'created_by' => 'dibuat_oleh',
                'amount' => 'jumlah',
                'payment_method' => 'metode_pembayaran',
                'paid_at' => 'dibayar_pada',
                'notes' => 'catatan',
            ],
            'peran' => [
                'slug' => 'kode',
                'name' => 'nama',
                'description' => 'deskripsi',
            ],
            'peran_pengguna' => [
                'role_id' => 'peran_id',
                'user_id' => 'pengguna_id',
                'assigned_by' => 'ditetapkan_oleh',
            ],
        ]);

        DB::table('peran')->where('kode', 'admin')->update(['kode' => 'administrator']);
        DB::table('peran')->where('kode', 'doctor')->update(['kode' => 'dokter']);
        DB::table('peran')->where('kode', 'patient')->update(['kode' => 'pasien']);

        $this->translateValues('pasien', 'jenis_kelamin', [
            'male' => 'laki-laki',
            'female' => 'perempuan',
            'other' => 'lainnya',
        ]);
        $this->translateValues('janji_temu', 'status', [
            'scheduled' => 'terjadwal',
            'confirmed' => 'dikonfirmasi',
            'completed' => 'selesai',
            'cancelled' => 'dibatalkan',
            'no_show' => 'tidak_hadir',
        ]);
        $this->translateValues('pembayaran', 'status', [
            'pending' => 'menunggu',
            'paid' => 'dibayar',
            'failed' => 'gagal',
            'refunded' => 'dikembalikan',
        ]);
        $this->translateValues('pembayaran', 'metode_pembayaran', [
            'cash' => 'tunai',
            'bank_transfer' => 'transfer_bank',
            'card' => 'kartu',
            'e_wallet' => 'dompet_digital',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->translateValues('pasien', 'jenis_kelamin', [
            'laki-laki' => 'male',
            'perempuan' => 'female',
            'lainnya' => 'other',
        ]);
        $this->translateValues('janji_temu', 'status', [
            'terjadwal' => 'scheduled',
            'dikonfirmasi' => 'confirmed',
            'selesai' => 'completed',
            'dibatalkan' => 'cancelled',
            'tidak_hadir' => 'no_show',
        ]);
        $this->translateValues('pembayaran', 'status', [
            'menunggu' => 'pending',
            'dibayar' => 'paid',
            'gagal' => 'failed',
            'dikembalikan' => 'refunded',
        ]);
        $this->translateValues('pembayaran', 'metode_pembayaran', [
            'tunai' => 'cash',
            'transfer_bank' => 'bank_transfer',
            'kartu' => 'card',
            'dompet_digital' => 'e_wallet',
        ]);

        DB::table('peran')->where('kode', 'administrator')->update(['kode' => 'admin']);
        DB::table('peran')->where('kode', 'dokter')->update(['kode' => 'doctor']);
        DB::table('peran')->where('kode', 'pasien')->update(['kode' => 'patient']);

        $this->renameColumns([
            'peran_pengguna' => [
                'peran_id' => 'role_id',
                'pengguna_id' => 'user_id',
                'ditetapkan_oleh' => 'assigned_by',
            ],
            'peran' => [
                'kode' => 'slug',
                'nama' => 'name',
                'deskripsi' => 'description',
            ],
            'pembayaran' => [
                'nomor_pembayaran' => 'payment_number',
                'pasien_id' => 'patient_id',
                'janji_temu_id' => 'appointment_id',
                'dibuat_oleh' => 'created_by',
                'jumlah' => 'amount',
                'metode_pembayaran' => 'payment_method',
                'dibayar_pada' => 'paid_at',
                'catatan' => 'notes',
            ],
            'detail_resep' => [
                'resep_id' => 'prescription_id',
                'obat_id' => 'medicine_id',
                'dosis' => 'dosage',
                'frekuensi' => 'frequency',
                'durasi_hari' => 'duration_days',
                'jumlah' => 'quantity',
                'petunjuk_pemakaian' => 'instructions',
            ],
            'resep' => [
                'rekam_medis_id' => 'medical_record_id',
                'nomor_resep' => 'prescription_number',
                'diresepkan_pada' => 'prescribed_at',
                'catatan' => 'notes',
            ],
            'obat' => [
                'kode' => 'code',
                'nama' => 'name',
                'kategori' => 'category',
                'satuan' => 'unit',
                'stok' => 'stock',
                'stok_minimum' => 'minimum_stock',
                'harga_satuan' => 'unit_price',
                'tanggal_kedaluwarsa' => 'expiration_date',
                'aktif' => 'is_active',
            ],
            'rekam_medis' => [
                'janji_temu_id' => 'appointment_id',
                'pasien_id' => 'patient_id',
                'dokter_id' => 'doctor_id',
                'dicatat_pada' => 'recorded_at',
                'keluhan_utama' => 'chief_complaint',
                'rencana_perawatan' => 'treatment_plan',
                'tekanan_sistolik' => 'systolic_blood_pressure',
                'tekanan_diastolik' => 'diastolic_blood_pressure',
                'denyut_jantung' => 'heart_rate',
                'suhu_tubuh' => 'temperature',
                'tinggi_badan_cm' => 'height_cm',
                'berat_badan_kg' => 'weight_kg',
            ],
            'janji_temu' => [
                'nomor_janji' => 'appointment_number',
                'pasien_id' => 'patient_id',
                'dokter_id' => 'doctor_id',
                'tanggal_janji' => 'appointment_date',
                'nomor_antrean' => 'queue_number',
                'waktu_mulai' => 'start_time',
                'keluhan_utama' => 'chief_complaint',
                'catatan' => 'notes',
                'dibuat_oleh' => 'created_by',
            ],
            'jadwal_dokter' => [
                'dokter_id' => 'doctor_id',
                'hari_dalam_minggu' => 'weekday',
                'waktu_mulai' => 'start_time',
                'waktu_selesai' => 'end_time',
                'durasi_janji_menit' => 'appointment_duration',
                'maksimal_pasien' => 'max_patients',
            ],
            'dokter' => [
                'pengguna_id' => 'user_id',
                'spesialis_id' => 'specialty_id',
                'nomor_dokter' => 'doctor_number',
                'nama_lengkap' => 'full_name',
                'nomor_izin_praktik' => 'license_number',
                'telepon' => 'phone',
                'aktif' => 'is_active',
            ],
            'pasien' => [
                'nomor_pasien' => 'patient_number',
                'nomor_identitas' => 'national_id',
                'nama_lengkap' => 'full_name',
                'tanggal_lahir' => 'birth_date',
                'jenis_kelamin' => 'gender',
                'telepon' => 'phone',
                'alamat' => 'address',
                'golongan_darah' => 'blood_type',
                'pengguna_id' => 'user_id',
            ],
            'spesialis' => [
                'kode' => 'code',
                'nama' => 'name',
                'deskripsi' => 'description',
            ],
        ]);

        $tableNames = [
            'spesialis' => 'specialties',
            'pasien' => 'patients',
            'dokter' => 'doctors',
            'jadwal_dokter' => 'doctor_schedules',
            'janji_temu' => 'appointments',
            'rekam_medis' => 'medical_records',
            'obat' => 'medicines',
            'resep' => 'prescriptions',
            'detail_resep' => 'prescription_items',
            'pembayaran' => 'payments',
            'peran' => 'roles',
            'peran_pengguna' => 'role_user',
        ];

        foreach ($tableNames as $oldName => $newName) {
            Schema::rename($oldName, $newName);
        }
    }

    /**
     * @param  array<string, array<string, string>>  $columnsByTable
     */
    private function renameColumns(array $columnsByTable): void
    {
        foreach ($columnsByTable as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $oldName => $newName) {
                    $table->renameColumn($oldName, $newName);
                }
            });
        }
    }

    /**
     * @param  array<string, string>  $values
     */
    private function translateValues(string $tableName, string $columnName, array $values): void
    {
        foreach ($values as $oldValue => $newValue) {
            DB::table($tableName)
                ->where($columnName, $oldValue)
                ->update([$columnName => $newValue]);
        }
    }
};
