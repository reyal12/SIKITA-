<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Klinik &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'laporan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Laporan Klinik'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading report-heading">
                        <div>
                            <h2>Ringkasan laporan</h2>
                            <p class="muted">Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</p>
                        </div>
                        <button class="sikita-btn-outline no-print" type="button" onclick="window.print()">Cetak laporan</button>
                    </div>
                    <form class="admin-filter-form no-print" method="GET" action="{{ route('admin.laporan.index') }}">
                        <div class="dashboard-field">
                            <label for="start_date">Dari tanggal</label>
                            <input class="sikita-input" id="start_date" name="start_date" type="date" value="{{ $startDate }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="end_date">Sampai tanggal</label>
                            <input class="sikita-input" id="end_date" name="end_date" type="date" value="{{ $endDate }}" required>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Tampilkan laporan</button>
                    </form>
                </section>

                <section class="dashboard-grid dashboard-grid--stats admin-dashboard-section report-metrics" aria-label="Ringkasan laporan">
                    <article class="sikita-card dashboard-stat"><span class="muted">Pasien baru</span><strong>{{ $totalPatients }}</strong></article>
                    <article class="sikita-card dashboard-stat"><span class="muted">Pendaftaran pemeriksaan</span><strong>{{ $totalAppointments }}</strong></article>
                    <article class="sikita-card dashboard-stat"><span class="muted">Pemeriksaan tercatat</span><strong>{{ $totalServices }}</strong></article>
                    <article class="sikita-card dashboard-stat"><span class="muted">Resep diterbitkan</span><strong>{{ $totalPrescriptions }}</strong></article>
                </section>

                <section class="dashboard-grid dashboard-grid--two report-sections">
                    <article class="sikita-card dashboard-card">
                        <div class="section-heading"><div><h2>Status pendaftaran</h2><p class="muted">Jumlah janji temu per status.</p></div></div>
                        @php
                            $statusLabels = [
                                'terjadwal' => 'Terjadwal',
                                'dikonfirmasi' => 'Dikonfirmasi',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                                'tidak_hadir' => 'Tidak hadir',
                            ];
                        @endphp
                        <div class="report-status-list">
                            @foreach ($statusLabels as $status => $label)
                                <div><span>{{ $label }}</span><strong>{{ $appointmentStatuses->get($status, 0) }}</strong></div>
                            @endforeach
                        </div>
                    </article>
                    <article class="sikita-card dashboard-card">
                        <div class="section-heading"><div><h2>Penerimaan pembayaran</h2><p class="muted">Transaksi berstatus dibayar pada periode ini.</p></div></div>
                        <p class="report-revenue">Rp{{ number_format((float) $totalRevenue, 0, ',', '.') }}</p>
                    </article>
                </section>

                <section class="sikita-card dashboard-card admin-patient-card report-sections">
                    <div class="section-heading"><div><h2>Ringkasan per dokter</h2><p class="muted">Pendaftaran dan pemeriksaan menurut dokter.</p></div></div>
                    @if ($doctorSummary->isEmpty())
                        <p class="dashboard-empty">Belum ada data pemeriksaan pada periode ini.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>Dokter</th><th>Total pendaftaran</th><th>Selesai</th><th>Pemeriksaan tercatat</th></tr></thead>
                                <tbody>
                                    @foreach ($doctorSummary as $doctor)
                                        <tr><td>{{ $doctor->nama_lengkap }}</td><td>{{ $doctor->jumlah_janji }}</td><td>{{ $doctor->janji_selesai }}</td><td>{{ $doctor->jumlah_pemeriksaan }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <p class="report-print-footer">SIKITA &bull; Laporan dicetak {{ now()->format('d M Y H:i') }}</p>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
