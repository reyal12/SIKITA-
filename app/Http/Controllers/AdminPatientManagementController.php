<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StorePatientRequest;
use App\Http\Requests\Admin\UpdatePatientRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminPatientManagementController extends Controller
{
    public function index(): View
    {
        $filters = request()->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $patients = DB::table('pasien')
            ->whereNull('deleted_at')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nama_lengkap', 'like', '%'.$search.'%')
                        ->orWhere('nomor_identitas', 'like', '%'.$search.'%')
                        ->orWhere('nomor_pasien', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.admin.pasien.index', [
            'patients' => $patients,
            'filters' => $filters,
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.pasien.form', [
            'patient' => null,
            'user' => request()->user(),
        ]);
    }

    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $patientId = DB::transaction(function () use ($validated): int {
            $providedNumber = $validated['nomor_pasien'] ?? null;
            $patientId = DB::table('pasien')->insertGetId([
                ...$this->patientAttributes($validated),
                'nomor_pasien' => $providedNumber ?: 'TMP-'.Str::random(20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! $providedNumber) {
                DB::table('pasien')
                    ->where('id', $patientId)
                    ->update(['nomor_pasien' => $this->generatePatientNumber((int) $patientId)]);
            }

            return (int) $patientId;
        });

        return redirect()->route('admin.pasien.show', $patientId)
            ->with('sukses', 'Data pasien berhasil ditambahkan.');
    }

    public function show(int $patient): View
    {
        $patientRecord = $this->findPatient($patient);

        $appointments = DB::table('janji_temu as j')
            ->leftJoin('dokter as d', 'd.id', '=', 'j.dokter_id')
            ->where('j.pasien_id', $patient)
            ->orderByDesc('j.tanggal_janji')
            ->orderByDesc('j.waktu_mulai')
            ->get([
                'j.nomor_janji',
                'j.tanggal_janji',
                'j.waktu_mulai',
                'j.status',
                'j.keluhan_utama',
                'd.nama_lengkap as nama_dokter',
            ]);

        $medicalRecords = DB::table('rekam_medis as r')
            ->leftJoin('dokter as d', 'd.id', '=', 'r.dokter_id')
            ->where('r.pasien_id', $patient)
            ->orderByDesc('r.dicatat_pada')
            ->get([
                'r.dicatat_pada',
                'r.keluhan_utama',
                'r.diagnosis',
                'r.rencana_perawatan',
                'r.tekanan_sistolik',
                'r.tekanan_diastolik',
                'r.denyut_jantung',
                'r.suhu_tubuh',
                'd.nama_lengkap as nama_dokter',
            ]);

        return view('dashboard.admin.pasien.show', [
            'patient' => $patientRecord,
            'appointments' => $appointments,
            'medicalRecords' => $medicalRecords,
            'user' => request()->user(),
        ]);
    }

    public function edit(int $patient): View
    {
        return view('dashboard.admin.pasien.form', [
            'patient' => $this->findPatient($patient),
            'user' => request()->user(),
        ]);
    }

    public function update(UpdatePatientRequest $request, int $patient): RedirectResponse
    {
        $patientRecord = $this->findPatient($patient);

        DB::table('pasien')
            ->where('id', $patientRecord->id)
            ->whereNull('deleted_at')
            ->update($this->patientAttributes($request->validated()) + [
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.pasien.show', $patient)
            ->with('sukses', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(int $patient): RedirectResponse
    {
        $patientRecord = $this->findPatient($patient);

        $hasAppointments = DB::table('janji_temu')->where('pasien_id', $patient)->exists();
        $hasMedicalRecords = DB::table('rekam_medis')->where('pasien_id', $patient)->exists();
        $hasPayments = DB::table('pembayaran')->where('pasien_id', $patient)->exists();

        if ($hasAppointments || $hasMedicalRecords || $hasPayments) {
            throw ValidationException::withMessages([
                'patient' => 'Pasien memiliki riwayat janji temu, pemeriksaan, atau pembayaran sehingga tidak dapat dihapus. Data riwayat tetap dipertahankan.',
            ]);
        }

        DB::table('pasien')
            ->where('id', $patientRecord->id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.pasien.index')
            ->with('sukses', 'Data pasien berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function patientAttributes(array $validated): array
    {
        return [
            'nomor_identitas' => $validated['nomor_identitas'] ?? null,
            'nama_lengkap' => $validated['nama_lengkap'],
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'email' => $validated['email'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'golongan_darah' => $validated['golongan_darah'] ?? null,
        ];
    }

    private function findPatient(int $patientId): object
    {
        $patient = DB::table('pasien')
            ->where('id', $patientId)
            ->whereNull('deleted_at')
            ->first();

        abort_if($patient === null, 404);

        return $patient;
    }

    private function generatePatientNumber(int $patientId): string
    {
        $sequence = $patientId;

        do {
            $number = 'PSN-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table('pasien')->where('nomor_pasien', $number)->exists());

        return $number;
    }
}
