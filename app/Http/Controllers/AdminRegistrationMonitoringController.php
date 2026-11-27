<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\JanjiTemu;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminRegistrationMonitoringController extends Controller
{
    public function index(): View
    {
        $validated = request()->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'doctor_id' => ['nullable', 'integer', 'exists:dokter,id'],
            'status' => ['nullable', 'in:terjadwal,dikonfirmasi,selesai,dibatalkan,tidak_hadir'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();

        $appointments = JanjiTemu::query()
            ->with(['pasien', 'dokter.jadwal'])
            ->whereBetween('tanggal_janji', [$startDate, $endDate])
            ->when($validated['doctor_id'] ?? null, function ($query, int $doctorId): void {
                $query->where('dokter_id', $doctorId);
            })
            ->when($validated['status'] ?? null, function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('pasien', function ($query) use ($search): void {
                    $query->where('nama_lengkap', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('tanggal_janji')
            ->orderByDesc('waktu_mulai')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $this->attachScheduleRanges($appointments->getCollection());

        return view('dashboard.admin.pemantauan.pendaftaran.index', [
            'appointments' => $appointments,
            'doctors' => $this->doctors(),
            'filters' => $validated,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'user' => request()->user(),
        ]);
    }

    public function show(int $appointment): View
    {
        $appointmentRecord = JanjiTemu::query()
            ->with(['pasien', 'dokter.jadwal'])
            ->findOrFail($appointment);
        $this->attachScheduleRange($appointmentRecord);

        return view('dashboard.admin.pemantauan.pendaftaran.show', [
            'appointment' => $appointmentRecord,
            'user' => request()->user(),
        ]);
    }

    /**
     * @param  Collection<int, JanjiTemu>  $appointments
     */
    private function attachScheduleRanges(Collection $appointments): void
    {
        foreach ($appointments as $appointment) {
            $this->attachScheduleRange($appointment);
        }
    }

    private function attachScheduleRange(JanjiTemu $appointment): void
    {
        $weekday = Carbon::parse($appointment->tanggal_janji)->dayOfWeek;
        $appointmentTime = substr((string) $appointment->waktu_mulai, 0, 8);
        $schedule = $appointment->dokter?->jadwal->first(function ($schedule) use ($weekday, $appointmentTime): bool {
            $startTime = substr((string) $schedule->waktu_mulai, 0, 8);
            $endTime = substr((string) $schedule->waktu_selesai, 0, 8);

            return (int) $schedule->hari_dalam_minggu === $weekday
                && $startTime <= $appointmentTime
                && $appointmentTime < $endTime;
        });

        $appointment->setAttribute(
            'jadwal_praktik',
            $schedule === null
                ? 'Jadwal tidak ditemukan'
                : substr((string) $schedule->waktu_mulai, 0, 5).' - '.substr((string) $schedule->waktu_selesai, 0, 5),
        );
    }

    private function doctors(): Collection
    {
        return Dokter::query()
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
    }
}
