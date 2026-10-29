<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(Request $request): View
    {
        return view('dashboard.admin', [
            'user' => $request->user(),
            'stats' => [
                'appointments_today' => DB::table('janji_temu')
                    ->whereDate('tanggal_janji', today())
                    ->count(),
                'appointments_scheduled' => DB::table('janji_temu')
                    ->where('status', 'terjadwal')
                    ->count(),
                'patients' => DB::table('pasien')->count(),
                'unpaid_payments' => DB::table('pembayaran')
                    ->where('status', 'menunggu')
                    ->count(),
            ],
            'appointments' => DB::table('janji_temu as j')
                ->join('pasien as p', 'p.id', '=', 'j.pasien_id')
                ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
                ->select([
                    'j.id',
                    'j.nomor_janji',
                    'j.nomor_antrean',
                    'j.tanggal_janji',
                    'j.waktu_mulai',
                    'j.status',
                    'j.keluhan_utama',
                    'p.nama_lengkap as nama_pasien',
                    'd.nama_lengkap as nama_dokter',
                ])
                ->orderByDesc('j.tanggal_janji')
                ->orderBy('j.waktu_mulai')
                ->limit(100)
                ->get(),
            'patients' => DB::table('pasien')
                ->orderBy('nama_lengkap')
                ->get(['id', 'nama_lengkap', 'nomor_pasien']),
            'paymentAppointments' => DB::table('janji_temu')
                ->join('pasien', 'pasien.id', '=', 'janji_temu.pasien_id')
                ->orderByDesc('tanggal_janji')
                ->get([
                    'janji_temu.id',
                    'janji_temu.pasien_id',
                    'janji_temu.nomor_janji',
                    'janji_temu.tanggal_janji',
                    'pasien.nama_lengkap as nama_pasien',
                ]),
            'payments' => DB::table('pembayaran as b')
                ->join('pasien as p', 'p.id', '=', 'b.pasien_id')
                ->leftJoin('janji_temu as j', 'j.id', '=', 'b.janji_temu_id')
                ->select([
                    'b.id',
                    'b.nomor_pembayaran',
                    'b.jumlah',
                    'b.metode_pembayaran',
                    'b.status',
                    'b.created_at',
                    'p.nama_lengkap as nama_pasien',
                    'j.nomor_janji',
                ])
                ->orderByDesc('b.created_at')
                ->limit(100)
                ->get(),
        ]);
    }

    public function dokter(Request $request): View
    {
        $doctor = DB::table('dokter')->where('pengguna_id', $request->user()->id)->first();
        $appointments = collect();

        if ($doctor !== null) {
            $appointments = DB::table('janji_temu as j')
                ->join('pasien as p', 'p.id', '=', 'j.pasien_id')
                ->where('j.dokter_id', $doctor->id)
                ->select([
                    'j.id',
                    'j.nomor_janji',
                    'j.nomor_antrean',
                    'j.tanggal_janji',
                    'j.waktu_mulai',
                    'j.status',
                    'j.keluhan_utama',
                    'p.nama_lengkap as nama_pasien',
                    'p.nomor_pasien',
                ])
                ->orderByDesc('j.tanggal_janji')
                ->orderBy('j.waktu_mulai')
                ->limit(100)
                ->get();
        }

        return view('dashboard.dokter', [
            'user' => $request->user(),
            'doctor' => $doctor,
            'appointments' => $appointments,
        ]);
    }

    public function pasien(Request $request): View
    {
        $patient = $this->patientForUser($request->user()->id);
        $appointments = $patient ? $this->patientAppointments($patient->id) : collect();
        $medicalRecords = $patient ? $this->patientMedicalRecords($patient->id) : collect();
        $prescriptions = $patient ? $this->patientPrescriptions($patient->id) : collect();
        $payments = $patient ? $this->patientPayments($patient->id) : collect();
        $medicalRecordsByAppointment = $medicalRecords->keyBy('janji_temu_id');
        $prescriptionsByMedicalRecord = $prescriptions->flatten(1)->groupBy('medical_record_id');

        return view('dashboard.pasien', [
            'user' => $request->user(),
            'pageTitle' => 'Dashboard Pasien',
            'activePage' => 'dashboard',
            'patient' => $patient,
            'activeAppointment' => $appointments
                ->filter(fn (object $appointment): bool => $appointment->status === 'terjadwal'
                    && $appointment->tanggal_janji >= today()->toDateString())
                ->sortBy(fn (object $appointment): string => $appointment->tanggal_janji.' '.$appointment->waktu_mulai)
                ->first(),
            'journeyAppointments' => $appointments->take(5),
            'medicalRecordsByAppointment' => $medicalRecordsByAppointment,
            'prescriptionsByMedicalRecord' => $prescriptionsByMedicalRecord,
            'unpaidPayment' => $payments->firstWhere('status', 'menunggu'),
            'latestPayment' => $payments->first(),
        ]);
    }

    public function janjiTemu(Request $request): View
    {
        $patient = $this->patientForUser($request->user()->id);

        return view('dashboard.pasien.janji-temu', [
            'user' => $request->user(),
            'pageTitle' => 'Janji Temu',
            'activePage' => 'janji-temu',
            'patient' => $patient,
            'appointments' => $patient ? $this->patientAppointments($patient->id) : collect(),
            'doctors' => DB::table('dokter as d')
                ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
                ->where('d.aktif', true)
                ->orderBy('d.nama_lengkap')
                ->get([
                    'd.id',
                    'd.nama_lengkap',
                    's.nama as nama_spesialis',
                ]),
            'doctorSchedules' => $this->doctorSchedules(),
        ]);
    }

    public function riwayatMedis(Request $request): View
    {
        $patient = $this->patientForUser($request->user()->id);

        return view('dashboard.pasien.riwayat-medis', [
            'user' => $request->user(),
            'pageTitle' => 'Riwayat Medis',
            'activePage' => 'riwayat-medis',
            'medicalRecords' => $patient ? $this->patientMedicalRecords($patient->id) : collect(),
            'prescriptions' => $patient ? $this->patientPrescriptions($patient->id) : collect(),
            'appointments' => $patient ? $this->patientAppointments($patient->id) : collect(),
        ]);
    }

    public function pembayaran(Request $request): View
    {
        $patient = $this->patientForUser($request->user()->id);

        return view('dashboard.pasien.pembayaran', [
            'user' => $request->user(),
            'pageTitle' => 'Pembayaran',
            'activePage' => 'pembayaran',
            'payments' => $patient ? $this->patientPayments($patient->id) : collect(),
        ]);
    }

    public function profil(Request $request): View
    {
        return view('dashboard.pasien.profil', [
            'user' => $request->user(),
            'pageTitle' => 'Profil Pasien',
            'activePage' => 'profil',
            'patient' => $this->patientForUser($request->user()->id),
        ]);
    }

    private function patientForUser(int $userId): ?object
    {
        return DB::table('pasien')->where('pengguna_id', $userId)->first();
    }

    private function patientAppointments(int $patientId): Collection
    {
        return DB::table('janji_temu as j')
            ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
            ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
            ->where('j.pasien_id', $patientId)
            ->select([
                'j.id',
                'j.nomor_janji',
                'j.nomor_antrean',
                'j.tanggal_janji',
                'j.waktu_mulai',
                'j.status',
                'j.keluhan_utama',
                'd.nama_lengkap as nama_dokter',
                's.nama as nama_spesialis',
            ])
            ->orderByDesc('j.tanggal_janji')
            ->orderByDesc('j.waktu_mulai')
            ->get();
    }

    private function patientMedicalRecords(int $patientId): Collection
    {
        return DB::table('rekam_medis as r')
            ->join('dokter as d', 'd.id', '=', 'r.dokter_id')
            ->where('r.pasien_id', $patientId)
            ->select([
                'r.id',
                'r.janji_temu_id',
                'r.dicatat_pada',
                'r.keluhan_utama',
                'r.diagnosis',
                'r.rencana_perawatan',
                'r.tekanan_sistolik',
                'r.tekanan_diastolik',
                'r.denyut_jantung',
                'r.suhu_tubuh',
                'd.nama_lengkap as nama_dokter',
            ])
            ->orderByDesc('r.dicatat_pada')
            ->get();
    }

    private function patientPrescriptions(int $patientId): Collection
    {
        return DB::table('resep as r')
            ->join('rekam_medis as m', 'm.id', '=', 'r.rekam_medis_id')
            ->join('detail_resep as i', 'i.resep_id', '=', 'r.id')
            ->join('obat as o', 'o.id', '=', 'i.obat_id')
            ->where('m.pasien_id', $patientId)
            ->select([
                'r.id as resep_id',
                'r.rekam_medis_id as medical_record_id',
                'r.nomor_resep',
                'r.diresepkan_pada',
                'r.catatan',
                'o.nama as nama_obat',
                'i.dosis',
                'i.frekuensi',
                'i.durasi_hari',
                'i.jumlah',
                'i.petunjuk_pemakaian',
            ])
            ->orderByDesc('r.diresepkan_pada')
            ->get()
            ->groupBy('resep_id');
    }

    private function patientPayments(int $patientId): Collection
    {
        return DB::table('pembayaran')
            ->where('pasien_id', $patientId)
            ->orderByDesc('created_at')
            ->get();
    }

    private function doctorSchedules(): Collection
    {
        return DB::table('jadwal_dokter as j')
            ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
            ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
            ->where('d.aktif', true)
            ->orderBy('j.hari_dalam_minggu')
            ->orderBy('j.waktu_mulai')
            ->get([
                'j.hari_dalam_minggu',
                'j.waktu_mulai',
                'j.waktu_selesai',
                'd.nama_lengkap as nama_dokter',
                's.nama as nama_spesialis',
            ]);
    }
}
