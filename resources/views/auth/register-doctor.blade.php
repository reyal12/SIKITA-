@extends('layouts.guest', ['title' => 'Pendaftaran Dokter'])

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--warna-teks);">Daftar Akun Dokter</h2>
        <p style="font-size: 0.875rem; color: var(--warna-teks-muted); margin: 0.25rem 0 0 0;">Akun dokter aktif setelah pendaftaran berhasil.</p>
    </div>

    <form method="POST" action="{{ route('register.dokter.store') }}">
        @csrf
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="name">Nama lengkap</label>
            <input class="sikita-input" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="email">Email</label>
            <input class="sikita-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="phone">Nomor telepon (opsional)</label>
            <input class="sikita-input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel">
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="license_number">Nomor izin praktik</label>
            <input class="sikita-input" id="license_number" name="license_number" value="{{ old('license_number') }}" required>
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="specialty">Spesialisasi</label>
            <input class="sikita-input" id="specialty" name="specialty" value="{{ old('specialty') }}" placeholder="Contoh: Dokter Umum" required>
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.1rem;">
            <label for="password">Kata sandi</label>
            <input class="sikita-input" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
        </div>
        <div class="dashboard-field" style="margin-bottom: 1.5rem;">
            <label for="password_confirmation">Ulangi kata sandi</label>
            <input class="sikita-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
        </div>
        <button class="sikita-btn-primer" type="submit">Daftar sebagai dokter</button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.2rem; border-top: 1px solid var(--warna-netral); font-size: 0.875rem; color: var(--warna-teks-muted);">
        Sudah memiliki akun dokter?
        <a href="{{ route('login.dokter') }}" style="color: var(--warna-primer); font-weight: 600; text-decoration: none;">Masuk di sini</a>
    </div>
@endsection
