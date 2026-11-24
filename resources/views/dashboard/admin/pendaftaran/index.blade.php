<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Pasien &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pendaftaran'])

        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Pendaftaran Pasien'])

            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Tindakan tidak dapat diselesaikan.</strong>
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
                            <p class="muted">Kelola profil pasien dan lihat dokter pada janji temu terakhir.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.pendaftaran.create') }}">
                            Daftarkan pasien
                        </a>
                    </div>

                    <form class="admin-filter-form admin-registration-filter" method="GET" action="{{ route('admin.pendaftaran.index') }}">
                        <div class="dashboard-field">
                            <label for="search">Cari pasien</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, nomor pasien, email">
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_janji">Tanggal pemeriksaan</label>
                            <input class="sikita-input" id="tanggal_janji" name="tanggal_janji" type="date" value="{{ $filters['tanggal_janji'] ?? '' }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="status_janji">Status janji temu</label>
                            <select class="sikita-input" id="status_janji" name="status_janji">
                                <option value="">Semua status</option>
                                <option value="terjadwal" @selected(($filters['status_janji'] ?? '') === 'terjadwal')>Terjadwal</option>
                                <option value="dikonfirmasi" @selected(($filters['status_janji'] ?? '') === 'dikonfirmasi')>Dikonfirmasi</option>
                                <option value="selesai" @selected(($filters['status_janji'] ?? '') === 'selesai')>Selesai</option>
                                <option value="dibatalkan" @selected(($filters['status_janji'] ?? '') === 'dibatalkan')>Dibatalkan</option>
                                <option value="tidak_hadir" @selected(($filters['status_janji'] ?? '') === 'tidak_hadir')>Tidak hadir</option>
                            </select>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Pantau pendaftaran</button>
                    </form>

                    @if ($patients->isEmpty())
                        <p class="dashboard-empty">Tidak ada pasien atau pendaftaran yang sesuai filter.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>No. Pasien</th>
                                        <th>Nama pasien</th>
                                        <th>Kontak</th>
                                        <th>Dokter</th>
                                        <th>Janji temu terakhir</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($patients as $patient)
                                        <tr>
                                            <td>{{ $patient->nomor_pasien }}</td>
                                            <td>
                                                <strong>{{ $patient->nama_lengkap }}</strong>
                                                @if ($patient->nomor_identitas)
                                                    <br><span class="muted">{{ $patient->nomor_identitas }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $patient->email ?: '—' }}<br>
                                                <span class="muted">{{ $patient->telepon ?: 'Telepon belum diisi' }}</span>
                                            </td>
                                            <td>{{ $patient->nama_dokter ?: 'Belum ada janji temu' }}</td>
                                            <td>
                                                @if ($patient->tanggal_janji)
                                                    {{ \Illuminate\Support\Carbon::parse($patient->tanggal_janji)->format('d M Y') }}
                                                    @if ($patient->waktu_mulai)
                                                        <br><span class="muted">{{ substr($patient->waktu_mulai, 0, 5) }}</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                <div class="admin-patient-actions">
                                                    <a class="sikita-btn-outline" href="{{ route('admin.pendaftaran.edit', $patient->id) }}">
                                                        Edit
                                                    </a>
                                                    <form
                                                        method="POST"
                                                        action="{{ route('admin.pendaftaran.destroy', $patient->id) }}"
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

                        <div class="admin-patient-pagination">
                            {{ $patients->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
