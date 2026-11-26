<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Dokter &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    @php
        $weekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    @endphp
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'jadwal'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Jadwal Dokter'])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Perubahan jadwal tidak dapat disimpan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading admin-patient-toolbar">
                        <div>
                            <h2>Jadwal praktik mingguan</h2>
                            <p class="muted">Kelola dokter, hari praktik, jam, durasi, dan kuota pasien.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.dokter.index') }}">Daftar dokter</a>
                    </div>

                    <form class="admin-filter-form" method="GET" action="{{ route('admin.jadwal-dokter.index') }}">
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
                            <label for="weekday">Hari praktik</label>
                            <select class="sikita-input" id="weekday" name="weekday">
                                <option value="">Semua hari</option>
                                @foreach ($weekdays as $weekday => $weekdayName)
                                    <option value="{{ $weekday }}" @selected((string) ($filters['weekday'] ?? '') === (string) $weekday)>{{ $weekdayName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Terapkan filter</button>
                    </form>

                    @if ($schedules->isEmpty())
                        <p class="dashboard-empty">Belum ada jadwal praktik yang sesuai filter.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Hari</th><th>Jam praktik</th><th>Dokter</th><th>Spesialis</th><th>Durasi</th><th>Kuota</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $schedule)
                                        <tr>
                                            <td>{{ $weekdays[(int) $schedule->hari_dalam_minggu] ?? '—' }}</td>
                                            <td>{{ substr($schedule->waktu_mulai, 0, 5) }}–{{ substr($schedule->waktu_selesai, 0, 5) }}</td>
                                            <td>{{ $schedule->dokter->nama_lengkap }}</td>
                                            <td>{{ $schedule->dokter->spesialis->nama }}</td>
                                            <td>{{ $schedule->durasi_janji_menit }} menit</td>
                                            <td>{{ $schedule->maksimal_pasien }} pasien</td>
                                            <td>
                                                <details>
                                                    <summary>Ubah</summary>
                                                    <form method="POST" action="{{ route('admin.jadwal-dokter.update', $schedule->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="dashboard-field">
                                                            <label for="doctor_{{ $schedule->id }}">Dokter</label>
                                                            <select class="sikita-input" id="doctor_{{ $schedule->id }}" name="doctor_id" required>
                                                                @foreach ($doctors as $doctor)
                                                                    <option value="{{ $doctor->id }}" @selected((int) $schedule->dokter_id === (int) $doctor->id)>{{ $doctor->nama_lengkap }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="dashboard-field">
                                                            <label for="weekday_{{ $schedule->id }}">Hari praktik</label>
                                                            <select class="sikita-input" id="weekday_{{ $schedule->id }}" name="weekday" required>
                                                                @foreach ($weekdays as $weekday => $weekdayName)
                                                                    <option value="{{ $weekday }}" @selected((int) $schedule->hari_dalam_minggu === $weekday)>{{ $weekdayName }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="dashboard-field">
                                                            <label for="start_{{ $schedule->id }}">Jam mulai</label>
                                                            <input class="sikita-input" id="start_{{ $schedule->id }}" name="start_time" type="time" value="{{ substr($schedule->waktu_mulai, 0, 5) }}" required>
                                                        </div>
                                                        <div class="dashboard-field">
                                                            <label for="end_{{ $schedule->id }}">Jam selesai</label>
                                                            <input class="sikita-input" id="end_{{ $schedule->id }}" name="end_time" type="time" value="{{ substr($schedule->waktu_selesai, 0, 5) }}" required>
                                                        </div>
                                                        <div class="dashboard-field">
                                                            <label for="duration_{{ $schedule->id }}">Durasi janji (menit)</label>
                                                            <input class="sikita-input" id="duration_{{ $schedule->id }}" name="appointment_duration" type="number" min="1" max="1440" value="{{ $schedule->durasi_janji_menit }}" required>
                                                        </div>
                                                        <div class="dashboard-field">
                                                            <label for="quota_{{ $schedule->id }}">Kuota pasien</label>
                                                            <input class="sikita-input" id="quota_{{ $schedule->id }}" name="max_patients" type="number" min="1" max="500" value="{{ $schedule->maksimal_pasien }}" required>
                                                        </div>
                                                        <button class="sikita-btn-outline" type="submit">Simpan perubahan</button>
                                                    </form>
                                                </details>
                                                <form method="POST" action="{{ route('admin.jadwal-dokter.destroy', $schedule->id) }}" onsubmit="return confirm('Hapus jadwal praktik ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="admin-patient-delete" type="submit">Hapus jadwal</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $schedules->links() }}</div>
                    @endif
                </section>

                <section class="sikita-card dashboard-card admin-schedule-card">
                    <div class="section-heading">
                        <div>
                            <h2>Tambah jadwal praktik</h2>
                            <p class="muted">Jam selesai harus setelah jam mulai; durasi dan kuota harus lebih dari nol.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.jadwal-dokter.store') }}">
                        @csrf
                        <div class="dashboard-form-grid admin-patient-form-grid">
                            <div class="dashboard-field">
                                <label for="new_doctor_id">Dokter *</label>
                                <select class="sikita-input" id="new_doctor_id" name="doctor_id" required>
                                    <option value="">Pilih dokter</option>
                                    @foreach ($doctors->where('aktif', true) as $doctor)
                                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $filters['doctor_id'] ?? '') === (string) $doctor->id)>{{ $doctor->nama_lengkap }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dashboard-field">
                                <label for="new_weekday">Hari praktik *</label>
                                <select class="sikita-input" id="new_weekday" name="weekday" required>
                                    @foreach ($weekdays as $weekday => $weekdayName)
                                        <option value="{{ $weekday }}" @selected((string) old('weekday', '') === (string) $weekday)>{{ $weekdayName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dashboard-field">
                                <label for="new_start_time">Jam mulai *</label>
                                <input class="sikita-input" id="new_start_time" name="start_time" type="time" value="{{ old('start_time') }}" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="new_end_time">Jam selesai *</label>
                                <input class="sikita-input" id="new_end_time" name="end_time" type="time" value="{{ old('end_time') }}" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="new_appointment_duration">Durasi janji (menit) *</label>
                                <input class="sikita-input" id="new_appointment_duration" name="appointment_duration" type="number" min="1" max="1440" value="{{ old('appointment_duration', 30) }}" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="new_max_patients">Kuota pasien *</label>
                                <input class="sikita-input" id="new_max_patients" name="max_patients" type="number" min="1" max="500" value="{{ old('max_patients', 20) }}" required>
                            </div>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Tambah jadwal</button>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
