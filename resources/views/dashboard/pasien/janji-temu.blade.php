@extends('dashboard.pasien.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Atur kunjungan</span>
                <h2>Janji Temu</h2>
            </div>
        </div>
        <div class="patient-appointment-section-grid">
            <div>
                <h3 class="patient-subheading">Buat janji temu baru</h3>
                @if ($doctors->isEmpty())
                    <p class="dashboard-empty">Belum ada dokter aktif yang dapat dipilih.</p>
                @else
                    <form class="dashboard-form" method="POST" action="{{ route('pasien.janji-temu.store') }}" id="appointment-form">
                        @csrf
                        <div class="dashboard-field">
                            <label for="doctor_id">Dokter</label>
                            <select class="sikita-input" id="doctor_id" name="doctor_id" required>
                                <option value="">Pilih dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                        {{ $doctor->nama_lengkap }} &bull; {{ $doctor->nama_spesialis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-form-grid">
                            <div class="dashboard-field">
                                <label for="appointment_date">Tanggal kunjungan</label>
                                <input class="sikita-input" id="appointment_date" name="appointment_date" type="date" min="{{ today()->toDateString() }}" max="{{ today()->addDays(90)->toDateString() }}" value="{{ old('appointment_date') }}" required>
                            </div>
                            <div class="dashboard-field">
                                <label for="start_time">Waktu praktik</label>
                                <select class="sikita-input" id="start_time" name="start_time" required disabled>
                                    <option value="">Pilih dokter dan tanggal terlebih dahulu</option>
                                </select>
                            </div>
                        </div>
                        <p class="field-hint" id="slot-message" aria-live="polite">Waktu yang tersedia akan ditampilkan setelah dokter dan tanggal dipilih.</p>
                        <div class="dashboard-field">
                            <label for="chief_complaint">Keluhan utama (opsional)</label>
                            <textarea class="sikita-input" id="chief_complaint" name="chief_complaint" maxlength="2000">{{ old('chief_complaint') }}</textarea>
                        </div>
                        <button class="sikita-btn-primer" type="submit">Ambil nomor antrean</button>
                    </form>
                @endif
            </div>
            <div>
                <h3 class="patient-subheading">Jadwal dokter</h3>
                @if ($doctorSchedules->isEmpty())
                    <p class="dashboard-empty">Jadwal praktik belum tersedia.</p>
                @else
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead>
                                <tr><th>Hari</th><th>Dokter &amp; spesialis</th><th>Jam</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($doctorSchedules as $schedule)
                                    <tr>
                                        <td>{{ ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$schedule->hari_dalam_minggu] ?? '—' }}</td>
                                        <td>{{ $schedule->nama_dokter }}<br><span class="muted">{{ $schedule->nama_spesialis }}</span></td>
                                        <td>{{ substr($schedule->waktu_mulai, 0, 5) }}–{{ substr($schedule->waktu_selesai, 0, 5) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Pendaftaran Anda</span>
                <h2>Semua Janji Temu</h2>
            </div>
        </div>
        @if ($appointments->isEmpty())
            <p class="dashboard-empty">Belum ada janji temu.</p>
        @else
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr><th>Tanggal &amp; waktu</th><th>Nomor antrean</th><th>Dokter</th><th>Keluhan</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}<br>{{ substr($appointment->waktu_mulai, 0, 5) }}</td>
                                <td>{{ $appointment->nomor_janji }}<br><span class="muted">No. {{ $appointment->nomor_antrean }}</span></td>
                                <td>{{ $appointment->nama_dokter }}<br><span class="muted">{{ $appointment->nama_spesialis }}</span></td>
                                <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ['terjadwal' => 'Terjadwal', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'][$appointment->status] ?? $appointment->status }}</span></td>
                                <td>
                                    @if ($appointment->status === 'terjadwal')
                                        <form method="POST" action="{{ route('pasien.janji-temu.cancel', $appointment->id) }}" onsubmit="return confirm('Batalkan janji temu ini?')">
                                            @csrf
                                            <button class="patient-button patient-button--light" type="submit">Batalkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <script>
        const doctorField = document.getElementById('doctor_id');
        const dateField = document.getElementById('appointment_date');
        const timeField = document.getElementById('start_time');
        const slotMessage = document.getElementById('slot-message');
        let slotRequest = 0;

        async function loadAvailableTimes() {
            const requestNumber = ++slotRequest;
            timeField.replaceChildren(new Option('Memuat jadwal...', ''));
            timeField.disabled = true;

            if (!doctorField.value || !dateField.value) {
                timeField.replaceChildren(new Option('Pilih dokter dan tanggal terlebih dahulu', ''));
                slotMessage.textContent = 'Waktu yang tersedia akan ditampilkan setelah dokter dan tanggal dipilih.';
                return;
            }

            try {
                const query = new URLSearchParams({
                    doctor_id: doctorField.value,
                    appointment_date: dateField.value,
                });
                const response = await fetch(`{{ route('pasien.jadwal-tersedia') }}?${query}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Jadwal tidak dapat dimuat. Periksa tanggal dan dokter yang dipilih.');
                }

                const data = await response.json();

                if (requestNumber !== slotRequest) {
                    return;
                }

                if (data.slots.length === 0) {
                    timeField.replaceChildren(new Option('Tidak ada waktu tersedia', ''));
                    slotMessage.textContent = 'Tidak ada jadwal kosong pada tanggal ini. Silakan pilih tanggal lain.';
                    return;
                }

                timeField.replaceChildren(new Option('Pilih waktu', ''));
                data.slots.forEach((time) => timeField.add(new Option(time, time)));
                timeField.disabled = false;
                slotMessage.textContent = `${data.slots.length} waktu tersedia. Nomor antrean dibuat setelah janji disimpan.`;
            } catch (error) {
                if (requestNumber === slotRequest) {
                    timeField.replaceChildren(new Option('Jadwal tidak tersedia', ''));
                    slotMessage.textContent = error.message;
                }
            }
        }

        if (doctorField && dateField && timeField && slotMessage) {
            doctorField.addEventListener('change', loadAvailableTimes);
            dateField.addEventListener('change', loadAvailableTimes);

            if (doctorField.value && dateField.value) {
                loadAvailableTimes();
            }
        }
    </script>
@endsection
