@extends('dashboard.pasien.layout')

@section('content')
    <section class="patient-welcome-banner">
        <div>
            <span class="patient-welcome-kicker">{{ today()->format('d.m.Y') }} &bull; SIKITA Care</span>
            <h2>Selamat Datang, {{ $patient->nama_lengkap ?? $user->name }}!</h2>
            <p>Semoga hari Anda sehat. Pantau jadwal dan informasi kesehatan Anda di sini.</p>
        </div>
        <div class="patient-welcome-symbol" aria-hidden="true">
            <svg viewBox="0 0 120 120"><path d="M60 12v96M12 60h96"/><circle cx="60" cy="60" r="43"/></svg>
        </div>
    </section>

    <section class="patient-overview-grid patient-overview-grid--dashboard" aria-label="Ringkasan dashboard">
        <article class="sikita-card patient-overview-card">
            <div class="patient-card-heading">
                <div>
                    <span class="patient-card-eyebrow">Agenda Anda</span>
                    <h2>Janji Temu Aktif</h2>
                </div>
                @if ($activeAppointment)
                    <span class="dashboard-badge dashboard-badge--terjadwal">Terjadwal</span>
                @endif
            </div>
            @if ($activeAppointment)
                <div class="patient-appointment-doctor">
                    <span class="patient-doctor-avatar" aria-hidden="true">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($activeAppointment->nama_dokter, 0, 1)) }}
                    </span>
                    <div>
                        <strong>{{ $activeAppointment->nama_dokter }}</strong>
                        <span>{{ $activeAppointment->nama_spesialis }}</span>
                    </div>
                </div>
                <div class="patient-appointment-meta">
                    <div>
                        <span class="patient-meta-label">Jadwal</span>
                        <strong>{{ \Illuminate\Support\Carbon::parse($activeAppointment->tanggal_janji)->format('d M Y') }}</strong>
                    </div>
                    <div>
                        <span class="patient-meta-label">Waktu praktik</span>
                        <strong>{{ substr($activeAppointment->waktu_mulai, 0, 5) }} WIB</strong>
                    </div>
                    <div>
                        <span class="patient-meta-label">Nomor antrean</span>
                        <strong>#{{ $activeAppointment->nomor_antrean }}</strong>
                    </div>
                </div>
                <div class="patient-card-actions">
                    <form method="POST" action="{{ route('pasien.janji-temu.cancel', $activeAppointment->id) }}" onsubmit="return confirm('Batalkan janji temu ini?')">
                        @csrf
                        <button class="patient-button patient-button--light" type="submit">Batalkan</button>
                    </form>
                    <a class="patient-button patient-button--primary" href="{{ route('pasien.janji-temu') }}">Lihat Detail</a>
                </div>
            @else
                <div class="patient-empty-appointment">
                    <p>Belum ada janji temu aktif. Buat janji dengan dokter untuk memulai.</p>
                    <a class="patient-button patient-button--primary" href="{{ route('pasien.janji-temu') }}">Buat Janji Temu</a>
                </div>
            @endif
        </article>

        <article class="sikita-card patient-overview-card patient-billing-card">
            <div class="patient-card-heading">
                <div>
                    <span class="patient-card-eyebrow">Informasi tagihan</span>
                    <h2>Pembayaran &amp; Tagihan</h2>
                </div>
                <span class="patient-billing-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                </span>
            </div>
            @if ($unpaidPayment)
                <span class="patient-meta-label">Tagihan belum dibayar</span>
                <strong class="patient-billing-amount">Rp{{ number_format((float) $unpaidPayment->jumlah, 0, ',', '.') }}</strong>
                <div class="patient-billing-details">
                    <span>{{ $unpaidPayment->nomor_pembayaran }}</span>
                    <span class="dashboard-badge dashboard-badge--menunggu">Menunggu Pembayaran</span>
                </div>
                <p class="patient-billing-note">Silakan lakukan pembayaran melalui metode yang tertera atau hubungi admin klinik. Status diperbarui setelah pembayaran diterima.</p>
                <a class="patient-button patient-button--primary patient-button--wide" href="{{ route('pasien.pembayaran') }}">Bayar Sekarang</a>
            @elseif ($latestPayment)
                <span class="patient-meta-label">Status tagihan terakhir</span>
                <strong class="patient-billing-amount">Rp{{ number_format((float) $latestPayment->jumlah, 0, ',', '.') }}</strong>
                <div class="patient-billing-details">
                    <span>{{ $latestPayment->nomor_pembayaran }}</span>
                    <span class="dashboard-badge dashboard-badge--{{ $latestPayment->status }}">{{ ['dibayar' => 'Lunas', 'gagal' => 'Gagal', 'dikembalikan' => 'Dikembalikan'][$latestPayment->status] ?? $latestPayment->status }}</span>
                </div>
                <p class="patient-billing-note">Tidak ada tagihan yang menunggu pembayaran.</p>
                <a class="patient-button patient-button--light patient-button--wide" href="{{ route('pasien.pembayaran') }}">Lihat Riwayat Pembayaran</a>
            @else
                <div class="patient-billing-empty">
                    <strong>Belum ada tagihan</strong>
                    <p>Informasi biaya layanan akan muncul setelah kunjungan Anda dicatat.</p>
                </div>
                <a class="patient-button patient-button--light patient-button--wide" href="{{ route('pasien.pembayaran') }}">Lihat Pembayaran</a>
            @endif
        </article>
    </section>

    <section class="sikita-card patient-section-card patient-journey-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Perjalanan kesehatan</span>
                <h2>Riwayat Kunjungan &amp; Medis Terakhir</h2>
            </div>
            <a class="patient-text-link" href="{{ route('pasien.riwayat-medis') }}">Lihat Riwayat</a>
        </div>
        @if ($journeyAppointments->isEmpty())
            <p class="dashboard-empty">Riwayat kunjungan Anda akan tampil di sini.</p>
        @else
            <div class="dashboard-table-wrap">
                <table class="dashboard-table patient-history-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Dokter</th>
                            <th>Hasil Pemeriksaan</th>
                            <th>Resep</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($journeyAppointments as $appointment)
                            @php($record = $medicalRecordsByAppointment->get($appointment->id))
                            @php($prescriptionItems = $record ? $prescriptionsByMedicalRecord->get($record->id, collect()) : collect())
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br><span class="muted">{{ substr($appointment->waktu_mulai, 0, 5) }}</span></td>
                                <td><strong>{{ $appointment->nama_dokter }}</strong><br><span class="muted">{{ $appointment->nama_spesialis }}</span></td>
                                <td>{{ $record?->diagnosis ?? 'Belum tersedia' }}</td>
                                <td>
                                    @if ($prescriptionItems->isNotEmpty())
                                        <strong>{{ $prescriptionItems->pluck('nomor_resep')->unique()->join(', ') }}</strong><br>
                                        {{ $prescriptionItems->pluck('nama_obat')->unique()->join(', ') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
