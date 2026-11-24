<?php

namespace App\Http\Controllers;

use App\AppointmentAvailability;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentAvailability $appointmentAvailability) {}

    public function availableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:dokter,id'],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.now()->addDays(90)->toDateString(),
            ],
        ]);

        $doctorIsActive = DB::table('dokter')
            ->where('id', $validated['doctor_id'])
            ->where('aktif', true)
            ->exists();

        if (! $doctorIsActive) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Dokter yang dipilih tidak tersedia.',
            ]);
        }

        return response()->json([
            'slots' => $this->appointmentAvailability->availableTimes(
                (int) $validated['doctor_id'],
                $validated['appointment_date'],
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:dokter,id'],
            'appointment_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.now()->addDays(90)->toDateString(),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'chief_complaint' => ['nullable', 'string', 'max:2000'],
        ]);

        $appointmentNumber = DB::transaction(function () use ($request, $validated): string {
            DB::table('users')->where('id', $request->user()->id)->lockForUpdate()->first();

            $doctorIsActive = DB::table('dokter')
                ->where('id', $validated['doctor_id'])
                ->where('aktif', true)
                ->exists();

            if (! $doctorIsActive || ! in_array(
                $validated['start_time'],
                $this->appointmentAvailability->availableTimes(
                    (int) $validated['doctor_id'],
                    $validated['appointment_date'],
                    true,
                ),
                true,
            )) {
                throw ValidationException::withMessages([
                    'start_time' => 'Jadwal yang dipilih sudah tidak tersedia. Silakan pilih jadwal lain.',
                ]);
            }

            $patient = DB::table('pasien')
                ->where('pengguna_id', $request->user()->id)
                ->first();

            if ($patient === null) {
                $patientId = DB::table('pasien')->insertGetId([
                    'nomor_pasien' => 'PSN-'.Str::upper(Str::random(12)),
                    'nama_lengkap' => $request->user()->name,
                    'email' => $request->user()->email,
                    'pengguna_id' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $patientId = $patient->id;
            }

            $queueNumber = ((int) DB::table('janji_temu')
                ->where('dokter_id', $validated['doctor_id'])
                ->whereDate('tanggal_janji', $validated['appointment_date'])
                ->max('nomor_antrean')) + 1;
            $appointmentNumber = 'JAN-'.Str::upper(Str::random(12));

            DB::table('janji_temu')->insert([
                'nomor_janji' => $appointmentNumber,
                'pasien_id' => $patientId,
                'dokter_id' => $validated['doctor_id'],
                'tanggal_janji' => $validated['appointment_date'],
                'nomor_antrean' => $queueNumber,
                'waktu_mulai' => $validated['start_time'],
                'status' => 'terjadwal',
                'keluhan_utama' => $validated['chief_complaint'] ?? null,
                'dibuat_oleh' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $appointmentNumber;
        });

        return redirect()->route('pasien.dashboard')
            ->with('sukses', "Janji temu berhasil dibuat dengan nomor {$appointmentNumber}.");
    }

    public function cancel(Request $request, int $appointment): RedirectResponse
    {
        $patient = DB::table('pasien')
            ->where('pengguna_id', $request->user()->id)
            ->first();

        abort_if($patient === null, 404);

        $updated = DB::table('janji_temu')
            ->where('id', $appointment)
            ->where('pasien_id', $patient->id)
            ->where('status', 'terjadwal')
            ->update([
                'status' => 'dibatalkan',
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            abort(404);
        }

        return redirect()->route('pasien.dashboard')
            ->with('sukses', 'Janji temu berhasil dibatalkan.');
    }

    public function complete(Request $request, int $appointment): RedirectResponse
    {
        $query = DB::table('janji_temu')->where('id', $appointment);
        $doctor = DB::table('dokter')->where('pengguna_id', $request->user()->id)->first();

        if ($request->user()->punyaPeran('dokter') && ! $request->user()->punyaPeran('admin')) {
            abort_if($doctor === null, 404);
            $query->where('dokter_id', $doctor->id);
        }

        $updated = $query->where('status', 'terjadwal')->update([
            'status' => 'selesai',
            'updated_at' => now(),
        ]);

        if ($updated === 0) {
            abort(404);
        }

        return redirect()->route('dokter.dashboard')
            ->with('sukses', 'Status janji temu diperbarui menjadi selesai.');
    }
}
