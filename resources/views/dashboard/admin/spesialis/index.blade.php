<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spesialis &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'spesialis'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Data Spesialis'])
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
                            <h2>Daftar spesialis</h2>
                            <p class="muted">Data spesialis mengelompokkan dokter; jadwal dan kuota diatur per dokter.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.spesialis.create') }}">Tambah spesialis</a>
                    </div>
                    @if ($specialties->isEmpty())
                        <p class="dashboard-empty">Belum ada data spesialis.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>Kode</th><th>Nama spesialis</th><th>Deskripsi</th><th>Jumlah dokter</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    @foreach ($specialties as $specialty)
                                        <tr>
                                            <td>{{ $specialty->kode }}</td>
                                            <td><strong>{{ $specialty->nama }}</strong></td>
                                            <td>{{ $specialty->deskripsi ?: '—' }}</td>
                                            <td>{{ $specialty->jumlah_dokter }}</td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.spesialis.edit', $specialty->id) }}">Edit</a>
                                                    <form method="POST" action="{{ route('admin.spesialis.destroy', $specialty->id) }}" onsubmit="return confirm('Hapus spesialis {{ $specialty->nama }}?')">
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
                        <div class="admin-patient-pagination">{{ $specialties->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
