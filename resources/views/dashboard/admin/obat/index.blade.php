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
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Tindakan obat tidak dapat diselesaikan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="dashboard-grid dashboard-grid--stats admin-dashboard-section">
                    <article class="sikita-card dashboard-stat"><span class="muted">Obat stok minimum</span><strong>{{ $lowStockCount }}</strong></article>
                    <article class="sikita-card dashboard-stat"><span class="muted">Obat kedaluwarsa</span><strong>{{ $expiredCount }}</strong></article>
                </section>

                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading admin-patient-toolbar">
                        <div>
                            <h2>Daftar obat</h2>
                            <p class="muted">Informasi, stok, harga, dan tanggal kedaluwarsa obat klinik.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.obat.create') }}">Tambah obat</a>
                    </div>
                    @if ($medicines->isEmpty())
                        <p class="dashboard-empty">Belum ada data obat.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Kode / Nama</th><th>Kategori</th><th>Stok</th><th>Harga</th><th>Kedaluwarsa</th><th>Status</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($medicines as $medicine)
                                        @php
                                            $isExpired = $medicine->tanggal_kedaluwarsa && $medicine->tanggal_kedaluwarsa < today()->toDateString();
                                            $isLowStock = $medicine->stok <= $medicine->stok_minimum;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $medicine->nama }}</strong><br><span class="muted">{{ $medicine->kode }}</span></td>
                                            <td>{{ $medicine->kategori ?: '—' }}</td>
                                            <td>
                                                {{ $medicine->stok }} {{ $medicine->satuan }}
                                                @if ($isLowStock)
                                                    <br><span class="dashboard-badge dashboard-badge--menunggu">Stok minimum</span>
                                                @endif
                                            </td>
                                            <td>Rp{{ number_format((float) $medicine->harga_satuan, 0, ',', '.') }}</td>
                                            <td>
                                                @if ($medicine->tanggal_kedaluwarsa)
                                                    {{ \Illuminate\Support\Carbon::parse($medicine->tanggal_kedaluwarsa)->format('d M Y') }}
                                                    @if ($isExpired)
                                                        <br><span class="dashboard-badge dashboard-badge--dibatalkan">Kedaluwarsa</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="dashboard-badge dashboard-badge--{{ $medicine->aktif ? 'dibayar' : 'dibatalkan' }}">{{ $medicine->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.obat.edit', $medicine->id) }}">Edit</a>
                                                    <form method="POST" action="{{ route('admin.obat.destroy', $medicine->id) }}" onsubmit="return confirm('Hapus obat {{ $medicine->nama }}?')">
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
                        <div class="admin-patient-pagination">{{ $medicines->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
