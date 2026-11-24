<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminServiceMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();

        $records = DB::table('rekam_medis as r')
            ->join('pasien as p', 'p.id', '=', 'r.pasien_id')
            ->join('dokter as d', 'd.id', '=', 'r.dokter_id')
            ->leftJoin('janji_temu as j', 'j.id', '=', 'r.janji_temu_id')
            ->whereBetween('r.dicatat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('p.nama_lengkap', 'like', '%'.$search.'%')
                        ->orWhere('p.nomor_pasien', 'like', '%'.$search.'%')
                        ->orWhere('d.nama_lengkap', 'like', '%'.$search.'%')
                        ->orWhere('r.diagnosis', 'like', '%'.$search.'%');
                });
            })
            ->select([
                'r.id',
                'r.janji_temu_id',
                'r.dicatat_pada',
                'r.keluhan_utama',
                'r.diagnosis',
                'r.rencana_perawatan',
                'p.nama_lengkap as nama_pasien',
                'p.nomor_pasien',
                'd.nama_lengkap as nama_dokter',
                'j.nomor_janji',
            ])
            ->orderByDesc('r.dicatat_pada')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.admin.pelayanan.index', [
            'records' => $records,
            'prescriptionsByRecord' => $this->prescriptionsForRecords($records->getCollection()),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $validated['search'] ?? '',
            'user' => $request->user(),
        ]);
    }

    /**
     * @param  Collection<int, object>  $records
     * @return Collection<int, Collection<int, object>>
     */
    private function prescriptionsForRecords(Collection $records): Collection
    {
        $recordIds = $records->pluck('id');

        if ($recordIds->isEmpty()) {
            return collect();
        }

        return DB::table('resep as r')
            ->join('detail_resep as i', 'i.resep_id', '=', 'r.id')
            ->join('obat as o', 'o.id', '=', 'i.obat_id')
            ->whereIn('r.rekam_medis_id', $recordIds)
            ->orderBy('r.diresepkan_pada')
            ->get([
                'r.rekam_medis_id',
                'r.nomor_resep',
                'o.nama as nama_obat',
                'i.dosis',
                'i.frekuensi',
                'i.jumlah',
            ])
            ->groupBy('rekam_medis_id');
    }
}
