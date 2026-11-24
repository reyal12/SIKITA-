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
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.dokter.create') }}">Buat akun dokter</a>
                    </div>

                    @if ($doctors->isEmpty())
                        <p class="dashboard-empty">Belum ada dokter terdaftar.</p>
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
                                            <td>{{ $doctor->email ?: 'Belum ada akun tertaut' }}</td>
                                            <td>{{ $doctor->nama }}</td>
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
                                                        <form method="POST" action="{{ route('admin.dokter.destroy', $doctor->id) }}" onsubmit="return confirm('Nonaktifkan dokter {{ $doctor->nama_lengkap }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="admin-patient-delete" type="submit">Nonaktifkan</button>
                                                        </form>
                                                    @endif
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
