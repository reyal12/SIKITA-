<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Obat &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'obat'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Data Obat'])
            <div class="admin-page-content">
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading">
                        <div>
                            <h2>Daftar obat</h2>
                            <p class="muted">Informasi obat hanya dapat dilihat oleh admin.</p>
                        </div>
                    </div>

                    <form class="admin-filter-form admin-registration-filter" method="GET" action="{{ route('admin.obat.index') }}">
                        <div class="dashboard-field">
                            <label for="search">Cari nama obat</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100">
                        </div>
                        <button class="sikita-btn-primer" type="submit">Cari</button>
                    </form>

                    @if ($medicines->isEmpty())
                        <p class="dashboard-empty">Belum ada obat yang sesuai pencarian.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Nama obat</th><th>Stok</th><th>Harga</th><th>Satuan</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($medicines as $medicine)
                                        <tr>
                                            <td>{{ $medicine->nama }}</td>
                                            <td>{{ $medicine->stok }}</td>
                                            <td>Rp {{ number_format((float) $medicine->harga_satuan, 0, ',', '.') }}</td>
                                            <td>{{ $medicine->satuan }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $medicines->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
