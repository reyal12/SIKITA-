@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Atur waktu layanan</span>
                <h2>Jadwal Praktik</h2>
            </div>
            <span class="dashboard-badge">{{ $schedules->count() }} jadwal</span>
        </div>
        <p class="doctor-page-intro">Pilih rentang hari praktik, misalnya Minggu sampai Selasa. Kosongkan hari selesai jika hanya praktik satu hari.</p>

        <div class="patient-appointment-section-grid">
            <div>
                <h3 class="patient-subheading">Tambah jadwal baru</h3>
                <form class="dashboard-form doctor-schedule-form" method="POST" action="{{ route('dokter.jadwal-praktik.store') }}">
                    @csrf
                    <div class="dashboard-form-grid">
                        <div class="dashboard-field">
                            <label for="weekday_start">Hari mulai</label>
                            <select class="sikita-input" id="weekday_start" name="weekday_start" required>
                                <option value="">Pilih hari mulai</option>
                                @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dayIndex => $dayName)
                                    <option value="{{ $dayIndex }}" @selected((string) old('weekday_start') === (string) $dayIndex)>{{ $dayName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field">
                            <label for="weekday_end">Hari selesai (opsional)</label>
                            <select class="sikita-input" id="weekday_end" name="weekday_end">
                                <option value="">Satu hari saja</option>
                            @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dayIndex => $dayName)
                                    <option value="{{ $dayIndex }}" @selected((string) old('weekday_end') === (string) $dayIndex)>{{ $dayName }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    <p class="muted doctor-schedule-range-hint">Jam, durasi, dan kapasitas berlaku untuk setiap hari dalam rentang tersebut.</p>
                    <div class="dashboard-form-grid">
                        <div class="dashboard-field">
                            <label for="start_time">Mulai</label>
                            <input class="sikita-input" id="start_time" name="start_time" type="time" value="{{ old('start_time') }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="end_time">Selesai</label>
                            <input class="sikita-input" id="end_time" name="end_time" type="time" value="{{ old('end_time') }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="duration">Durasi per pasien (menit)</label>
                            <input class="sikita-input" id="duration" name="duration" type="number" min="5" max="240" value="{{ old('duration', 15) }}" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="max_patients">Kapasitas pasien</label>
                            <input class="sikita-input" id="max_patients" name="max_patients" type="number" min="1" max="100" value="{{ old('max_patients', 20) }}" required>
                        </div>
                    </div>
                    <button class="patient-button patient-button--primary" type="submit">Simpan jadwal</button>
                </form>
            </div>

            <div>
                <h3 class="patient-subheading">Jadwal tersedia</h3>
                @if ($schedules->isEmpty())
                    <div class="doctor-empty-state">
                        <strong>Belum ada jadwal praktik</strong>
                        <span>Tambahkan jadwal agar pasien dapat membuat janji temu.</span>
                    </div>
                @else
                    <div class="doctor-schedule-list">
                        @foreach ($schedules as $schedule)
                            <details class="doctor-schedule-card doctor-schedule-item">
                                <summary class="doctor-schedule-summary">
                                    <span class="doctor-schedule-details">
                                        <span class="patient-card-eyebrow">{{ ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$schedule->hari_dalam_minggu] ?? '—' }}</span>
                                        <strong>{{ substr($schedule->waktu_mulai, 0, 5) }}–{{ substr($schedule->waktu_selesai, 0, 5) }}</strong>
                                        <span class="muted">{{ $schedule->durasi_janji_menit }} menit per pasien &bull; Maks. {{ $schedule->maksimal_pasien }}</span>
                                    </span>
                                    <span class="doctor-medicine-toggle-label">Edit</span>
                                </summary>
                                <div class="doctor-schedule-edit">
                                    <form class="dashboard-form" method="POST" action="{{ route('dokter.jadwal-praktik.update', $schedule->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="dashboard-field">
                                            <label for="weekday-{{ $schedule->id }}">Hari</label>
                                            <select class="sikita-input" id="weekday-{{ $schedule->id }}" name="weekday" required>
                                                @foreach (['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dayIndex => $dayName)
                                                    <option value="{{ $dayIndex }}" @selected($schedule->hari_dalam_minggu === $dayIndex)>{{ $dayName }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="dashboard-form-grid">
                                            <div class="dashboard-field">
                                                <label for="start_time-{{ $schedule->id }}">Mulai</label>
                                                <input class="sikita-input" id="start_time-{{ $schedule->id }}" name="start_time" type="time" value="{{ substr($schedule->waktu_mulai, 0, 5) }}" required>
                                            </div>
                                            <div class="dashboard-field">
                                                <label for="end_time-{{ $schedule->id }}">Selesai</label>
                                                <input class="sikita-input" id="end_time-{{ $schedule->id }}" name="end_time" type="time" value="{{ substr($schedule->waktu_selesai, 0, 5) }}" required>
                                            </div>
                                            <div class="dashboard-field">
                                                <label for="duration-{{ $schedule->id }}">Durasi per pasien (menit)</label>
                                                <input class="sikita-input" id="duration-{{ $schedule->id }}" name="duration" type="number" min="5" max="240" value="{{ $schedule->durasi_janji_menit }}" required>
                                            </div>
                                            <div class="dashboard-field">
                                                <label for="max_patients-{{ $schedule->id }}">Kapasitas pasien</label>
                                                <input class="sikita-input" id="max_patients-{{ $schedule->id }}" name="max_patients" type="number" min="1" max="100" value="{{ $schedule->maksimal_pasien }}" required>
                                            </div>
                                        </div>
                                        <div class="doctor-schedule-actions">
                                            <button class="patient-button patient-button--primary" type="submit">Simpan perubahan</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('dokter.jadwal-praktik.destroy', $schedule->id) }}" onsubmit="return confirm('Hapus jadwal praktik ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="patient-button patient-button--light" type="submit">Hapus jadwal</button>
                                    </form>
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
