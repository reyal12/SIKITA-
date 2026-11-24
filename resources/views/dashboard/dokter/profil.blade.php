@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Informasi akun</span>
                <h2>Profil Dokter</h2>
            </div>
            <span class="dashboard-badge">{{ $doctor->nomor_dokter }}</span>
        </div>
        <p class="doctor-page-intro">Perbarui nama, nomor telepon, dan foto profil yang tampil di dashboard.</p>

        <form class="dashboard-form doctor-profile-form" method="POST" action="{{ route('dokter.profil.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="doctor-profile-photo-section">
                @if ($doctor->foto_profil)
                    <img class="doctor-profile-photo" src="{{ asset('storage/'.$doctor->foto_profil) }}" alt="Foto profil {{ $user->name }}">
                @else
                    <span class="doctor-profile-photo doctor-profile-photo--empty" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    </span>
                @endif
                <div>
                    <strong>Foto profil</strong>
                    <p class="muted">JPG, PNG, atau WebP. Ukuran maksimal 2 MB.</p>
                    <div class="dashboard-field">
                        <label for="photo">Pilih foto</label>
                        <input class="sikita-input doctor-photo-input" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                    </div>
                    @if ($doctor->foto_profil)
                        <label class="doctor-remove-photo">
                            <input type="checkbox" name="remove_photo" value="1">
                            Hapus foto profil saat ini
                        </label>
                    @endif
                </div>
            </div>

            <div class="dashboard-form-grid">
                <div class="dashboard-field">
                    <label for="name">Nama lengkap</label>
                    <input class="sikita-input" id="name" name="name" value="{{ old('name', $doctor->nama_lengkap) }}" maxlength="255" required>
                </div>
                <div class="dashboard-field">
                    <label for="email">Email</label>
                    <input class="sikita-input" id="email" value="{{ $user->email }}" disabled>
                </div>
                <div class="dashboard-field">
                    <label for="phone">Nomor telepon</label>
                    <input class="sikita-input" id="phone" name="phone" type="tel" value="{{ old('phone', $doctor->telepon) }}" maxlength="30">
                </div>
                <div class="dashboard-field">
                    <label for="specialty">Spesialisasi</label>
                    <input class="sikita-input" id="specialty" value="{{ $doctor->nama_spesialis }}" disabled>
                </div>
                <div class="dashboard-field">
                    <label for="license_number">Nomor izin praktik</label>
                    <input class="sikita-input" id="license_number" value="{{ $doctor->nomor_izin_praktik }}" disabled>
                </div>
            </div>
            <button class="patient-button patient-button--primary" type="submit">Simpan Profil</button>
        </form>
    </section>
@endsection
