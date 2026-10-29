<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="dashboard-shell">
        <header class="dashboard-header">
            <div class="dashboard-brand">
                <div class="dashboard-brand-mark">S</div>
                <div>
                    <h1>SIKITA</h1>
                    <p>Panel Administrator Klinik</p>
                </div>
            </div>
            <div class="dashboard-header-actions">
                <span class="muted">{{ $user->name }} &bull; {{ $user->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="sikita-btn-outline" type="submit">Keluar</button>
                </form>
            </div>
        </header>

        @if (session('sukses'))
            <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="dashboard-alert dashboard-alert--error" role="alert">
                <strong>Periksa kembali data tagihan.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="dashboard-grid dashboard-grid--stats" aria-label="Ringkasan klinik">
            <article class="sikita-card dashboard-stat">
                <span class="muted">Kunjungan hari ini</span>
                <strong>{{ $stats['appointments_today'] }}</strong>
            </article>
            <article class="sikita-card dashboard-stat">
                <span class="muted">Pendaftaran aktif</span>
                <strong>{{ $stats['appointments_scheduled'] }}</strong>
            </article>
            <article class="sikita-card dashboard-stat">
                <span class="muted">Pasien terdaftar</span>
                <strong>{{ $stats['patients'] }}</strong>
            </article>
            <article class="sikita-card dashboard-stat">
                <span class="muted">Tagihan menunggu</span>
                <strong>{{ $stats['unpaid_payments'] }}</strong>
            </article>
        </section>

        <section class="sikita-card dashboard-card">
            <div class="section-heading">
                <div>
                    <h2>Data pendaftaran</h2>
                    <p class="muted">100 janji temu terbaru dari seluruh pasien.</p>
                </div>
            </div>
            @if ($appointments->isEmpty())
                <p class="dashboard-empty">Belum ada pendaftaran pasien.</p>
            @else
                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr><th>Tanggal &amp; waktu</th><th>Antrean</th><th>Pasien</th><th>Dokter</th><th>Keluhan</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br>{{ substr($appointment->waktu_mulai, 0, 5) }}</td>
                                    <td>{{ $appointment->nomor_janji }}<br><span class="muted">No. {{ $appointment->nomor_antrean }}</span></td>
                                    <td>{{ $appointment->nama_pasien }}</td>
                                    <td>{{ $appointment->nama_dokter }}</td>
                                    <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                    <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="dashboard-grid dashboard-grid--two">
            <article class="sikita-card dashboard-card">
                <div class="section-heading">
                    <div>
                        <h2>Catat tagihan</h2>
                        <p class="muted">Pencatatan biaya layanan dan status pembayaran.</p>
                    </div>
                </div>
                @if ($patients->isEmpty())
                    <p class="dashboard-empty">Belum ada data pasien untuk ditagih.</p>
                @else
                    <form class="dashboard-form" method="POST" action="{{ route('admin.pembayaran.store') }}">
                        @csrf
                        <div class="dashboard-field">
                            <label for="patient_id">Pasien</label>
                            <select class="sikita-input" id="patient_id" name="patient_id" required>
                                <option value="">Pilih pasien</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                        {{ $patient->nama_lengkap }} &bull; {{ $patient->nomor_pasien }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="appointment_id">Janji temu (opsional)</label>
                            <select class="sikita-input" id="appointment_id" name="appointment_id">
                                <option value="">Tidak ditautkan</option>
                                @foreach ($paymentAppointments as $appointment)
                                    <option value="{{ $appointment->id }}" data-patient-id="{{ $appointment->pasien_id }}" @selected(old('appointment_id') == $appointment->id)>
                                        {{ $appointment->nomor_janji }} &bull; {{ $appointment->nama_pasien }} &bull; {{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-form-grid">
                            <div class="dashboard-field">
                                <label for="amount">Jumlah (Rp)</label>
                                <input class="sikita-input" id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="payment_method">Metode pembayaran</label>
                                <select class="sikita-input" id="payment_method" name="payment_method" required>
                                    <option value="">Pilih metode</option>
                                    <option value="tunai" @selected(old('payment_method') === 'tunai')>Tunai</option>
                                    <option value="transfer_bank" @selected(old('payment_method') === 'transfer_bank')>Transfer bank</option>
                                    <option value="kartu" @selected(old('payment_method') === 'kartu')>Kartu</option>
                                    <option value="dompet_digital" @selected(old('payment_method') === 'dompet_digital')>Dompet digital</option>
                                </select>
                            </div>
                        </div>
                        <div class="dashboard-field">
                            <label for="status">Status</label>
                            <select class="sikita-input" id="status" name="status" required>
                                <option value="menunggu" @selected(old('status', 'menunggu') === 'menunggu')>Menunggu</option>
                                <option value="dibayar" @selected(old('status') === 'dibayar')>Dibayar</option>
                                <option value="gagal" @selected(old('status') === 'gagal')>Gagal</option>
                                <option value="dikembalikan" @selected(old('status') === 'dikembalikan')>Dikembalikan</option>
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="notes">Catatan (opsional)</label>
                            <textarea class="sikita-input" id="notes" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Simpan tagihan</button>
                    </form>
                @endif
            </article>

            <article class="sikita-card dashboard-card">
                <div class="section-heading">
                    <div>
                        <h2>Riwayat pembayaran</h2>
                        <p class="muted">100 transaksi terbaru.</p>
                    </div>
                </div>
                @if ($payments->isEmpty())
                    <p class="dashboard-empty">Belum ada pembayaran.</p>
                @else
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead>
                                <tr><th>Tagihan</th><th>Pasien</th><th>Jumlah</th><th>Metode</th><th>Status</th><th>Perbarui</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->nomor_pembayaran }}<br><span class="muted">{{ $payment->nomor_janji ?: 'Tanpa janji temu' }}</span></td>
                                        <td>{{ $payment->nama_pasien }}</td>
                                        <td>Rp{{ number_format((float) $payment->jumlah, 0, ',', '.') }}</td>
                                        <td>{{ ['tunai' => 'Tunai', 'transfer_bank' => 'Transfer bank', 'kartu' => 'Kartu', 'dompet_digital' => 'Dompet digital'][$payment->metode_pembayaran] ?? $payment->metode_pembayaran }}</td>
                                        <td><span class="dashboard-badge dashboard-badge--{{ $payment->status }}">{{ ['menunggu' => 'Menunggu', 'dibayar' => 'Dibayar', 'gagal' => 'Gagal', 'dikembalikan' => 'Dikembalikan'][$payment->status] ?? $payment->status }}</span></td>
                                        <td>
                                            <form class="inline-actions" method="POST" action="{{ route('admin.pembayaran.status', $payment->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select class="sikita-input" name="status" aria-label="Status pembayaran {{ $payment->nomor_pembayaran }}">
                                                    <option value="menunggu" @selected($payment->status === 'menunggu')>Menunggu</option>
                                                    <option value="dibayar" @selected($payment->status === 'dibayar')>Dibayar</option>
                                                    <option value="gagal" @selected($payment->status === 'gagal')>Gagal</option>
                                                    <option value="dikembalikan" @selected($payment->status === 'dikembalikan')>Dikembalikan</option>
                                                </select>
                                                <button class="sikita-btn-outline" type="submit">Simpan</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    </main>

    <script>
        const patientField = document.getElementById('patient_id');
        const appointmentField = document.getElementById('appointment_id');
        if (patientField && appointmentField) {
            const appointmentOptions = Array.from(appointmentField.options).slice(1);

            function filterPatientAppointments() {
                const selectedAppointment = appointmentField.value;

                appointmentOptions.forEach((option) => {
                    option.hidden = patientField.value !== '' && option.dataset.patientId !== patientField.value;
                });

                if (selectedAppointment && appointmentField.selectedOptions[0].hidden) {
                    appointmentField.value = '';
                }
            }

            patientField.addEventListener('change', filterPatientAppointments);
            filterPatientAppointments();
        }
    </script>
</body>
</html>
