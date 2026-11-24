<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DoctorPatientController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->doctorForUser($request);

        return view('dashboard.dokter.pasien', [
            'user' => $request->user(),
            'pageTitle' => 'Daftar Pasien',
            'activePage' => 'pasien',
            'doctor' => $doctor,
            'patients' => DB::table('pasien as p')
                ->join('rekam_medis as r', 'r.pasien_id', '=', 'p.id')
                ->whereNull('p.deleted_at')
                ->where('r.dokter_id', $doctor->id)
                ->select([
                    'p.id',
                    'p.nomor_pasien',
                    'p.nama_lengkap',
                    'p.email',
                    'p.telepon',
                    DB::raw('COUNT(r.id) as jumlah_pemeriksaan'),
                    DB::raw('MAX(r.dicatat_pada) as pemeriksaan_terakhir'),
                ])
                ->groupBy('p.id', 'p.nomor_pasien', 'p.nama_lengkap', 'p.email', 'p.telepon')
                ->orderBy('p.nama_lengkap')
                ->get(),
        ]);
    }

    public function history(Request $request, int $patient): View
    {
        $doctor = $this->doctorForUser($request);
        $patientRecord = DB::table('pasien')->where('id', $patient)->whereNull('deleted_at')->first();
        abort_if($patientRecord === null, 404);
        abort_unless(
            DB::table('rekam_medis')
                ->where('pasien_id', $patientRecord->id)
                ->where('dokter_id', $doctor->id)
                ->exists(),
            404
        );

        $records = DB::table('rekam_medis as r')
            ->join('dokter as d', 'd.id', '=', 'r.dokter_id')
            ->leftJoin('janji_temu as j', 'j.id', '=', 'r.janji_temu_id')
            ->where('r.pasien_id', $patientRecord->id)
            ->orderByDesc('r.dicatat_pada')
            ->get([
                'r.id',
                'r.janji_temu_id',
                'r.dicatat_pada',
                'r.keluhan_utama',
                'r.diagnosis',
                'r.rencana_perawatan',
                'r.catatan',
                'r.tekanan_sistolik',
                'r.tekanan_diastolik',
                'r.denyut_jantung',
                'r.suhu_tubuh',
                'r.tinggi_badan_cm',
                'r.berat_badan_kg',
                'd.nama_lengkap as nama_dokter',
                'j.nomor_janji',
            ]);

        $prescriptions = $this->prescriptionsForRecords($records);

        return view('dashboard.dokter.riwayat-pasien', [
            'user' => $request->user(),
            'pageTitle' => 'Riwayat Pemeriksaan',
            'activePage' => 'pasien',
            'doctor' => $doctor,
            'patient' => $patientRecord,
            'records' => $records,
            'prescriptions' => $prescriptions,
        ]);
    }

    public function examination(Request $request, int $appointment): View
    {
        $doctor = $this->doctorForUser($request);
        $appointmentData = DB::table('janji_temu as j')
            ->join('pasien as p', 'p.id', '=', 'j.pasien_id')
            ->where('j.id', $appointment)
            ->where('j.dokter_id', $doctor->id)
            ->first([
                'j.id',
                'j.nomor_janji',
                'j.tanggal_janji',
                'j.waktu_mulai',
                'j.status',
                'j.keluhan_utama',
                'p.id as pasien_id',
                'p.nama_lengkap as nama_pasien',
                'p.nomor_pasien',
            ]);
        abort_if($appointmentData === null || $appointmentData->status === 'dibatalkan', 404);

        $record = DB::table('rekam_medis')->where('janji_temu_id', $appointmentData->id)->first();

        return view('dashboard.dokter.pemeriksaan', [
            'user' => $request->user(),
            'pageTitle' => 'Form Pemeriksaan',
            'activePage' => 'pasien',
            'doctor' => $doctor,
            'appointment' => $appointmentData,
            'record' => $record,
            'medicines' => DB::table('obat')
                ->where('aktif', true)
                ->orderBy('nama')
                ->get(['id', 'kode', 'nama', 'satuan', 'stok']),
        ]);
    }

    public function storeExamination(Request $request, int $appointment): RedirectResponse
    {
        $doctor = $this->doctorForUser($request);
        $prescriptions = collect($request->input('prescriptions', []))
            ->filter(fn (mixed $item): bool => is_array($item) && filled($item['medicine_id'] ?? null))
            ->values()
            ->all();
        $request->merge(['prescriptions' => $prescriptions]);

        $validated = $request->validate([
            'chief_complaint' => ['nullable', 'string', 'max:2000'],
            'systolic_blood_pressure' => ['nullable', 'integer', 'between:30,300'],
            'diastolic_blood_pressure' => ['nullable', 'integer', 'between:20,200'],
            'heart_rate' => ['nullable', 'integer', 'between:20,250'],
            'temperature' => ['nullable', 'numeric', 'between:30,45'],
            'height_cm' => ['nullable', 'numeric', 'between:20,250'],
            'weight_kg' => ['nullable', 'numeric', 'between:1,500'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'treatment_plan' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'prescriptions' => ['nullable', 'array', 'max:10'],
            'prescriptions.*.medicine_id' => ['required', 'integer', 'exists:obat,id'],
            'prescriptions.*.dosage' => ['required', 'string', 'max:100'],
            'prescriptions.*.frequency' => ['required', 'string', 'max:100'],
            'prescriptions.*.duration_days' => ['nullable', 'integer', 'between:1,365'],
            'prescriptions.*.quantity' => ['required', 'integer', 'between:1,1000'],
            'prescriptions.*.instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $patientId = DB::transaction(function () use ($appointment, $doctor, $validated): int {
            $appointmentData = DB::table('janji_temu')
                ->where('id', $appointment)
                ->where('dokter_id', $doctor->id)
                ->lockForUpdate()
                ->first();

            abort_if(
                $appointmentData === null
                    || $appointmentData->status === 'dibatalkan'
                    || DB::table('rekam_medis')->where('janji_temu_id', $appointment)->exists(),
                404
            );

            $medicineIds = collect($validated['prescriptions'] ?? [])
                ->pluck('medicine_id')
                ->unique();
            $activeMedicineCount = DB::table('obat')
                ->whereIn('id', $medicineIds)
                ->where('aktif', true)
                ->count();

            if ($activeMedicineCount !== $medicineIds->count()) {
                throw ValidationException::withMessages([
                    'prescriptions' => 'Obat yang dipilih tidak aktif atau tidak tersedia.',
                ]);
            }

            $recordId = DB::table('rekam_medis')->insertGetId([
                'janji_temu_id' => $appointment,
                'pasien_id' => $appointmentData->pasien_id,
                'dokter_id' => $doctor->id,
                'dicatat_pada' => now(),
                'keluhan_utama' => $validated['chief_complaint'] ?? null,
                'diagnosis' => $validated['diagnosis'],
                'rencana_perawatan' => $validated['treatment_plan'] ?? null,
                'catatan' => $validated['notes'] ?? null,
                'tekanan_sistolik' => $validated['systolic_blood_pressure'] ?? null,
                'tekanan_diastolik' => $validated['diastolic_blood_pressure'] ?? null,
                'denyut_jantung' => $validated['heart_rate'] ?? null,
                'suhu_tubuh' => $validated['temperature'] ?? null,
                'tinggi_badan_cm' => $validated['height_cm'] ?? null,
                'berat_badan_kg' => $validated['weight_kg'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! empty($validated['prescriptions'])) {
                $prescriptionId = DB::table('resep')->insertGetId([
                    'rekam_medis_id' => $recordId,
                    'nomor_resep' => 'RSP-'.Str::upper(Str::random(12)),
                    'diresepkan_pada' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($validated['prescriptions'] as $item) {
                    DB::table('detail_resep')->insert([
                        'resep_id' => $prescriptionId,
                        'obat_id' => $item['medicine_id'],
                        'dosis' => $item['dosage'],
                        'frekuensi' => $item['frequency'],
                        'durasi_hari' => $item['duration_days'] ?? null,
                        'jumlah' => $item['quantity'],
                        'petunjuk_pemakaian' => $item['instructions'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('janji_temu')
                ->where('id', $appointment)
                ->update([
                    'status' => 'selesai',
                    'updated_at' => now(),
                ]);

            return (int) $appointmentData->pasien_id;
        });

        return redirect()->route('dokter.pasien.history', $patientId)
            ->with('sukses', 'Pemeriksaan dan resep berhasil disimpan.');
    }

    /**
     * @param  Collection<int, object>  $records
     * @return Collection<int, Collection<int, object>>
     */
    private function prescriptionsForRecords(Collection $records): Collection
    {
        if ($records->isEmpty()) {
            return collect();
        }

        return DB::table('resep as r')
            ->join('detail_resep as i', 'i.resep_id', '=', 'r.id')
            ->join('obat as o', 'o.id', '=', 'i.obat_id')
            ->whereIn('r.rekam_medis_id', $records->pluck('id'))
            ->orderBy('r.diresepkan_pada')
            ->get([
                'r.rekam_medis_id',
                'r.nomor_resep',
                'r.diresepkan_pada',
                'o.nama as nama_obat',
                'o.satuan',
                'i.dosis',
                'i.frekuensi',
                'i.durasi_hari',
                'i.jumlah',
                'i.petunjuk_pemakaian',
            ])
            ->groupBy('rekam_medis_id');
    }

    private function doctorForUser(Request $request): object
    {
        $doctor = DB::table('dokter')
            ->where('pengguna_id', $request->user()->id)
            ->first();

        abort_if($doctor === null, 404);

        return $doctor;
    }
}
