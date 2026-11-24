@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Janji temu {{ $appointment->nomor_janji }}</span>
                <h2>{{ $appointment->nama_pasien }}</h2>
                <p class="muted">{{ $appointment->nomor_pasien }} &bull; {{ \Illuminate\Support\Carbon::parse($appointment->tanggal_janji)->format('d M Y') }} &bull; {{ substr($appointment->waktu_mulai, 0, 5) }}</p>
            </div>
            <span class="dashboard-badge dashboard-badge--{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span>
        </div>

        @if ($record !== null)
            <div class="doctor-empty-state">
                <strong>Pemeriksaan sudah tersimpan</strong>
                <span>Diagnosis: {{ $record->diagnosis }}</span>
                <a class="patient-button patient-button--primary" href="{{ route('dokter.pasien.history', $appointment->pasien_id) }}">Lihat riwayat pasien</a>
            </div>
        @else
            <form class="dashboard-form doctor-examination-form" method="POST" action="{{ route('dokter.pemeriksaan.store', $appointment->id) }}">
                @csrf
                <div>
                    <span class="patient-card-eyebrow">Catatan klinis</span>
                    <h3 class="patient-subheading doctor-form-subheading">Form Pemeriksaan</h3>
                </div>

                <div class="dashboard-field">
                    <label for="chief_complaint">Keluhan</label>
                    <textarea class="sikita-input" id="chief_complaint" name="chief_complaint" maxlength="2000">{{ old('chief_complaint', $appointment->keluhan_utama) }}</textarea>
                </div>

                <fieldset class="doctor-fieldset">
                    <legend>Tanda vital</legend>
                    <div class="dashboard-form-grid">
                        <div class="dashboard-field">
                            <label for="systolic_blood_pressure">Tekanan sistolik (mmHg)</label>
                            <input class="sikita-input" id="systolic_blood_pressure" name="systolic_blood_pressure" type="number" min="30" max="300" value="{{ old('systolic_blood_pressure') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="diastolic_blood_pressure">Tekanan diastolik (mmHg)</label>
                            <input class="sikita-input" id="diastolic_blood_pressure" name="diastolic_blood_pressure" type="number" min="20" max="200" value="{{ old('diastolic_blood_pressure') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="heart_rate">Denyut jantung (bpm)</label>
                            <input class="sikita-input" id="heart_rate" name="heart_rate" type="number" min="20" max="250" value="{{ old('heart_rate') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="temperature">Suhu (°C)</label>
                            <input class="sikita-input" id="temperature" name="temperature" type="number" min="30" max="45" step="0.1" value="{{ old('temperature') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="height_cm">Tinggi badan (cm)</label>
                            <input class="sikita-input" id="height_cm" name="height_cm" type="number" min="20" max="250" step="0.1" value="{{ old('height_cm') }}">
                        </div>
                        <div class="dashboard-field">
                            <label for="weight_kg">Berat badan (kg)</label>
                            <input class="sikita-input" id="weight_kg" name="weight_kg" type="number" min="1" max="500" step="0.1" value="{{ old('weight_kg') }}">
                        </div>
                    </div>
                </fieldset>

                <div class="dashboard-field">
                    <label for="diagnosis">Diagnosis</label>
                    <textarea class="sikita-input" id="diagnosis" name="diagnosis" maxlength="2000" required>{{ old('diagnosis') }}</textarea>
                </div>
                <div class="dashboard-field">
                    <label for="treatment_plan">Tindakan / rencana perawatan</label>
                    <textarea class="sikita-input" id="treatment_plan" name="treatment_plan" maxlength="2000">{{ old('treatment_plan') }}</textarea>
                </div>
                <div class="dashboard-field">
                    <label for="notes">Catatan tambahan</label>
                    <textarea class="sikita-input" id="notes" name="notes" maxlength="2000">{{ old('notes') }}</textarea>
                </div>

                <fieldset class="doctor-fieldset">
                    <legend>Resep obat (opsional)</legend>
                    @if ($medicines->isEmpty())
                        <p class="muted">Belum ada obat aktif. Tambahkan obat melalui <a href="{{ route('obat.index') }}">Data Obat</a>.</p>
                    @else
                        <div id="prescription-items" class="dashboard-form"></div>
                        <button class="patient-button patient-button--light doctor-add-medicine" id="add-prescription" type="button">Tambah obat</button>
                        <template id="prescription-template">
                            <div class="prescription-item">
                                <div class="dashboard-form-grid">
                                    <div class="dashboard-field">
                                        <label>Obat</label>
                                        <select class="sikita-input" name="prescriptions[__INDEX__][medicine_id]" required>
                                            <option value="">Pilih obat</option>
                                            @foreach ($medicines as $medicine)
                                                <option value="{{ $medicine->id }}">{{ $medicine->nama }} &bull; stok {{ $medicine->stok }} {{ $medicine->satuan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="dashboard-field">
                                        <label>Dosis</label>
                                        <input class="sikita-input" name="prescriptions[__INDEX__][dosage]" maxlength="100" placeholder="Contoh: 500 mg" required>
                                    </div>
                                    <div class="dashboard-field">
                                        <label>Frekuensi</label>
                                        <input class="sikita-input" name="prescriptions[__INDEX__][frequency]" maxlength="100" placeholder="Contoh: 3 kali sehari" required>
                                    </div>
                                    <div class="dashboard-field">
                                        <label>Durasi (hari)</label>
                                        <input class="sikita-input" name="prescriptions[__INDEX__][duration_days]" type="number" min="1" max="365">
                                    </div>
                                    <div class="dashboard-field">
                                        <label>Jumlah</label>
                                        <input class="sikita-input" name="prescriptions[__INDEX__][quantity]" type="number" min="1" max="1000" required>
                                    </div>
                                    <div class="dashboard-field">
                                        <label>Petunjuk penggunaan</label>
                                        <input class="sikita-input" name="prescriptions[__INDEX__][instructions]" maxlength="1000" placeholder="Contoh: sesudah makan">
                                    </div>
                                </div>
                                <button class="patient-button patient-button--light" type="button" data-remove-prescription>Hapus obat ini</button>
                            </div>
                        </template>
                    @endif
                </fieldset>
                <button class="patient-button patient-button--primary" type="submit">Simpan pemeriksaan</button>
            </form>
        @endif
    </section>

    @if ($record === null && $medicines->isNotEmpty())
        <script>
            const prescriptionItems = document.getElementById('prescription-items');
            const prescriptionTemplate = document.getElementById('prescription-template');
            let prescriptionIndex = 0;

            document.getElementById('add-prescription').addEventListener('click', () => {
                const item = prescriptionTemplate.content.cloneNode(true);
                item.querySelectorAll('[name]').forEach((field) => {
                    field.name = field.name.replaceAll('__INDEX__', prescriptionIndex);
                });
                prescriptionItems.appendChild(item);
                prescriptionIndex += 1;
            });

            prescriptionItems.addEventListener('click', (event) => {
                if (event.target.matches('[data-remove-prescription]')) {
                    event.target.closest('.prescription-item').remove();
                }
            });
        </script>
    @endif
@endsection
