<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $medicine ? 'Edit Obat' : 'Tambah Obat' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'obat'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => $medicine ? 'Edit Obat' : 'Tambah Obat'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data obat.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ $medicine ? route('admin.obat.update', $medicine->id) : route('admin.obat.store') }}">
                    @csrf
                    @if ($medicine)
                        @method('PUT')
                    @endif
                    <div class="section-heading">
                        <div>
                            <h2>Informasi obat</h2>
                            <p class="muted">Obat yang sudah digunakan pada resep tidak dapat dihapus; nonaktifkan untuk menghentikan penggunaannya.</p>
                        </div>
                    </div>
                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="kode">Kode obat *</label>
                            <input class="sikita-input" id="kode" name="kode" value="{{ old('kode', $medicine->kode ?? '') }}" maxlength="30" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="nama">Nama obat *</label>
                            <input class="sikita-input" id="nama" name="nama" value="{{ old('nama', $medicine->nama ?? '') }}" maxlength="255" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="kategori">Kategori</label>
                            <input class="sikita-input" id="kategori" name="kategori" value="{{ old('kategori', $medicine->kategori ?? '') }}" maxlength="255">
                        </div>
                        <div class="dashboard-field">
                            <label for="satuan">Satuan *</label>
                            <input class="sikita-input" id="satuan" name="satuan" value="{{ old('satuan', $medicine->satuan ?? '') }}" maxlength="30" placeholder="tablet, botol, kapsul" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="stok">Stok tersedia *</label>
                            <input class="sikita-input" id="stok" name="stok" type="number" min="0" value="{{ old('stok', $medicine->stok ?? 0) }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="stok_minimum">Batas stok minimum *</label>
                            <input class="sikita-input" id="stok_minimum" name="stok_minimum" type="number" min="0" value="{{ old('stok_minimum', $medicine->stok_minimum ?? 0) }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="harga_satuan">Harga satuan (Rp) *</label>
                            <input class="sikita-input" id="harga_satuan" name="harga_satuan" type="number" min="0" step="0.01" value="{{ old('harga_satuan', $medicine->harga_satuan ?? 0) }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_kedaluwarsa">Tanggal kedaluwarsa</label>
                            <input class="sikita-input" id="tanggal_kedaluwarsa" name="tanggal_kedaluwarsa" type="date" value="{{ old('tanggal_kedaluwarsa', $medicine->tanggal_kedaluwarsa ?? '') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="aktif">Status</label>
                            <select class="sikita-input" id="aktif" name="aktif" required>
                                <option value="1" @selected((string) old('aktif', $medicine->aktif ?? 1) === '1')>Aktif</option>
                                <option value="0" @selected((string) old('aktif', $medicine->aktif ?? 1) === '0')>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.obat.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">{{ $medicine ? 'Simpan perubahan' : 'Tambah obat' }}</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
