<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $item ? 'Edit Informasi' : 'Tambah Informasi' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'informasi-klinik'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => $item ? 'Edit Informasi Klinik' : 'Tambah Informasi Klinik'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali informasi klinik.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ $item ? route('admin.informasi-klinik.update', $item->id) : route('admin.informasi-klinik.store') }}">
                    @csrf
                    @if ($item)
                        @method('PUT')
                    @endif
                    <div class="section-heading">
                        <div>
                            <h2>Informasi layanan atau kegiatan</h2>
                            <p class="muted">Informasi yang diarsipkan tetap tersimpan dan dapat diaktifkan kembali.</p>
                        </div>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="kategori">Jenis informasi *</label>
                            <select class="sikita-input" id="kategori" name="kategori" required>
                                <option value="layanan" @selected(old('kategori', $item->kategori ?? '') === 'layanan')>Layanan klinik</option>
                                <option value="kegiatan" @selected(old('kategori', $item->kategori ?? '') === 'kegiatan')>Kegiatan klinik</option>
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="judul">Judul *</label>
                            <input class="sikita-input" id="judul" name="judul" value="{{ old('judul', $item->judul ?? '') }}" maxlength="255" required>
                        </div>
                        <div class="dashboard-field admin-patient-address">
                            <label for="deskripsi">Deskripsi *</label>
                            <textarea class="sikita-input" id="deskripsi" name="deskripsi" maxlength="5000" required>{{ old('deskripsi', $item->deskripsi ?? '') }}</textarea>
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_mulai">Tanggal mulai</label>
                            <input class="sikita-input" id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $item->tanggal_mulai ?? '') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_selesai">Tanggal selesai</label>
                            <input class="sikita-input" id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', $item->tanggal_selesai ?? '') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="aktif">Status publikasi</label>
                            <select class="sikita-input" id="aktif" name="aktif" required>
                                <option value="1" @selected((string) old('aktif', $item->aktif ?? 1) === '1')>Aktif</option>
                                <option value="0" @selected((string) old('aktif', $item->aktif ?? 1) === '0')>Arsip</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.informasi-klinik.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">{{ $item ? 'Simpan perubahan' : 'Tambah informasi' }}</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
