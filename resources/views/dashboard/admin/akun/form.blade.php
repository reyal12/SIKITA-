<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Akun Pengguna &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'akun'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Buat Akun Pengguna'])
            <div class="admin-page-content">
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data akun.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="sikita-card dashboard-card admin-patient-form" method="POST" action="{{ route('admin.akun.store') }}">
                    @csrf
                    <div class="section-heading">
                        <div>
                            <h2>Informasi akun</h2>
                            <p class="muted">Pilih salah satu dari tiga peran. Profil pasien atau dokter dibuat bersama akun.</p>
                        </div>
                    </div>

                    <div class="dashboard-form-grid admin-patient-form-grid">
                        <div class="dashboard-field">
                            <label for="name">Nama lengkap *</label>
                            <input class="sikita-input" id="name" name="name" value="{{ old('name') }}" maxlength="255" autocomplete="name" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="email">Alamat email *</label>
                            <input class="sikita-input" id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="password">Kata sandi awal *</label>
                            <input class="sikita-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="password_confirmation">Ulangi kata sandi *</label>
                            <input class="sikita-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
                        </div>
                        <div class="dashboard-field">
                            <label for="role">Peran *</label>
                            <select class="sikita-input" id="role" name="role" required>
                                <option value="">Pilih peran</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->kode }}" @selected(old('role') === $role->kode)>{{ $role->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dashboard-field" data-doctor-field hidden>
                            <label for="specialty_id">Spesialisasi *</label>
                            <select class="sikita-input" id="specialty_id" name="specialty_id">
                                <option value="">Pilih spesialisasi</option>
                                @foreach ($specialties as $specialty)
                                    <option value="{{ $specialty->id }}" @selected((string) old('specialty_id') === (string) $specialty->id)>{{ $specialty->nama }}</option>
                                @endforeach
                            </select>
                            @if ($specialties->isEmpty())
                                <span class="field-hint">Tambahkan spesialisasi dari halaman Kelola Dokter terlebih dahulu.</span>
                            @endif
                        </div>
                        <div class="dashboard-field" data-doctor-field hidden>
                            <label for="license_number">Nomor izin praktik *</label>
                            <input class="sikita-input" id="license_number" name="license_number" value="{{ old('license_number') }}" maxlength="50">
                        </div>
                        <div class="dashboard-field" data-doctor-field hidden>
                            <label for="phone">Nomor telepon</label>
                            <input class="sikita-input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                        </div>
                    </div>

                    <div class="admin-patient-form-actions">
                        <a class="sikita-btn-outline" href="{{ route('admin.akun.index') }}">Kembali</a>
                        <button class="sikita-btn-primer" type="submit">Buat akun</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        const roleField = document.getElementById('role');
        const doctorFields = document.querySelectorAll('[data-doctor-field]');

        function updateDoctorFields() {
            const isDoctor = roleField.value === 'dokter';

            doctorFields.forEach((field) => {
                field.hidden = !isDoctor;
                const input = field.querySelector('input, select');
                input.required = isDoctor && input.id !== 'phone';
            });
        }

        roleField.addEventListener('change', updateDoctorFields);
        updateDoctorFields();
    </script>
</body>
</html>
