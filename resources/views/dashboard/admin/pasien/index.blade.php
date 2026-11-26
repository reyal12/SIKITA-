<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Pasien &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pasien'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Kelola Pasien'])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Tindakan pasien tidak dapat diselesaikan.</strong>
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
                            <h2>Data pasien</h2>
                            <p class="muted">Cari dan kelola profil pasien klinik.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.pasien.create') }}">Tambah pasien</a>
                    </div>

                    <form class="admin-filter-form admin-registration-filter" method="GET" action="{{ route('admin.pasien.index') }}">
                        <div class="dashboard-field">
                            <label for="search">Cari nama, NIK, atau nomor pasien</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100">
                        </div>
                        <button class="sikita-btn-primer" type="submit">Cari</button>
                    </form>

                    @if ($patients->isEmpty())
                        <p class="dashboard-empty">Belum ada pasien yang sesuai pencarian.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>No. pasien</th>
                                        <th>NIK</th>
                                        <th>Nama lengkap</th>
                                        <th>Telepon</th>
                                        <th>Email</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patients as $patient)
                                        <tr>
                                            <td>{{ $patient->nomor_pasien }}</td>
                                            <td>{{ $patient->nomor_identitas ?: '—' }}</td>
                                            <td>{{ $patient->nama_lengkap }}</td>
                                            <td>{{ $patient->telepon ?: '—' }}</td>
                                            <td>{{ $patient->email ?: '—' }}</td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.pasien.show', $patient->id) }}">Detail</a>
                                                    <a class="sikita-btn-outline" href="{{ route('admin.pasien.edit', $patient->id) }}">Edit</a>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.pasien.destroy', $patient->id) }}"
                                                        onsubmit="return confirm('Hapus data pasien {{ $patient->nama_lengkap }}?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="admin-patient-delete" type="submit">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $patients->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
