<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'dashboard'])

        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Ringkasan Klinik'])

            <div class="admin-page-content">
                <section id="ringkasan" class="dashboard-grid dashboard-grid--stats admin-dashboard-section" aria-label="Ringkasan klinik">
                    <article class="sikita-card dashboard-stat">
                        <span class="muted">Pasien terdaftar</span>
                        <strong>{{ $stats['patients'] }}</strong>
                    </article>
                    <article class="sikita-card dashboard-stat">
                        <span class="muted">Dokter aktif</span>
                        <strong>{{ $stats['active_doctors'] }}</strong>
                    </article>
                    <article class="sikita-card dashboard-stat">
                        <span class="muted">Pendaftaran hari ini</span>
                        <strong>{{ $stats['appointments_today'] }}</strong>
                    </article>
                </section>

                <section class="sikita-card dashboard-card admin-dashboard-section">
                    <div class="section-heading">
                        <div>
                            <h2>Pendaftaran terbaru</h2>
                            <p class="muted">Maksimal 10 pendaftaran terakhir.</p>
                        </div>
                        <a class="sikita-btn-outline" href="{{ route('admin.pemantauan.pendaftaran.index') }}">Pantau Pendaftaran</a>
                    </div>

                    @if ($appointments->isEmpty())
                        <p class="dashboard-empty">Belum ada data pendaftaran terbaru.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>No. antrean</th>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Tanggal periksa</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->nomor_antrean ?? '—' }}</td>
                                            <td>{{ $appointment->pasien?->nama_lengkap ?? '—' }}</td>
                                            <td>{{ $appointment->dokter?->nama_lengkap ?? '—' }}</td>
                                            <td>{{ $appointment->tanggal_janji->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.pemantauan.pendaftaran.show', $appointment->id) }}">
                                                    <span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">
                                                        {{ [
                                                            'terjadwal' => 'Terjadwal',
                                                            'dikonfirmasi' => 'Dikonfirmasi',
                                                            'selesai' => 'Selesai',
                                                            'dibatalkan' => 'Dibatalkan',
                                                            'tidak_hadir' => 'Tidak hadir',
                                                        ][$appointment->status] ?? $appointment->status }}
                                                    </span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
