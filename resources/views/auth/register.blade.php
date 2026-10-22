@extends('layouts.guest', ['title' => 'Pendaftaran Pasien'])

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--warna-teks);">Daftar Akun Pasien</h2>
        <p style="font-size: 0.875rem; color: var(--warna-teks-muted); margin: 0.25rem 0 0 0;">Lengkapi data di bawah ini untuk membuat akun baru.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Input Nama Lengkap -->
        <div style="margin-bottom: 1.25rem;">
            <label for="name" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--warna-teks);">
                Nama Lengkap
            </label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="sikita-input"
                placeholder="cth. Budi Pratama"
            >
            @error('name')
                <p style="color: var(--warna-bahaya); font-size: 0.8rem; margin: 0.35rem 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Input Email -->
        <div style="margin-bottom: 1.25rem;">
            <label for="email" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--warna-teks);">
                Alamat Email
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="sikita-input"
                placeholder="nama@email.com"
            >
            @error('email')
                <p style="color: var(--warna-bahaya); font-size: 0.8rem; margin: 0.35rem 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Input Password -->
        <div style="margin-bottom: 1.25rem;">
            <label for="password" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--warna-teks);">
                Kata Sandi
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="sikita-input"
                placeholder="Minimal 8 karakter"
            >
            @error('password')
                <p style="color: var(--warna-bahaya); font-size: 0.8rem; margin: 0.35rem 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Input Konfirmasi Password -->
        <div style="margin-bottom: 1.5rem;">
            <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--warna-teks);">
                Ulangi Kata Sandi
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="sikita-input"
                placeholder="Ketik ulang kata sandi"
            >
            @error('password_confirmation')
                <p style="color: var(--warna-bahaya); font-size: 0.8rem; margin: 0.35rem 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tombol Daftar -->
        <button type="submit" class="sikita-btn-primer">
            Daftar Sekarang
        </button>
    </form>

    <!-- Tautan Kembali ke Login -->
    <div style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--warna-netral); font-size: 0.875rem; color: var(--warna-teks-muted);">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" style="color: var(--warna-primer); font-weight: 600; text-decoration: none; margin-left: 0.25rem;">
            Masuk di Sini
        </a>
    </div>
@endsection
