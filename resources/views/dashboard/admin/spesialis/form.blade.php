<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $specialty ? 'Edit Spesialis' : 'Tambah Spesialis' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'spesialis'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => $specialty ? 'Edit Spesialis' : 'Tambah Spesialis'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ $specialty ? route('admin.spesialis.update', $specialty->id) : route('admin.spesialis.store') }}">
                    @csrf
                    @if ($specialty)
                        @method('PUT')
                    @endif
                    <div class="section-heading">
                        <div>
                            <h2>Informasi spesialis</h2>
                            <p class="muted">Jadwal praktik dan kuota pasien ditetapkan pada masing-masing dokter.</p>
                        </div>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="kode">Kode *</label>
                            <input class="sikita-input" id="kode" name="kode" value="{{ old('kode', $specialty->kode ?? '') }}" maxlength="20" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="nama">Nama spesialis *</label>
                            <input class="sikita-input" id="nama" name="nama" value="{{ old('nama', $specialty->nama ?? '') }}" maxlength="255" required>
                        </div>
                        <div class="dashboard-field admin-patient-address">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea class="sikita-input" id="deskripsi" name="deskripsi" maxlength="2000">{{ old('deskripsi', $specialty->deskripsi ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.spesialis.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">{{ $specialty ? 'Simpan perubahan' : 'Tambah spesialis' }}</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
