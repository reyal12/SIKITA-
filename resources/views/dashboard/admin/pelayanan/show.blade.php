<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pemeriksaan &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pemantauan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Detail Pemeriksaan'])
            <div class="admin-page-content">
                @include('dashboard.admin.pemantauan.partials.tabs', ['activeTab' => 'pelayanan'])
                <section class="sikita-card dashboard-card">
                    <div class="section-heading">
                        <div>
                            <h2>Rekam medis pasien</h2>
                            <p class="muted">Hasil pemeriksaan dan resep hanya dapat dilihat.</p>
                        </div>
                        <a class="sikita-btn-outline" href="{{ route('admin.pemantauan.pelayanan.index') }}">Kembali ke daftar</a>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div><strong>Tanggal</strong><br>{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y H:i') }}</div>
                        <div><strong>Pasien</strong><br>{{ $record->nama_pasien }} ({{ $record->nomor_pasien }})</div>
                        <div><strong>Dokter</strong><br>{{ $record->nama_dokter }}</div>
                        <div><strong>Nomor janji</strong><br>{{ $record->nomor_janji ?: '—' }}</div>
                        <div><strong>Diagnosis</strong><br>{{ $record->diagnosis }}</div>
                        <div><strong>Keluhan</strong><br>{{ $record->keluhan_utama ?: '—' }}</div>
                        <div class="admin-patient-address"><strong>Rencana perawatan</strong><br>{{ $record->rencana_perawatan ?: '—' }}</div>
                    </div>
                </section>

                <section class="sikita-card dashboard-card admin-schedule-card">
                    <div class="section-heading"><div><h2>Tanda vital</h2></div></div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div><strong>Tekanan sistolik/diastolik</strong><br>{{ $record->tekanan_sistolik !== null && $record->tekanan_diastolik !== null ? $record->tekanan_sistolik.'/'.$record->tekanan_diastolik.' mmHg' : '—' }}</div>
                        <div><strong>Denyut jantung</strong><br>{{ $record->denyut_jantung !== null ? $record->denyut_jantung.' bpm' : '—' }}</div>
                        <div><strong>Suhu</strong><br>{{ $record->suhu_tubuh !== null ? $record->suhu_tubuh.' °C' : '—' }}</div>
                        <div><strong>Tinggi badan</strong><br>{{ $record->tinggi_badan_cm !== null ? $record->tinggi_badan_cm.' cm' : '—' }}</div>
                        <div><strong>Berat badan</strong><br>{{ $record->berat_badan_kg !== null ? $record->berat_badan_kg.' kg' : '—' }}</div>
                    </div>
                </section>

                <section class="sikita-card dashboard-card admin-schedule-card">
                    <div class="section-heading"><div><h2>Resep obat</h2></div></div>
                    @if ($prescriptions->isEmpty())
                        <p class="dashboard-empty">Belum ada resep untuk pemeriksaan ini.</p>
                    @else
                        @foreach ($prescriptions as $prescriptionNumber => $items)
                            <div class="service-prescription">
                                <h3>{{ $prescriptionNumber }}</h3>
                                <div class="dashboard-table-wrap">
                                    <table class="dashboard-table">
                                        <thead><tr><th>Obat</th><th>Dosis</th><th>Frekuensi</th><th>Durasi</th><th>Petunjuk</th></tr></thead>
                                        <tbody>
                                            @foreach ($items as $item)
                                                <tr>
                                                    <td>{{ $item->nama_obat }}</td>
                                                    <td>{{ $item->dosis }}</td>
                                                    <td>{{ $item->frekuensi }}</td>
                                                    <td>{{ $item->durasi_hari ? $item->durasi_hari.' hari' : '—' }}</td>
                                                    <td>{{ $item->petunjuk_pemakaian ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
