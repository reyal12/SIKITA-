<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $patient ? 'Edit Pasien' : 'Tambah Pasien' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pasien'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => $patient ? 'Edit Data Pasien' : 'Tambah Pasien'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data pasien.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    class="sikita-card dashboard-card admin-patient-form"
                    method="POST"
                    action="{{ $patient ? route('admin.pasien.update', $patient->id) : route('admin.pasien.store') }}"
                >
                    @csrf
                    @if ($patient)
                        @method('PUT')
                    @endif

                    <div class="section-heading">
                        <div>
                            <h2>Informasi pasien</h2>
                            <p class="muted">NIK harus 16 digit angka. Nomor pasien akan dibuat otomatis jika tidak diisi.</p>
                        </div>
                    </div>

                    <div class="dashboard-form-grid admin-patient-form-grid">
                        @if ($patient)
                            <div class="dashboard-field">
                                <label for="nomor_pasien">Nomor pasien</label>
                                <input class="sikita-input" id="nomor_pasien" value="{{ $patient->nomor_pasien }}" readonly>
                            </div>
                        @else
                            <div class="dashboard-field">
                                <label for="nomor_pasien">Nomor pasien (opsional)</label>
                                <input class="sikita-input" id="nomor_pasien" name="nomor_pasien" value="{{ old('nomor_pasien') }}" maxlength="30">
                            </div>
                        @endif
                        <div class="dashboard-field">
                            <label for="nomor_identitas">NIK</label>
                            <input
                                class="sikita-input"
                                id="nomor_identitas"
                                name="nomor_identitas"
                                value="{{ old('nomor_identitas', $patient->nomor_identitas ?? '') }}"
                                inputmode="numeric"
                                pattern="[0-9]{16}"
                                maxlength="16"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="nama_lengkap">Nama lengkap *</label>
                            <input
                                class="sikita-input"
                                id="nama_lengkap"
                                name="nama_lengkap"
                                value="{{ old('nama_lengkap', $patient->nama_lengkap ?? '') }}"
                                maxlength="255"
                                autocomplete="name"
                                required
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_lahir">Tanggal lahir</label>
                            <input
                                class="sikita-input"
                                id="tanggal_lahir"
                                name="tanggal_lahir"
                                type="date"
                                value="{{ old('tanggal_lahir', $patient->tanggal_lahir ?? '') }}"
                                max="{{ today()->toDateString() }}"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="jenis_kelamin">Jenis kelamin</label>
                            <select class="sikita-input" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="laki-laki" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'laki-laki')>Laki-laki</option>
                                <option value="perempuan" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'perempuan')>Perempuan</option>
                                <option value="lainnya" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'lainnya')>Lainnya</option>
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="telepon">Nomor telepon</label>
                            <input
                                class="sikita-input"
                                id="telepon"
                                name="telepon"
                                type="tel"
                                value="{{ old('telepon', $patient->telepon ?? '') }}"
                                maxlength="30"
                                autocomplete="tel"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="email">Email</label>
                            <input
                                class="sikita-input"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $patient->email ?? '') }}"
                                maxlength="255"
                                autocomplete="email"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="golongan_darah">Golongan darah</label>
                            <select class="sikita-input" id="golongan_darah" name="golongan_darah">
                                <option value="">Pilih golongan darah</option>
                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                    <option value="{{ $bloodType }}" @selected(old('golongan_darah', $patient->golongan_darah ?? '') === $bloodType)>{{ $bloodType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field admin-patient-address">
                            <label for="alamat">Alamat</label>
                            <textarea class="sikita-input" id="alamat" name="alamat" maxlength="2000">{{ old('alamat', $patient->alamat ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ $patient ? route('admin.pasien.show', $patient->id) : route('admin.pasien.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">{{ $patient ? 'Simpan perubahan' : 'Tambah pasien' }}</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
