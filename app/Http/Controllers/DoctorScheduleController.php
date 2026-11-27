<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DoctorScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $this->doctorForUser($request);

        return view('dashboard.dokter.jadwal-praktik', [
            'user' => $request->user(),
            'pageTitle' => 'Jadwal Praktik',
            'activePage' => 'jadwal-praktik',
            'doctor' => $doctor,
            'schedules' => DB::table('jadwal_dokter')
                ->where('dokter_id', $doctor->id)
                ->orderBy('hari_dalam_minggu')
                ->orderBy('waktu_mulai')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $doctor = $this->doctorForUser($request);
        $validated = $request->validate([
            'weekday_start' => ['required', 'integer', 'between:0,6'],
            'weekday_end' => ['nullable', 'integer', 'between:0,6'],
            ...$this->scheduleDetailRules(),
        ]);
        $weekdayStart = (int) $validated['weekday_start'];
        $weekdayEnd = isset($validated['weekday_end']) ? (int) $validated['weekday_end'] : $weekdayStart;
        $weekdays = [$weekdayStart];

        while (end($weekdays) !== $weekdayEnd) {
            $weekdays[] = ((int) end($weekdays) + 1) % 7;
        }

        $scheduleDetails = $this->scheduleAttributes($validated);

        foreach ($weekdays as $weekday) {
            $this->ensureNoOverlap($doctor->id, [
                'hari_dalam_minggu' => $weekday,
                ...$scheduleDetails,
            ]);
        }

        DB::transaction(function () use ($doctor, $weekdays, $scheduleDetails): void {
            foreach ($weekdays as $weekday) {
                DB::table('jadwal_dokter')->insert([
                    'dokter_id' => $doctor->id,
                    'hari_dalam_minggu' => $weekday,
                    ...$scheduleDetails,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('dokter.jadwal-praktik.index')
            ->with('sukses', 'Jadwal praktik berhasil ditambahkan untuk '.count($weekdays).' hari.');
    }

    public function update(Request $request, int $schedule): RedirectResponse
    {
        $doctor = $this->doctorForUser($request);
        abort_unless(DB::table('jadwal_dokter')
            ->where('id', $schedule)
            ->where('dokter_id', $doctor->id)
            ->exists(), 404);

        $validated = $request->validate([
            'weekday' => ['required', 'integer', 'between:0,6'],
            ...$this->scheduleDetailRules(),
        ]);
        $scheduleAttributes = [
            'hari_dalam_minggu' => (int) $validated['weekday'],
            ...$this->scheduleAttributes($validated),
        ];
        $this->ensureNoOverlap($doctor->id, $scheduleAttributes, $schedule);

        DB::table('jadwal_dokter')
            ->where('id', $schedule)
            ->update([
                ...$scheduleAttributes,
                'updated_at' => now(),
            ]);

        return redirect()->route('dokter.jadwal-praktik.index')
            ->with('sukses', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroy(Request $request, int $schedule): RedirectResponse
    {
        $doctor = $this->doctorForUser($request);
        $deleted = DB::table('jadwal_dokter')
            ->where('id', $schedule)
            ->where('dokter_id', $doctor->id)
            ->delete();

        abort_if($deleted === 0, 404);

        return redirect()->route('dokter.jadwal-praktik.index')
            ->with('sukses', 'Jadwal praktik berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function scheduleDetailRules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'duration' => ['required', 'integer', 'between:5,240'],
            'max_patients' => ['required', 'integer', 'between:1,100'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     waktu_mulai:string,
     *     waktu_selesai:string,
     *     durasi_janji_menit:int,
     *     maksimal_pasien:int
     * }
     */
    private function scheduleAttributes(array $validated): array
    {
        return [
            'waktu_mulai' => $validated['start_time'].':00',
            'waktu_selesai' => $validated['end_time'].':00',
            'durasi_janji_menit' => (int) $validated['duration'],
            'maksimal_pasien' => (int) $validated['max_patients'],
        ];
    }

    /**
     * @param  array<string, int|string>  $schedule
     */
    private function ensureNoOverlap(int $doctorId, array $schedule, ?int $exceptScheduleId = null): void
    {
        $overlaps = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctorId)
            ->where('hari_dalam_minggu', $schedule['hari_dalam_minggu'])
            ->when($exceptScheduleId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->where('waktu_mulai', '<', $schedule['waktu_selesai'])
            ->where('waktu_selesai', '>', $schedule['waktu_mulai'])
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_time' => 'Jadwal ini beririsan dengan jadwal praktik yang sudah ada.',
            ]);
        }
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
