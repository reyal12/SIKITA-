@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-welcome-banner doctor-welcome-banner doctor-patient-banner">
        <div>
            <span class="patient-welcome-kicker">Rekam medis pasien</span>
            <h2>{{ $patient->nama_lengkap }}</h2>
            <p>{{ $patient->nomor_pasien }} &bull; {{ $patient->telepon ?: $patient->email ?: 'Kontak belum diisi' }}</p>
        </div>
        <span class="dashboard-badge">{{ $records->count() }} pemeriksaan</span>
    </section>

    @forelse ($records as $record)
        <section class="patient-section-card patient-page-card doctor-record-card">
            <div class="patient-section-heading">
                <div>
                    <span class="patient-card-eyebrow">Hasil pemeriksaan</span>
                    <h2>{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y, H:i') }}</h2>
                    <p class="muted">Dokter {{ $record->nama_dokter }} @if ($record->nomor_janji)&bull; {{ $record->nomor_janji }}@endif</p>
                </div>
            </div>
            <div class="doctor-record-grid">
                <div class="doctor-record-section">
                    <h3>Keluhan &amp; diagnosis</h3>
                    <p><strong>Keluhan:</strong> {{ $record->keluhan_utama ?: '—' }}</p>
                    <p><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>
                    <p><strong>Tindakan:</strong> {{ $record->rencana_perawatan ?: '—' }}</p>
                    <p><strong>Catatan:</strong> {{ $record->catatan ?: '—' }}</p>
                </div>
                <div class="doctor-record-section">
                    <h3>Tanda vital</h3>
                    <p><strong>Tekanan darah:</strong> {{ $record->tekanan_sistolik && $record->tekanan_diastolik ? $record->tekanan_sistolik.'/'.$record->tekanan_diastolik.' mmHg' : '—' }}</p>
                    <p><strong>Denyut jantung:</strong> {{ $record->denyut_jantung ? $record->denyut_jantung.' bpm' : '—' }}</p>
                    <p><strong>Suhu:</strong> {{ $record->suhu_tubuh ? $record->suhu_tubuh.' °C' : '—' }}</p>
                    <p><strong>Tinggi / berat:</strong> {{ $record->tinggi_badan_cm ?: '—' }} cm / {{ $record->berat_badan_kg ?: '—' }} kg</p>
                </div>
            </div>

            @php($items = $prescriptions->get($record->id, collect()))
            @if ($items->isNotEmpty())
                <div class="doctor-prescription">
                    <div class="patient-section-heading">
                        <div><span class="patient-card-eyebrow">Obat yang diresepkan</span><h3>{{ $items->first()->nomor_resep }}</h3></div>
                    </div>
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table patient-history-table">
                            <thead><tr><th>Obat</th><th>Dosis</th><th>Frekuensi</th><th>Durasi</th><th>Jumlah</th><th>Petunjuk</th></tr></thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td><strong>{{ $item->nama_obat }}</strong></td>
                                        <td>{{ $item->dosis }}</td>
                                        <td>{{ $item->frekuensi }}</td>
                                        <td>{{ $item->durasi_hari ? $item->durasi_hari.' hari' : '—' }}</td>
                                        <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                                        <td>{{ $item->petunjuk_pemakaian ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>
    @empty
        <section class="patient-section-card patient-page-card">
            <div class="doctor-empty-state">
                <strong>Belum ada riwayat pemeriksaan</strong>
                <span>Catatan pemeriksaan pasien akan muncul di halaman ini.</span>
            </div>
        </section>
    @endforelse
@endsection
