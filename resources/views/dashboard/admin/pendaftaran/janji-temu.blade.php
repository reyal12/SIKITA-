<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Jadwal Janji Temu &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pendaftaran'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Ubah Jadwal Janji Temu'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali jadwal yang dipilih.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ route('admin.pendaftaran.janji-temu.update', $appointment->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="section-heading">
                        <div>
                            <h2>{{ $appointment->nomor_janji }} &bull; {{ $appointment->nama_pasien }}</h2>
                            <p class="muted">Pilih dokter dan slot praktik baru. Kuota jadwal akan diperiksa sebelum perubahan disimpan.</p>
                        </div>
                    </div>

                    @if ($doctors->isEmpty())
                        <div class="admin-schedule-notice" role="status">Belum ada dokter aktif yang bisa dipilih.</div>
                    @endif

                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="doctor_id">Dokter *</label>
                            <select class="sikita-input" id="doctor_id" name="doctor_id" required>
                                <option value="">Pilih dokter</option>
                                @foreach ($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}"
                                        @selected((string) old('doctor_id', $appointment->dokter_id) === (string) $doctor->id)
                                    >
                                        {{ $doctor->nama_lengkap }} &bull; {{ $doctor->nama_spesialis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="appointment_date">Tanggal janji temu *</label>
                            <input
                                class="sikita-input"
                                id="appointment_date"
                                name="appointment_date"
                                type="date"
                                value="{{ old('appointment_date', $appointment->tanggal_janji) }}"
                                min="{{ today()->toDateString() }}"
                                max="{{ now()->addDays(90)->toDateString() }}"
                                required
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="start_time">Jam praktik *</label>
                            <select class="sikita-input" id="start_time" name="start_time" required>
                                <option value="">Memuat jadwal...</option>
                            </select>
                            <span class="field-hint" id="schedule-message" role="status"></span>
                        </div>
                        <div class="dashboard-field admin-patient-address">
                            <label for="chief_complaint">Keluhan utama</label>
                            <textarea class="sikita-input" id="chief_complaint" name="chief_complaint" maxlength="2000">{{ old('chief_complaint', $appointment->keluhan_utama) }}</textarea>
                        </div>
                    </div>

                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.pendaftaran.edit', $appointment->pasien_id) }}">Kembali ke pasien</a>
                        <button class="sikita-btn-primer" id="save-appointment" type="submit" disabled>Simpan jadwal</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const doctorField = document.getElementById('doctor_id');
        const appointmentDateField = document.getElementById('appointment_date');
        const appointmentTimeField = document.getElementById('start_time');
        const scheduleMessage = document.getElementById('schedule-message');
        const saveButton = document.getElementById('save-appointment');
        const appointmentId = @json($appointment->id);
        const savedAppointmentTime = @json(old('start_time', substr($appointment->waktu_mulai, 0, 5)));
        let scheduleRequest;

        async function loadAvailableTimes() {
            const doctorId = doctorField.value;
            const appointmentDate = appointmentDateField.value;

            scheduleRequest?.abort();
            appointmentTimeField.replaceChildren(new Option('Memuat jadwal...', ''));
            appointmentTimeField.disabled = !doctorId || !appointmentDate;
            saveButton.disabled = true;
            scheduleMessage.textContent = '';

            if (!doctorId || !appointmentDate) {
                appointmentTimeField.replaceChildren(new Option('Pilih dokter dan tanggal terlebih dahulu', ''));
                return;
            }

            scheduleRequest = new AbortController();
            const query = new URLSearchParams({
                doctor_id: doctorId,
                appointment_date: appointmentDate,
                appointment_id: appointmentId,
            });

            try {
                const response = await fetch(`{{ route('admin.jadwal-tersedia') }}?${query}`, {
                    headers: { Accept: 'application/json' },
                    signal: scheduleRequest.signal,
                });

                if (!response.ok) {
                    throw new Error('Jadwal dokter tidak dapat dimuat. Silakan coba lagi.');
                }

                const data = await response.json();
                appointmentTimeField.replaceChildren(new Option('Pilih jam praktik', ''));

                data.slots.forEach((time) => {
                    const option = new Option(time, time);
                    option.selected = time === savedAppointmentTime;
                    appointmentTimeField.add(option);
                });

                appointmentTimeField.disabled = data.slots.length === 0;
                saveButton.disabled = data.slots.length === 0;
                scheduleMessage.textContent = data.slots.length === 0
                    ? 'Tidak ada slot tersedia pada tanggal ini.'
                    : `${data.slots.length} slot tersedia.`;
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                appointmentTimeField.replaceChildren(new Option('Jadwal tidak tersedia', ''));
                appointmentTimeField.disabled = true;
                scheduleMessage.textContent = error.message;
            }
        }

        doctorField.addEventListener('change', loadAvailableTimes);
        appointmentDateField.addEventListener('change', loadAvailableTimes);
        loadAvailableTimes();
    </script>
</body>
</html>
