<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
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
            'doctor_id' => ['nullable', 'integer', 'exists:dokter,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();

        $records = $this->recordQuery()
            ->whereBetween('r.dicatat_pada', [$startDate.' 00:00:00', $endDate.' 23:59:59'])
            ->when($validated['doctor_id'] ?? null, function ($query, int $doctorId): void {
                $query->where('r.dokter_id', $doctorId);
            })
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where('p.nama_lengkap', 'like', '%'.$search.'%');
            })
            ->orderByDesc('r.dicatat_pada')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard.admin.pelayanan.index', [
            'records' => $records,
            'prescriptionsByRecord' => $this->prescriptionsForRecords($records->getCollection()),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $validated['search'] ?? '',
            'doctorId' => $validated['doctor_id'] ?? '',
            'doctors' => $this->doctors(),
            'user' => $request->user(),
        ]);
    }

    public function show(int $record): View
    {
        $medicalRecord = $this->recordQuery()
            ->where('r.id', $record)
            ->first();

        abort_if($medicalRecord === null, 404);

        return view('dashboard.admin.pelayanan.show', [
            'record' => $medicalRecord,
            'prescriptions' => $this->prescriptionsForRecords(collect([$medicalRecord]))
                ->get($medicalRecord->id, collect())
                ->groupBy('nomor_resep'),
            'user' => request()->user(),
        ]);
    }

    private function recordQuery(): Builder
    {
        return DB::table('rekam_medis as r')
            ->join('pasien as p', 'p.id', '=', 'r.pasien_id')
            ->join('dokter as d', 'd.id', '=', 'r.dokter_id')
            ->leftJoin('janji_temu as j', 'j.id', '=', 'r.janji_temu_id')
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
                'r.tinggi_badan_cm',
                'r.berat_badan_kg',
                'p.nama_lengkap as nama_pasien',
                'p.nomor_pasien',
                'd.nama_lengkap as nama_dokter',
                'j.nomor_janji',
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
                'i.durasi_hari',
                'i.jumlah',
                'i.petunjuk_pemakaian',
            ])
            ->groupBy('rekam_medis_id');
    }

    private function doctors(): Collection
    {
        return DB::table('dokter')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
    }
}
