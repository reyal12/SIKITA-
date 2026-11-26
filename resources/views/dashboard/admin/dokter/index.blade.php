<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Dokter &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'dokter'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Data Dokter'])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
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
                            <h2>Daftar dokter</h2>
                            <p class="muted">Kelola profil, akun, spesialisasi, jadwal praktik, dan kuota dokter.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.dokter.create') }}">Tambah dokter</a>
                    </div>

                    <form class="admin-filter-form" method="GET" action="{{ route('admin.dokter.index') }}">
                        <div class="dashboard-field">
                            <label for="search">Cari dokter</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau nomor dokter" maxlength="100">
                        </div>
                        <div class="dashboard-field">
                            <label for="specialty_id">Spesialisasi</label>
                            <select class="sikita-input" id="specialty_id" name="specialty_id">
                                <option value="">Semua spesialisasi</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" @selected((string) ($filters['specialty_id'] ?? '') === (string) $specialty->id)>{{ $specialty->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Cari</button>
                    </form>

                    <details class="sikita-card dashboard-card">
                        <summary>Kelola spesialisasi</summary>
                        <div class="dashboard-form-grid admin-patient-form-grid">
                            <form method="POST" action="{{ route('admin.dokter.spesialisasi.store') }}">
                                @csrf
                                <h3>Tambah spesialisasi</h3>
                                <div class="dashboard-field">
                                    <label for="new_specialty_code">Kode *</label>
                                    <input class="sikita-input" id="new_specialty_code" name="kode" value="{{ old('kode') }}" maxlength="20" required>
                                </div>
                                <div class="dashboard-field">
                                    <label for="new_specialty_name">Nama *</label>
                                    <input class="sikita-input" id="new_specialty_name" name="nama" value="{{ old('nama') }}" maxlength="255" required>
                                </div>
                                <div class="dashboard-field">
                                    <label for="new_specialty_description">Deskripsi</label>
                                    <textarea class="sikita-input" id="new_specialty_description" name="deskripsi" maxlength="2000">{{ old('deskripsi') }}</textarea>
                                </div>
                                <button class="sikita-btn-primer" type="submit">Tambah spesialisasi</button>
                            </form>
                            @forelse ($specialties as $specialty)
                                <div>
                                    <form method="POST" action="{{ route('admin.dokter.spesialisasi.update', $specialty->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <h3>{{ $specialty->nama }}</h3>
                                        <div class="dashboard-field">
                                            <label for="specialty_code_{{ $specialty->id }}">Kode</label>
                                            <input class="sikita-input" id="specialty_code_{{ $specialty->id }}" name="kode" value="{{ $specialty->kode }}" maxlength="20" required>
                                        </div>
                                        <div class="dashboard-field">
                                            <label for="specialty_name_{{ $specialty->id }}">Nama</label>
                                            <input class="sikita-input" id="specialty_name_{{ $specialty->id }}" name="nama" value="{{ $specialty->nama }}" maxlength="255" required>
                                        </div>
                                        <div class="dashboard-field">
                                            <label for="specialty_description_{{ $specialty->id }}">Deskripsi</label>
                                            <textarea class="sikita-input" id="specialty_description_{{ $specialty->id }}" name="deskripsi" maxlength="2000">{{ $specialty->deskripsi }}</textarea>
                                        </div>
                                        <button class="sikita-btn-outline" type="submit">Simpan perubahan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.dokter.spesialisasi.destroy', $specialty->id) }}" onsubmit="return confirm('Hapus spesialisasi {{ $specialty->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-patient-delete" type="submit">Hapus spesialisasi</button>
                                    </form>
                                </div>
                            @empty
                                <p class="dashboard-empty">Belum ada spesialisasi.</p>
                            @endforelse
                        </div>
                    </details>

                    @if ($doctors->isEmpty())
                        <p class="dashboard-empty">Tidak ada dokter yang sesuai dengan pencarian.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Nama dokter</th><th>Akun</th><th>Spesialis</th><th>No. izin praktik</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($doctors as $doctor)
                                        <tr>
                                            <td>{{ $doctor->nama_lengkap }}<br><span class="muted">{{ $doctor->nomor_dokter }}</span></td>
                                            <td>{{ $doctor->pengguna?->email ?: 'Belum ada akun tertaut' }}</td>
                                            <td>{{ $doctor->spesialis->nama }}</td>
                                            <td>{{ $doctor->nomor_izin_praktik }}</td>
                                            <td>
                                                <span class="dashboard-badge dashboard-badge--{{ $doctor->aktif ? 'dibayar' : 'dibatalkan' }}">
                                                    {{ $doctor->aktif ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.dokter.edit', $doctor->id) }}">Kelola</a>
                                                    @if ($doctor->aktif)
                                                        <form method="POST" action="{{ route('admin.dokter.deactivate', $doctor->id) }}" onsubmit="return confirm('Nonaktifkan dokter {{ $doctor->nama_lengkap }}?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button class="admin-patient-delete" type="submit">Nonaktifkan</button>
                                                        </form>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.dokter.destroy', $doctor->id) }}" onsubmit="return confirm('Hapus permanen data dokter {{ $doctor->nama_lengkap }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="admin-patient-delete" type="submit">Hapus permanen</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $doctors->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
