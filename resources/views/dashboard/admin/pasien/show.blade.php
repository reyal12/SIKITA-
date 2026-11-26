<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pasien &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pasien'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Detail Pasien'])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Tindakan pasien tidak dapat diselesaikan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="sikita-card dashboard-card">
                    <div class="section-heading admin-patient-toolbar">
                        <div>
                            <h2>{{ $patient->nama_lengkap }}</h2>
                            <p class="muted">{{ $patient->nomor_pasien }}</p>
                        </div>
                        <div class="admin-patient-actions">
                            <a class="sikita-btn-outline" href="{{ route('admin.pasien.index') }}">Kembali</a>
                            <a class="sikita-btn-primer" href="{{ route('admin.pasien.edit', $patient->id) }}">Edit pasien</a>
                        </div>
                    </div>

                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div><strong>NIK</strong><br>{{ $patient->nomor_identitas ?: '—' }}</div>
                        <div><strong>Tanggal lahir</strong><br>{{ $patient->tanggal_lahir ? \Illuminate\Support\Carbon::parse($patient->tanggal_lahir)->format('d M Y') : '—' }}</div>
                        <div><strong>Jenis kelamin</strong><br>{{ $patient->jenis_kelamin ?: '—' }}</div>
                        <div><strong>Telepon</strong><br>{{ $patient->telepon ?: '—' }}</div>
                        <div><strong>Email</strong><br>{{ $patient->email ?: '—' }}</div>
                        <div><strong>Golongan darah</strong><br>{{ $patient->golongan_darah ?: '—' }}</div>
                        <div class="admin-patient-address"><strong>Alamat</strong><br>{{ $patient->alamat ?: '—' }}</div>
                    </div>
                </section>

                <section class="sikita-card dashboard-card admin-schedule-card">
                    <div class="section-heading">
                        <div>
                            <h2>Riwayat janji temu</h2>
                            <p class="muted">Informasi janji temu ditampilkan hanya untuk dibaca.</p>
                        </div>
                    </div>
                    @if ($appointments->isEmpty())
                        <p class="dashboard-empty">Belum ada riwayat janji temu.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>No. janji temu</th><th>Tanggal &amp; jam</th><th>Dokter</th><th>Keluhan</th><th>Status</th></tr></thead>
                                <tbody>
                                    @foreach ($appointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->nomor_janji }}</td>
                                            <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br><span class="muted">{{ $appointment->waktu_mulai ? substr($appointment->waktu_mulai, 0, 5) : '—' }}</span></td>
                                            <td>{{ $appointment->nama_dokter ?: '—' }}</td>
                                            <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                            <td>{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                <section class="sikita-card dashboard-card admin-schedule-card">
                    <div class="section-heading">
                        <div>
                            <h2>Riwayat pemeriksaan</h2>
                            <p class="muted">Hasil pemeriksaan dokter tidak dapat diubah dari halaman admin ini.</p>
                        </div>
                    </div>
                    @if ($medicalRecords->isEmpty())
                        <p class="dashboard-empty">Belum ada riwayat pemeriksaan.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>Waktu</th><th>Dokter</th><th>Keluhan</th><th>Diagnosis</th><th>Rencana perawatan</th><th>Tanda vital</th></tr></thead>
                                <tbody>
                                    @foreach ($medicalRecords as $record)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y H:i') }}</td>
                                            <td>{{ $record->nama_dokter ?: '—' }}</td>
                                            <td>{{ $record->keluhan_utama ?: '—' }}</td>
                                            <td>{{ $record->diagnosis }}</td>
                                            <td>{{ $record->rencana_perawatan ?: '—' }}</td>
                                            <td>
                                                {{ $record->tekanan_sistolik && $record->tekanan_diastolik ? $record->tekanan_sistolik.'/'.$record->tekanan_diastolik.' mmHg' : '—' }}
                                                @if ($record->denyut_jantung)
                                                    <br>{{ $record->denyut_jantung }} bpm
                                                @endif
                                                @if ($record->suhu_tubuh)
                                                    <br>{{ $record->suhu_tubuh }} °C
                                                @endif
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
