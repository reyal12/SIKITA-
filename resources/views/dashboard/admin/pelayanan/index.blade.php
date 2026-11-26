<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pantau Pelayanan &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pemantauan'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Pendaftaran & Pelayanan'])
            <div class="admin-page-content">
                @include('dashboard.admin.pemantauan.partials.tabs', ['activeTab' => 'pelayanan'])
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading">
                        <div>
                            <h2>Hasil pemeriksaan</h2>
                            <p class="muted">Rekam medis dan resep hanya dapat dilihat oleh admin.</p>
                        </div>
                    </div>
                    <form class="admin-filter-form" method="GET" action="{{ route('admin.pemantauan.pelayanan.index') }}">
                        <div class="dashboard-field">
                            <label for="start_date">Dari tanggal</label>
                            <input class="sikita-input" id="start_date" name="start_date" type="date" value="{{ $startDate }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="end_date">Sampai tanggal</label>
                            <input class="sikita-input" id="end_date" name="end_date" type="date" value="{{ $endDate }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="doctor_id">Dokter</label>
                            <select class="sikita-input" id="doctor_id" name="doctor_id">
                                <option value="">Semua dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) $doctorId === (string) $doctor->id)>{{ $doctor->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field admin-filter-search">
                            <label for="search">Nama pasien</label>
                            <input class="sikita-input" id="search" name="search" value="{{ $search }}" maxlength="100">
                        </div>
                        <button class="sikita-btn-primer" type="submit">Terapkan filter</button>
                    </form>

                    @if ($records->isEmpty())
                        <p class="dashboard-empty">Belum ada data pelayanan pada filter yang dipilih.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Diagnosis</th>
                                        <th>Keluhan</th>
                                        <th>Rencana perawatan</th>
                                        <th>Tanda vital</th>
                                        <th>Resep obat</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $record)
                                        <tr>
                                            <td>{{ \Illuminate\Support\Carbon::parse($record->dicatat_pada)->format('d M Y H:i') }}</td>
                                            <td><strong>{{ $record->nama_pasien }}</strong><br><span class="muted">{{ $record->nomor_pasien }}</span></td>
                                            <td>{{ $record->nama_dokter }}</td>
                                            <td>{{ $record->diagnosis }}</td>
                                            <td>{{ $record->keluhan_utama ?: '—' }}</td>
                                            <td>{{ $record->rencana_perawatan ?: '—' }}</td>
                                            <td>
                                                {{ $record->tekanan_sistolik !== null && $record->tekanan_diastolik !== null ? $record->tekanan_sistolik.'/'.$record->tekanan_diastolik.' mmHg' : '—' }}
                                                @if ($record->denyut_jantung !== null)
                                                    <br>{{ $record->denyut_jantung }} bpm
                                                @endif
                                                @if ($record->suhu_tubuh !== null)
                                                    <br>{{ $record->suhu_tubuh }} °C
                                                @endif
                                                @if ($record->tinggi_badan_cm !== null)
                                                    <br>{{ $record->tinggi_badan_cm }} cm
                                                @endif
                                                @if ($record->berat_badan_kg !== null)
                                                    <br>{{ $record->berat_badan_kg }} kg
                                                @endif
                                            </td>
                                            <td>
                                                @forelse ($prescriptionsByRecord->get($record->id, collect())->groupBy('nomor_resep') as $prescriptionNumber => $items)
                                                    <div class="service-prescription">
                                                        <strong>{{ $prescriptionNumber }}</strong>
                                                        <ul>
                                                            @foreach ($items as $item)
                                                                <li>
                                                                    {{ $item->nama_obat }} — {{ $item->dosis }}, {{ $item->frekuensi }}
                                                                    @if ($item->durasi_hari)
                                                                        selama {{ $item->durasi_hari }} hari
                                                                    @endif
                                                                    @if ($item->petunjuk_pemakaian)
                                                                        ({{ $item->petunjuk_pemakaian }})
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @empty
                                                    —
                                                @endforelse
                                            </td>
                                            <td><a class="sikita-btn-outline" href="{{ route('admin.pemantauan.pelayanan.show', $record->id) }}">Lihat</a></td>
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
