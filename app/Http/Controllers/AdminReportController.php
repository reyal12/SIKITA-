<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date', 'before_or_equal:today'],
        ]);
        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $appointmentsByStatus = DB::table('janji_temu')
            ->whereBetween('tanggal_janji', [$startDate, $endDate])
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.admin.laporan.index', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPatients' => DB::table('pasien')
                ->whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->count(),
            'totalAppointments' => $appointmentsByStatus->sum(),
            'appointmentStatuses' => $appointmentsByStatus,
            'totalServices' => DB::table('rekam_medis')
                ->whereBetween('dicatat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->count(),
            'totalPrescriptions' => DB::table('resep')
                ->whereBetween('diresepkan_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->count(),
            'totalRevenue' => DB::table('pembayaran')
                ->where('status', 'dibayar')
                ->whereBetween('dibayar_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
                ->sum('jumlah'),
            'doctorSummary' => $this->doctorSummary($startDate, $endDate),
            'user' => $request->user(),
        ]);
    }

    /**
     * @return Collection<int, object>
     */
    private function doctorSummary(string $startDate, string $endDate): Collection
    {
        $appointments = DB::table('janji_temu as j')
            ->join('dokter as d', 'd.id', '=', 'j.dokter_id')
            ->whereBetween('j.tanggal_janji', [$startDate, $endDate])
            ->select('d.id', 'd.nama_lengkap')
            ->selectRaw('COUNT(j.id) as jumlah_janji')
            ->selectRaw("SUM(CASE WHEN j.status = 'selesai' THEN 1 ELSE 0 END) as janji_selesai")
            ->groupBy('d.id', 'd.nama_lengkap')
            ->get()
            ->keyBy('id');

        $medicalRecords = DB::table('rekam_medis')
            ->whereBetween('dicatat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->select('dokter_id')
            ->selectRaw('COUNT(*) as jumlah_pemeriksaan')
            ->groupBy('dokter_id')
            ->pluck('jumlah_pemeriksaan', 'dokter_id');

        return $appointments->map(function (object $doctor) use ($medicalRecords): object {
            $doctor->jumlah_pemeriksaan = (int) ($medicalRecords[$doctor->id] ?? 0);

            return $doctor;
        })->values();
    }
}
