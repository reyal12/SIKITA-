<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Klinik &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'informasi-klinik'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Informasi Klinik'])
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
                            <h2>Layanan dan kegiatan klinik</h2>
                            <p class="muted">Perbarui informasi layanan dan kegiatan yang diselenggarakan klinik.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.informasi-klinik.create') }}">Tambah informasi</a>
                    </div>
                    @if ($information->isEmpty())
                        <p class="dashboard-empty">Belum ada informasi layanan atau kegiatan.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>Jenis</th><th>Judul / Deskripsi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    @foreach ($information as $item)
                                        <tr>
                                            <td><span class="dashboard-badge">{{ ucfirst($item->kategori) }}</span></td>
                                            <td><strong>{{ $item->judul }}</strong><br><span class="muted">{{ $item->deskripsi }}</span></td>
                                            <td>
                                                @if ($item->tanggal_mulai || $item->tanggal_selesai)
                                                    {{ $item->tanggal_mulai ? \Illuminate\Support\Carbon::parse($item->tanggal_mulai)->format('d M Y') : '—' }}
                                                    @if ($item->tanggal_selesai)
                                                        – {{ \Illuminate\Support\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}
                                                    @endif
                                                @else
                                                    Tidak ditentukan
                                                @endif
                                            </td>
                                            <td><span class="dashboard-badge dashboard-badge--{{ $item->aktif ? 'dibayar' : 'dibatalkan' }}">{{ $item->aktif ? 'Aktif' : 'Arsip' }}</span></td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.informasi-klinik.edit', $item->id) }}">Edit</a>
                                                    <form method="POST" action="{{ route('admin.informasi-klinik.destroy', $item->id) }}" onsubmit="return confirm('Hapus informasi {{ $item->judul }}?')">
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
                        <div class="admin-patient-pagination">{{ $information->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
