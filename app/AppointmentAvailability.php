<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentAvailability
{
    /**
     * @return list<string>
     */
    public function availableTimes(
        int $doctorId,
        string $date,
        bool $lockSchedules = false,
        ?int $exceptAppointmentId = null,
    ): array {
        $schedulesQuery = DB::table('jadwal_dokter')
            ->where('dokter_id', $doctorId)
            ->where('hari_dalam_minggu', Carbon::parse($date)->dayOfWeek)
            ->orderBy('waktu_mulai');

        if ($lockSchedules) {
            $schedulesQuery->lockForUpdate();
        }

        $schedules = $schedulesQuery->get();
        $appointmentsQuery = DB::table('janji_temu')
            ->where('dokter_id', $doctorId)
            ->whereDate('tanggal_janji', $date)
            ->where('status', 'terjadwal');

        if ($exceptAppointmentId !== null) {
            $appointmentsQuery->where('id', '!=', $exceptAppointmentId);
        }

        $appointments = $appointmentsQuery->get(['waktu_mulai']);
        $availableTimes = [];

        foreach ($schedules as $schedule) {
            $scheduleAppointments = $appointments->filter(function (object $appointment) use ($schedule): bool {
                $appointmentTime = substr($appointment->waktu_mulai, 0, 5);

                return $appointmentTime >= substr($schedule->waktu_mulai, 0, 5)
                    && $appointmentTime < substr($schedule->waktu_selesai, 0, 5);
            });

            if ($scheduleAppointments->count() >= $schedule->maksimal_pasien) {
                continue;
            }

            $start = Carbon::createFromFormat('H:i:s', $schedule->waktu_mulai);
            $end = Carbon::createFromFormat('H:i:s', $schedule->waktu_selesai);

            while ($start->copy()->addMinutes($schedule->durasi_janji_menit)->lessThanOrEqualTo($end)) {
                $time = $start->format('H:i');

                if (Carbon::parse($date.' '.$time)->isFuture() && ! $appointments->contains(
                    fn (object $appointment): bool => substr($appointment->waktu_mulai, 0, 5) === $time,
                )) {
                    $availableTimes[] = $time;
                }

                $start->addMinutes($schedule->durasi_janji_menit);
            }
        }

        return array_values(array_unique($availableTimes));
    }
}
