<div>
    <!-- Nothing worth having comes easy. - Theodore Roosevelt -->
</div>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Dokter &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'jadwal'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Jadwal Dokter'])
            <div class="admin-page-content">
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading admin-patient-toolbar">
                        <div>
                            <h2>Jadwal praktik mingguan</h2>
                            <p class="muted">Lihat jadwal, durasi layanan, dan kuota semua dokter. Pilih Kelola untuk mengubah jadwal dokter.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.dokter.index') }}">Daftar dokter</a>
                    </div>

                    @if ($schedules->isEmpty())
                        <p class="dashboard-empty">Belum ada jadwal praktik. Tambahkan jadwal melalui menu Dokter.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Hari</th><th>Jam praktik</th><th>Dokter</th><th>Spesialis</th><th>Durasi</th><th>Kuota</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td>{{ ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int) $schedule->hari_dalam_minggu] ?? '—' }}</td>
                                            <td>{{ substr($schedule->waktu_mulai, 0, 5) }}–{{ substr($schedule->waktu_selesai, 0, 5) }}</td>
                                            <td>{{ $schedule->nama_lengkap }}</td>
                                            <td>{{ $schedule->nama }}</td>
                                            <td>{{ $schedule->durasi_janji_menit }} menit</td>
                                            <td>{{ $schedule->maksimal_pasien }} pasien</td>
                                            <td>
                                                <span class="dashboard-badge dashboard-badge--{{ $schedule->aktif ? 'dibayar' : 'dibatalkan' }}">
                                                    {{ $schedule->aktif ? 'Aktif' : 'Dokter nonaktif' }}
                                                </span>
                                            </td>
                                            <td><a class="sikita-btn-outline" href="{{ route('admin.dokter.edit', $schedule->dokter_id) }}">{{ $schedule->aktif ? 'Kelola' : 'Lihat dokter' }}</a></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $schedules->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
