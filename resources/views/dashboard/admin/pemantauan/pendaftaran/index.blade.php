<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pantau Pendaftaran &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pemantauan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Pendaftaran & Pelayanan'])
            <div class="admin-page-content">
                @include('dashboard.admin.pemantauan.partials.tabs', ['activeTab' => 'pendaftaran'])
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading">
                        <div>
                            <h2>Pendaftaran pasien</h2>
                            <p class="muted">Daftar janji temu dan status pendaftaran. Informasi ini hanya untuk dilihat.</p>
                        </div>
                    </div>

                    <form class="admin-filter-form" method="GET" action="{{ route('admin.pemantauan.pendaftaran.index') }}">
                        <div class="dashboard-field">
                            <label for="start_date">Dari tanggal</label>
                            <input class="sikita-input" id="start_date" name="start_date" type="date" value="{{ $startDate }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="end_date">Sampai tanggal</label>
                            <input class="sikita-input" id="end_date" name="end_date" type="date" value="{{ $endDate }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="doctor_id">Dokter</label>
                            <select class="sikita-input" id="doctor_id" name="doctor_id">
                                <option value="">Semua dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) ($filters['doctor_id'] ?? '') === (string) $doctor->id)>{{ $doctor->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="status">Status</label>
                            <select class="sikita-input" id="status" name="status">
                                <option value="">Semua status</option>
                                @foreach (['terjadwal' => 'Terjadwal', 'dikonfirmasi' => 'Dikonfirmasi', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan', 'tidak_hadir' => 'Tidak hadir'] as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field admin-filter-search">
                            <label for="search">Nama pasien</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100">
                        </div>
                        <button class="sikita-btn-primer" type="submit">Terapkan filter</button>
                    </form>

                    @if ($appointments->isEmpty())
                        <p class="dashboard-empty">Belum ada data pendaftaran pada filter yang dipilih.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>No. antrean</th>
                                        <th>No. janji</th>
                                        <th>Tanggal periksa</th>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Jadwal</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($appointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->nomor_antrean ?? '—' }}</td>
                                            <td>{{ $appointment->nomor_janji }}</td>
                                            <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br><span class="muted">{{ $appointment->waktu_mulai ? substr($appointment->waktu_mulai, 0, 5) : '—' }}</span></td>
                                            <td>{{ $appointment->pasien->nama_lengkap }}<br><span class="muted">{{ $appointment->pasien->nomor_pasien }}</span></td>
                                            <td>{{ $appointment->dokter->nama_lengkap }}</td>
                                            <td>{{ $appointment->jadwal_praktik }}</td>
                                            <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                            <td>{{ str_replace('_', ' ', ucfirst($appointment->status)) }}</td>
                                            <td><a class="sikita-btn-outline" href="{{ route('admin.pemantauan.pendaftaran.show', $appointment->id) }}">Lihat</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $appointments->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
