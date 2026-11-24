@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Kelola layanan</span>
                <h2>Daftar Pasien</h2>
            </div>
            <span class="dashboard-badge">{{ $patients->count() }} pasien</span>
        </div>
        <p class="doctor-page-intro">Pilih pasien untuk melihat riwayat pemeriksaan, tanda vital, diagnosis, dan resep.</p>

        @if ($patients->isEmpty())
            <div class="doctor-empty-state">
                <span class="doctor-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6M23 11h-6"/></svg>
                </span>
                <strong>Belum ada data pasien</strong>
                <span>Pasien akan muncul setelah Anda menyimpan hasil pemeriksaannya.</span>
            </div>
        @else
            <div class="dashboard-table-wrap">
                <table class="dashboard-table patient-history-table">
                    <thead>
                        <tr><th>Nomor pasien</th><th>Nama</th><th>Kontak</th><th>Pemeriksaan</th><th>Terakhir diperiksa</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr>
                                <td>{{ $patient->nomor_pasien }}</td>
                                <td><strong>{{ $patient->nama_lengkap }}</strong></td>
                                <td>{{ $patient->telepon ?: $patient->email ?: '—' }}</td>
                                <td>{{ $patient->jumlah_pemeriksaan }}</td>
                                <td>{{ $patient->pemeriksaan_terakhir ? \Illuminate\Support\Carbon::parse($patient->pemeriksaan_terakhir)->format('d M Y H:i') : 'Belum ada' }}</td>
                                <td><a class="patient-button patient-button--light" href="{{ route('dokter.pasien.history', $patient->id) }}">Lihat riwayat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
