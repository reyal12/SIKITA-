<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $doctor ? 'Kelola Dokter' : 'Tambah Dokter' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    @php
        $isEditing = $doctor !== null;
        $formAction = $isEditing
            ? route('admin.dokter.update', $doctor->id)
            : route('admin.dokter.store');
    @endphp

    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'dokter'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', [
                'pageTitle' => $isEditing ? 'Kelola Dokter' : 'Tambah Dokter',
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
                        <div class="dashboard-field">
                            <label for="doctor_number">Nomor dokter *</label>
                            <input class="sikita-input" id="doctor_number" name="doctor_number" value="{{ old('doctor_number', $doctor->nomor_dokter ?? '') }}" maxlength="30" required>
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
                                <input class="sikita-input" id="email" name="email" type="email" value="{{ old('email', $doctor->pengguna?->email) }}" maxlength="255" autocomplete="email" required>
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

                @if ($isEditing)
                    <a class="sikita-btn-outline" href="{{ route('admin.jadwal-dokter.index', ['doctor_id' => $doctor->id]) }}">
                        Kelola jadwal dokter
                    </a>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
