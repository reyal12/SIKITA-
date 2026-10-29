@extends('dashboard.pasien.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Tagihan layanan</span>
                <h2>Pembayaran</h2>
            </div>
        </div>
        @if ($payments->isEmpty())
            <p class="dashboard-empty">Belum ada tagihan.</p>
        @else
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr><th>Nomor tagihan</th><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $payment->nomor_pembayaran }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($payment->created_at)->format('d M Y') }}</td>
                                <td>Rp{{ number_format((float) $payment->jumlah, 0, ',', '.') }}</td>
                                <td>{{ ['tunai' => 'Tunai', 'transfer_bank' => 'Transfer bank', 'kartu' => 'Kartu', 'dompet_digital' => 'Dompet digital'][$payment->metode_pembayaran] ?? $payment->metode_pembayaran }}</td>
                                <td><span class="dashboard-badge dashboard-badge--{{ $payment->status }}">{{ ['menunggu' => 'Menunggu Pembayaran', 'dibayar' => 'Lunas', 'gagal' => 'Gagal', 'dikembalikan' => 'Dikembalikan'][$payment->status] ?? $payment->status }}</span></td>
                                <td>
                                    @if ($payment->status === 'menunggu')
                                        <span class="muted">Hubungi admin untuk pembayaran</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
