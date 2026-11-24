<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $patient ? 'Edit Pasien' : 'Daftarkan Pasien' }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    @php
        $isEditing = $patient !== null;
        $formAction = $isEditing
            ? route('admin.pendaftaran.update', $patient->id)
            : route('admin.pendaftaran.store');
    @endphp

    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'pendaftaran'])

        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', [
                'pageTitle' => $isEditing ? 'Edit Data Pasien' : 'Pendaftaran Pasien',
            ])

            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data yang dimasukkan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ $formAction }}">
                    @csrf
                    @if ($isEditing)
                        @method('PUT')
                    @endif

                    <div class="section-heading">
                        <div>
                            <h2>{{ $isEditing ? 'Informasi pasien' : 'Data pasien baru' }}</h2>
                            <p class="muted">Isi informasi pasien. Kolom bertanda * wajib diisi.</p>
                        </div>
                    </div>

                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="nama_lengkap">Nama lengkap *</label>
                            <input
                                class="sikita-input"
                                id="nama_lengkap"
                                name="nama_lengkap"
                                value="{{ old('nama_lengkap', $patient->nama_lengkap ?? '') }}"
                                maxlength="255"
                                autocomplete="name"
                                required
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="nomor_identitas">Nomor identitas</label>
                            <input
                                class="sikita-input"
                                id="nomor_identitas"
                                name="nomor_identitas"
                                value="{{ old('nomor_identitas', $patient->nomor_identitas ?? '') }}"
                                maxlength="30"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="email">Email</label>
                            <input
                                class="sikita-input"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', $patient->email ?? '') }}"
                                maxlength="255"
                                autocomplete="email"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="telepon">Nomor telepon</label>
                            <input
                                class="sikita-input"
                                id="telepon"
                                name="telepon"
                                type="tel"
                                value="{{ old('telepon', $patient->telepon ?? '') }}"
                                maxlength="30"
                                autocomplete="tel"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="tanggal_lahir">Tanggal lahir</label>
                            <input
                                class="sikita-input"
                                id="tanggal_lahir"
                                name="tanggal_lahir"
                                type="date"
                                value="{{ old('tanggal_lahir', $patient->tanggal_lahir ?? '') }}"
                                max="{{ now()->toDateString() }}"
                            >
                        </div>
                        <div class="dashboard-field">
                            <label for="jenis_kelamin">Jenis kelamin</label>
                            <select class="sikita-input" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="laki-laki" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'laki-laki')>Laki-laki</option>
                                <option value="perempuan" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'perempuan')>Perempuan</option>
                                <option value="lainnya" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'lainnya')>Lainnya</option>
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="golongan_darah">Golongan darah</label>
                            <select class="sikita-input" id="golongan_darah" name="golongan_darah">
                                <option value="">Pilih golongan darah</option>
                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodType)
                                    <option value="{{ $bloodType }}" @selected(old('golongan_darah', $patient->golongan_darah ?? '') === $bloodType)>{{ $bloodType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field admin-patient-address">
                            <label for="alamat">Alamat</label>
                            <textarea class="sikita-input" id="alamat" name="alamat" maxlength="2000">{{ old('alamat', $patient->alamat ?? '') }}</textarea>
                        </div>
                    </div>

                    <section class="admin-appointment-fields" aria-labelledby="appointment-heading">
                        <div class="section-heading">
                            <div>
                                <h2 id="appointment-heading">{{ $isEditing ? 'Buat janji temu baru (opsional)' : 'Janji temu dokter (opsional)' }}</h2>
                                <p class="muted">
                                    {{ $isEditing
                                        ? 'Pilih dokter dan jadwal untuk menambahkan janji temu baru tanpa mengubah riwayat sebelumnya.'
                                        : 'Data pasien tetap dapat disimpan tanpa janji temu. Pilih dokter, tanggal, dan slot praktik untuk membuat janji temu.' }}
                                </p>
                            </div>
                        </div>

                        @if ($doctors->isEmpty())
                            <div class="admin-schedule-notice" role="status">
                                Belum ada dokter aktif. Pasien tetap dapat didaftarkan; janji temu dapat dibuat setelah dokter dan jadwal praktik tersedia.
                            </div>
                        @endif

                        <div class="dashboard-form-grid admin-patient-form-grid">
                            <div class="dashboard-field">
                                <label for="doctor_id">Dokter{{ $isEditing ? '' : ' *' }}</label>
                                <select
                                    class="sikita-input"
                                    id="doctor_id"
                                    name="doctor_id"
                                    @disabled($doctors->isEmpty())
                                >
                                    <option value="">Pilih dokter</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>
                                            {{ $doctor->nama_lengkap }} &bull; {{ $doctor->nama_spesialis }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="dashboard-field">
                                <label for="appointment_date">Tanggal janji temu{{ $isEditing ? '' : ' *' }}</label>
                                <input
                                    class="sikita-input"
                                    id="appointment_date"
                                    name="appointment_date"
                                    type="date"
                                    value="{{ old('appointment_date') }}"
                                    min="{{ today()->toDateString() }}"
                                    max="{{ now()->addDays(90)->toDateString() }}"
                                    @disabled($doctors->isEmpty())
                                >
                            </div>
                            <div class="dashboard-field">
                                <label for="start_time">Jam praktik{{ $isEditing ? '' : ' *' }}</label>
                                <select
                                    class="sikita-input"
                                    id="start_time"
                                    name="start_time"
                                    @disabled($doctors->isEmpty())
                                >
                                    <option value="">Pilih dokter dan tanggal terlebih dahulu</option>
                                </select>
                                <span class="field-hint" id="schedule-message" role="status"></span>
                            </div>
                            <div class="dashboard-field admin-patient-address">
                                <label for="chief_complaint">Keluhan utama</label>
                                <textarea
                                    class="sikita-input"
                                    id="chief_complaint"
                                    name="chief_complaint"
                                    maxlength="2000"
                                    @disabled($doctors->isEmpty())
                                >{{ old('chief_complaint') }}</textarea>
                            </div>
                        </div>
                    </section>

                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.pendaftaran.index') }}">Kembali</a>
                        <button
                            class="sikita-btn-primer"
                            type="submit"
                        >
                            {{ $isEditing ? 'Simpan perubahan' : 'Daftarkan pasien' }}
                        </button>
                    </div>
                </form>

                @if ($isEditing)
                    <section class="sikita-card dashboard-card admin-schedule-card">
                        <div class="section-heading">
                            <div>
                                <h2>Jadwal pasien</h2>
                                <p class="muted">Ubah jadwal atau batalkan janji temu yang masih terjadwal.</p>
                            </div>
                        </div>

                        @if ($appointments->isEmpty())
                            <p class="dashboard-empty">Pasien belum memiliki riwayat janji temu.</p>
                        @else
                            <div class="dashboard-table-wrap">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr><th>No. janji temu</th><th>Tanggal &amp; jam</th><th>Dokter</th><th>Keluhan</th><th>Status</th><th>Aksi</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($appointments as $appointment)
                                            <tr>
                                                <td>{{ $appointment->nomor_janji }}</td>
                                                <td>
                                                    {{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }}
                                                    <br><span class="muted">{{ substr($appointment->waktu_mulai, 0, 5) }}</span>
                                                </td>
                                                <td>{{ $appointment->nama_dokter }}</td>
                                                <td>{{ $appointment->keluhan_utama ?: '—' }}</td>
                                                <td><span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                                                <td>
                                                    @if ($appointment->status === 'terjadwal' && $appointment->tanggal_janji >= today()->toDateString())
                                                        <div class="admin-patient-actions">
                                                            <a class="sikita-btn-outline" href="{{ route('admin.pendaftaran.janji-temu.edit', $appointment->id) }}">Ubah jadwal</a>
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.pendaftaran.janji-temu.cancel', $appointment->id) }}"
                                                                onsubmit="return confirm('Batalkan janji temu {{ $appointment->nomor_janji }}?')"
                                                            >
                                                                @csrf
                                                                @method('PATCH')
                                                                <button class="admin-patient-delete" type="submit">Batalkan</button>
                                                            </form>
                                                        </div>
                                                    @else
                                                        <span class="muted">Tidak dapat diubah</span>
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
            </div>
        </main>
    </div>

    <script>
        const doctorField = document.getElementById('doctor_id');
        const appointmentDateField = document.getElementById('appointment_date');
        const appointmentTimeField = document.getElementById('start_time');
        const scheduleMessage = document.getElementById('schedule-message');
        const savedAppointmentTime = @json(old('start_time'));
        const appointmentId = @json($appointmentId ?? null);
        let scheduleRequest;

        async function loadAvailableTimes() {
            const doctorId = doctorField.value;
            const appointmentDate = appointmentDateField.value;
            const requiresAppointment = doctorId !== '';

            appointmentTimeField.replaceChildren(new Option('Memuat jadwal...', ''));
            appointmentTimeField.disabled = !doctorId || !appointmentDate;
            appointmentTimeField.required = requiresAppointment;
            appointmentDateField.required = requiresAppointment;
            scheduleMessage.textContent = '';
            scheduleRequest?.abort();

            if (!doctorId || !appointmentDate) {
                appointmentTimeField.replaceChildren(new Option('Pilih dokter dan tanggal terlebih dahulu', ''));
                return;
            }

            scheduleRequest = new AbortController();

            try {
                const query = new URLSearchParams({
                    doctor_id: doctorId,
                    appointment_date: appointmentDate,
                });
                if (appointmentId) {
                    query.set('appointment_id', appointmentId);
                }
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

        if (doctorField.value && appointmentDateField.value) {
            loadAvailableTimes();
        } else {
            appointmentTimeField.disabled = true;
            appointmentTimeField.required = false;
            appointmentDateField.required = false;
        }
    </script>
</body>
</html>
