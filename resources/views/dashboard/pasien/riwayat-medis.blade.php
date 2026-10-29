@extends('dashboard.pasien.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Perjalanan kesehatan</span>
                <h2>Riwayat Kunjungan</h2>
            </div>
        </div>
        @if ($appointments->isEmpty())
            <p class="dashboard-empty">Riwayat kunjungan Anda akan tampil di sini.</p>
        @else
            <div class="dashboard-table-wrap">
                <table class="dashboard-table patient-history-table">
                    <thead>
                        <tr><th>Tanggal</th><th>Dokter</th><th>Keluhan</th><th>Hasil Pemeriksaan</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            @php($record = $medicalRecords->firstWhere('janji_temu_id', $appointment->id))
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br><span class="muted">{{ substr($appointment->waktu_mulai, 0, 5) }}</span></td>
                                <td><strong>{{ $appointment->nama_dokter }}</strong><br><span class="muted">{{ $appointment->nama_spesialis }}</span></td>
                                <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                <td>{{ $record?->diagnosis ?? 'Belum tersedia' }}</td>
                                <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Catatan dokter</span>
                <h2>Hasil Pemeriksaan</h2>
            </div>
        </div>
        @forelse ($medicalRecords as $record)
            <article class="patient-medical-record">
                <div class="patient-medical-detail-heading">
                    <strong>{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y, H:i') }}</strong>
                    <span class="muted">{{ $record->nama_dokter }}</span>
                </div>
                <p><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>
                @if ($record->keluhan_utama)
                    <p><strong>Keluhan:</strong> {{ $record->keluhan_utama }}</p>
                @endif
                @if ($record->rencana_perawatan)
                    <p><strong>Rencana perawatan:</strong> {{ $record->rencana_perawatan }}</p>
                @endif
                <p class="muted">
                    Tekanan darah: {{ $record->tekanan_sistolik && $record->tekanan_diastolik ? "{$record->tekanan_sistolik}/{$record->tekanan_diastolik} mmHg" : '—' }}
                    &bull; Denyut jantung: {{ $record->denyut_jantung ? "{$record->denyut_jantung} bpm" : '—' }}
                    &bull; Suhu: {{ $record->suhu_tubuh ? "{$record->suhu_tubuh} °C" : '—' }}
                </p>
            </article>
        @empty
            <p class="dashboard-empty">Hasil pemeriksaan belum tersedia.</p>
        @endforelse
    </section>

    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Petunjuk penggunaan obat</span>
                <h2>Resep</h2>
            </div>
        </div>
        @forelse ($prescriptions as $items)
            @php($prescription = $items->first())
            <article class="patient-medical-record">
                <div class="patient-medical-detail-heading">
                    <strong>{{ $prescription->nomor_resep }}</strong>
                    <span class="muted">{{ \Illuminate\Support\Carbon::parse($prescription->diresepkan_pada)->format('d M Y') }}</span>
                </div>
                @if ($prescription->catatan)
                    <p>{{ $prescription->catatan }}</p>
                @endif
                <ul>
                    @foreach ($items as $item)
                        <li>{{ $item->nama_obat }} — {{ $item->dosis }}, {{ $item->frekuensi }} ({{ $item->jumlah }} unit){{ $item->durasi_hari ? ", {$item->durasi_hari} hari" : '' }}{{ $item->petunjuk_pemakaian ? ". {$item->petunjuk_pemakaian}" : '' }}</li>
                    @endforeach
                </ul>
            </article>
        @empty
            <p class="dashboard-empty">Resep belum tersedia.</p>
        @endforelse
    </section>
@endsection
