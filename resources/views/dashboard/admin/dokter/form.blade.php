<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $doctor ? 'Kelola Dokter' : 'Buat Akun Dokter' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    @php
        $isEditing = $doctor !== null;
        $weekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $formAction = $isEditing
            ? route('admin.dokter.update', $doctor->id)
            : (request()->routeIs('admin.akun.dokter.create')
                ? route('admin.akun.dokter.store')
                : route('admin.dokter.store'));
    @endphp

    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'dokter'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', [
                'pageTitle' => $isEditing ? 'Kelola Dokter' : 'Buat Akun Dokter',
            ])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data yang dimasukkan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ $formAction }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif
                    <div class="section-heading">
                        <div>
                            <h2>Profil dan akun dokter</h2>
                            <p class="muted">{{ $isEditing ? 'Ubah data dokter yang tersimpan.' : 'Form ini sekaligus membuat akun login dengan peran dokter.' }}</p>
                        </div>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="name">Nama lengkap *</label>
                            <input class="sikita-input" id="name" name="name" value="{{ old('name', $doctor->nama_lengkap ?? '') }}" maxlength="255" required>
                        </div>
                        @unless ($isEditing)
                            <div class="dashboard-field">
                                <label for="email">Email akun *</label>
                                <input class="sikita-input" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="password">Kata sandi awal *</label>
                                <input class="sikita-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="password_confirmation">Ulangi kata sandi *</label>
                                <input class="sikita-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                            </div>
                        @endunless
                        @if ($isEditing && $doctor->pengguna_id !== null)
                            <div class="dashboard-field">
                                <label for="email">Email akun *</label>
                                <input class="sikita-input" id="email" name="email" type="email" value="{{ old('email', $doctor->email) }}" maxlength="255" autocomplete="email" required>
                            </div>
                        @elseif ($isEditing)
                            <div class="admin-schedule-notice" role="status">
                                Dokter ini belum terhubung ke akun pengguna. Profil dan jadwal tetap dapat dikelola.
                            </div>
                        @endif
                        <div class="dashboard-field">
                            <label for="specialty_id">Spesialis *</label>
                            <select class="sikita-input" id="specialty_id" name="specialty_id" required>
                                <option value="">Pilih spesialis</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" @selected((string) old('specialty_id', $doctor->spesialis_id ?? '') === (string) $specialty->id)>{{ $specialty->nama }}</option>
                                @endforeach
                            </select>
                            @if ($specialties->isEmpty())
                                <span class="field-hint">Buat data spesialis terlebih dahulu.</span>
                            @endif
                        </div>
                        <div class="dashboard-field">
                            <label for="license_number">Nomor izin praktik *</label>
                            <input class="sikita-input" id="license_number" name="license_number" value="{{ old('license_number', $doctor->nomor_izin_praktik ?? '') }}" maxlength="50" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="phone">Nomor telepon</label>
                            <input class="sikita-input" id="phone" name="phone" type="tel" value="{{ old('phone', $doctor->telepon ?? '') }}" maxlength="30">
                        </div>
                        @if ($isEditing)
                            <div class="dashboard-field">
                                <label for="aktif">Status akun dokter</label>
                                <select class="sikita-input" id="aktif" name="aktif" required>
                                    <option value="1" @selected((bool) old('aktif', $doctor->aktif))>Aktif</option>
                                    <option value="0" @selected(!(bool) old('aktif', $doctor->aktif))>Nonaktif</option>
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.dokter.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">{{ $isEditing ? 'Simpan profil dokter' : 'Buat akun dokter' }}</button>
                    </div>
                </form>

                @if ($isEditing && $doctor->aktif)
                    <section class="sikita-card dashboard-card admin-schedule-card">
                        <div class="section-heading">
                            <div>
                                <h2>Jadwal praktik mingguan</h2>
                                <p class="muted">Atur rentang jam praktik dan kuota total pasien untuk tiap jadwal.</p>
                            </div>
                        </div>

                        @if ($schedules->isEmpty())
                            <p class="dashboard-empty">Belum ada jadwal praktik untuk dokter ini.</p>
                        @else
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr><th>Hari</th><th>Jam mulai</th><th>Jam selesai</th><th>Durasi</th><th>Kuota pasien</th><th>Aksi</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schedules as $schedule)
                                            <tr>
                                                <td colspan="5">
                                                    <form class="admin-schedule-inline-form" method="POST" action="{{ route('admin.dokter.jadwal.update', $schedule->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <select class="sikita-input" name="weekday" aria-label="Hari praktik" required>
                                                            @foreach ($weekdays as $weekday => $weekdayName)
                                                                <option value="{{ $weekday }}" @selected((int) $schedule->hari_dalam_minggu === $weekday)>{{ $weekdayName }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input class="sikita-input" name="start_time" type="time" value="{{ substr($schedule->waktu_mulai, 0, 5) }}" aria-label="Jam mulai" required>
                                                        <input class="sikita-input" name="end_time" type="time" value="{{ substr($schedule->waktu_selesai, 0, 5) }}" aria-label="Jam selesai" required>
                                                        <select class="sikita-input" name="appointment_duration" aria-label="Durasi janji temu" required>
                                                            @foreach ([15, 20, 30, 45, 60] as $duration)
                                                                <option value="{{ $duration }}" @selected((int) $schedule->durasi_janji_menit === $duration)>{{ $duration }} menit</option>
                                                            @endforeach
                                                        </select>
                                                        <input class="sikita-input" name="max_patients" type="number" min="1" max="500" value="{{ $schedule->maksimal_pasien }}" aria-label="Kuota pasien" required>
                                                        <button class="sikita-btn-outline" type="submit">Simpan</button>
                                                    </form>
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('admin.dokter.jadwal.destroy', $schedule->id) }}" onsubmit="return confirm('Hapus jadwal praktik ini?')">
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
                        @endif

                        <form class="admin-schedule-create" method="POST" action="{{ route('admin.dokter.jadwal.store', $doctor->id) }}">
                            @csrf
                            <h3>Tambah jadwal praktik</h3>
                            <div class="admin-schedule-inline-form">
                                <select class="sikita-input" name="weekday" aria-label="Hari praktik" required>
                                    @foreach ($weekdays as $weekday => $weekdayName)
                                        <option value="{{ $weekday }}" @selected((string) old('weekday') === (string) $weekday)>{{ $weekdayName }}</option>
                                    @endforeach
                                </select>
                                <input class="sikita-input" name="start_time" type="time" value="{{ old('start_time') }}" aria-label="Jam mulai" required>
                                <input class="sikita-input" name="end_time" type="time" value="{{ old('end_time') }}" aria-label="Jam selesai" required>
                                <select class="sikita-input" name="appointment_duration" aria-label="Durasi janji temu" required>
                                    @foreach ([15, 20, 30, 45, 60] as $duration)
                                        <option value="{{ $duration }}" @selected((string) old('appointment_duration', 30) === (string) $duration)>{{ $duration }} menit</option>
                                    @endforeach
                                </select>
                                <input class="sikita-input" name="max_patients" type="number" min="1" max="500" value="{{ old('max_patients', 20) }}" aria-label="Kuota pasien" required>
                                <button class="sikita-btn-primer" type="submit">Tambah jadwal</button>
                            </div>
                        </form>
                    </section>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
