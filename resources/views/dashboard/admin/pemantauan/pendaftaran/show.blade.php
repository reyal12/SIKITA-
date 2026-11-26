<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pendaftaran &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pemantauan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Detail Pendaftaran'])
            <div class="admin-page-content">
                @include('dashboard.admin.pemantauan.partials.tabs', ['activeTab' => 'pendaftaran'])
                <section class="sikita-card dashboard-card">
                    <div class="section-heading">
                        <div>
                            <h2>{{ $appointment->nomor_janji }}</h2>
                            <p class="muted">Detail pendaftaran hanya dapat dilihat.</p>
                        </div>
                        <a class="sikita-btn-outline" href="{{ route('admin.pemantauan.pendaftaran.index') }}">Kembali ke daftar</a>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div><strong>Nomor antrean</strong><br>{{ $appointment->nomor_antrean ?? '—' }}</div>
                        <div><strong>Tanggal periksa</strong><br>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}</div>
                        <div><strong>Jam</strong><br>{{ $appointment->waktu_mulai ? substr($appointment->waktu_mulai, 0, 5) : '—' }}</div>
                        <div><strong>Pasien</strong><br>{{ $appointment->pasien->nama_lengkap }} ({{ $appointment->pasien->nomor_pasien }})</div>
                        <div><strong>Dokter</strong><br>{{ $appointment->dokter->nama_lengkap }}</div>
                        <div><strong>Jadwal praktik</strong><br>{{ $appointment->jadwal_praktik }}</div>
                        <div><strong>Status pendaftaran</strong><br>{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</div>
                        <div class="admin-patient-address"><strong>Keluhan</strong><br>{{ $appointment->keluhan_utama ?: '—' }}</div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
