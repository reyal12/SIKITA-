<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Dokter &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="dashboard-shell">
        <header class="dashboard-header">
            <div class="dashboard-brand">
                <div class="dashboard-brand-mark">S</div>
                <div>
                    <h1>SIKITA</h1>
                    <p>Panel Dokter Praktik</p>
                </div>
            </div>
            <div class="dashboard-header-actions">
                <span class="muted">dr. {{ $user->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="sikita-btn-outline" type="submit">Keluar</button>
                </form>
            </div>
        </header>

        @if (session('sukses'))
            <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
        @endif

        <section class="sikita-card dashboard-card">
            <div class="section-heading">
                <div>
                    <h2>Pendaftaran pasien</h2>
                    <p class="muted">Janji temu untuk dokter yang sedang masuk.</p>
                </div>
                <span class="dashboard-badge">{{ $appointments->count() }} kunjungan</span>
            </div>
            @if ($doctor === null)
                <p class="dashboard-empty">Akun ini belum terhubung dengan data dokter. Hubungi administrator klinik.</p>
            @elseif ($appointments->isEmpty())
                <p class="dashboard-empty">Belum ada janji temu pasien.</p>
            @else
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr><th>Tanggal &amp; waktu</th><th>Antrean</th><th>Pasien</th><th>Keluhan</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br>{{ substr($appointment->waktu_mulai, 0, 5) }}</td>
                                    <td>{{ $appointment->nomor_janji }}<br><span class="muted">No. {{ $appointment->nomor_antrean }}</span></td>
                                    <td>{{ $appointment->nama_pasien }}<br><span class="muted">{{ $appointment->nomor_pasien }}</span></td>
                                    <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                    <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                                    <td>
                                        @if ($appointment->status === 'terjadwal')
                                            <form method="POST" action="{{ route('dokter.janji-temu.complete', $appointment->id) }}">
                                                @csrf
                                                <button class="sikita-btn-outline" type="submit">Tandai selesai</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
