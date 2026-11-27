@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-welcome-banner doctor-welcome-banner">
        <div>
            <h2>Selamat Datang, dr. {{ $user->name }}!</h2>
            <p>Pantau agenda pasien dan catatan pemeriksaan Anda dari satu tempat.</p>
        </div>
        <div class="patient-welcome-symbol" aria-hidden="true">
            <svg viewBox="0 0 120 120"><path d="M60 12v96M12 60h96"/><circle cx="60" cy="60" r="43"/></svg>
        </div>
    </section>

    @if ($doctor === null)
        <section class="patient-section-card patient-page-card">
            <h2 class="doctor-card-title">Profil dokter belum tersedia</h2>
            <p class="muted">Akun ini belum terhubung dengan data dokter. Hubungi administrator klinik.</p>
        </section>
    @else
        <section class="doctor-overview-grid" aria-label="Ringkasan dokter">
            <article class="patient-overview-card doctor-metric-card">
                <span class="patient-card-eyebrow">Agenda hari ini</span>
                <strong>{{ $stats['scheduled_today'] }}</strong>
                <span class="muted">janji temu</span>
            </article>
            <article class="patient-overview-card doctor-metric-card">
                <span class="patient-card-eyebrow">Pasien menunggu</span>
                <strong>{{ $stats['scheduled_total'] }}</strong>
                <span class="muted">janji aktif</span>
            </article>
            <article class="patient-overview-card doctor-metric-card">
                <span class="patient-card-eyebrow">Pemeriksaan selesai</span>
                <strong>{{ $stats['completed'] }}</strong>
                <span class="muted">kunjungan</span>
            </article>
        </section>

        <section class="patient-section-card patient-page-card">
            <div class="patient-section-heading">
                <div>
                    <span class="patient-card-eyebrow">Agenda klinik</span>
                    <h2>Janji Temu Pasien</h2>
                </div>
                <a class="patient-text-link" href="{{ route('dokter.pasien.index') }}">Lihat daftar pasien</a>
            </div>
            @if ($appointments->isEmpty())
                <p class="dashboard-empty">Belum ada janji temu pasien.</p>
            @else
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table patient-history-table">
                        <thead>
                            <tr><th>Tanggal &amp; waktu</th><th>Antrean</th><th>Pasien</th><th>Keluhan</th><th>Status</th><th>Tindakan</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br><span class="muted">{{ substr($appointment->waktu_mulai, 0, 5) }}</span></td>
                                    <td>{{ $appointment->nomor_janji }}<br><span class="muted">No. {{ $appointment->nomor_antrean }}</span></td>
                                    <td><strong>{{ $appointment->nama_pasien }}</strong><br><span class="muted">{{ $appointment->nomor_pasien }}</span></td>
                                    <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                    <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                                    <td>
                                        @if ($appointment->status === 'terjadwal' && $appointment->rekam_medis_id === null)
                                            <a class="patient-button patient-button--primary" href="{{ route('dokter.pemeriksaan.create', $appointment->id) }}">Isi pemeriksaan</a>
                                        @elseif ($appointment->rekam_medis_id !== null)
                                            <a class="patient-button patient-button--light" href="{{ route('dokter.pasien.history', $appointment->pasien_id) }}">Riwayat pasien</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif
@endsection
