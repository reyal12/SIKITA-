<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemantauan Pelayanan &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pelayanan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Pemantauan Pelayanan'])
            <div class="admin-page-content">
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading">
                        <div>
                            <h2>Riwayat pemeriksaan</h2>
                            <p class="muted">Data pemeriksaan, diagnosis, rencana perawatan, dan resep yang dicatat dokter.</p>
                        </div>
                    </div>
                    <form class="admin-filter-form" method="GET" action="{{ route('admin.pelayanan.index') }}">
                        <div class="dashboard-field">
                            <label for="start_date">Dari tanggal</label>
                            <input class="sikita-input" id="start_date" name="start_date" type="date" value="{{ $startDate }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="end_date">Sampai tanggal</label>
                            <input class="sikita-input" id="end_date" name="end_date" type="date" value="{{ $endDate }}" required>
                        </div>
                        <div class="dashboard-field admin-filter-search">
                            <label for="search">Pasien, dokter, atau diagnosis</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $search }}" maxlength="100">
                        </div>
                        <button class="sikita-btn-primer" type="submit">Terapkan filter</button>
                    </form>

                    @if ($records->isEmpty())
                        <p class="dashboard-empty">Tidak ada data pemeriksaan pada periode ini.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr><th>Tanggal</th><th>Pasien</th><th>Dokter</th><th>Keluhan / Diagnosis</th><th>Rencana perawatan / Resep</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $record)
                                        <tr>
                                            <td>
                                                {{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y') }}
                                                <br><span class="muted">{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('H:i') }}</span>
                                                @if ($record->nomor_janji)
                                                    <br><span class="muted">{{ $record->nomor_janji }}</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ $record->nama_pasien }}</strong><br><span class="muted">{{ $record->nomor_pasien }}</span></td>
                                            <td>{{ $record->nama_dokter }}</td>
                                            <td>
                                                <strong>{{ $record->diagnosis }}</strong>
                                                @if ($record->keluhan_utama)
                                                    <br><span class="muted">Keluhan: {{ $record->keluhan_utama }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $record->rencana_perawatan ?: '—' }}
                                                @foreach ($prescriptionsByRecord->get($record->id, collect())->groupBy('nomor_resep') as $prescriptionNumber => $items)
                                                    <div class="service-prescription">
                                                        <strong>Resep {{ $prescriptionNumber }}</strong>
                                                        <ul>
                                                            @foreach ($items as $item)
                                                                <li>{{ $item->nama_obat }} — {{ $item->dosis }}, {{ $item->frekuensi }} ({{ $item->jumlah }} unit)</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $records->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
