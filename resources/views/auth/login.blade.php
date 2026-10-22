@extends('layouts.guest', ['title' => 'Masuk'])

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--warna-teks);">Masuk ke Akun</h2>
        <p style="font-size: 0.875rem; color: var(--warna-teks-muted); margin: 0.25rem 0 0 0;">Silakan masukkan email dan kata sandi Anda.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                autofocus
                autocomplete="username"
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
                autocomplete="current-password"
                class="sikita-input"
                placeholder="••••••••"
            >
            @error('password')
                <p style="color: var(--warna-bahaya); font-size: 0.8rem; margin: 0.35rem 0 0 0;">{{ $message }}</p>
            @enderror
        </div>

        <!-- Opsi Ingat Saya -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <label style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; color: var(--warna-teks);">
                <input
                    type="checkbox"
                    name="remember"
                    id="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                    style="width: 16px; height: 16px; accent-color: var(--warna-primer); cursor: pointer;"
                >
                <span>Ingat saya di perangkat ini</span>
            </label>
        </div>

        <!-- Tombol Masuk -->
        <button type="submit" class="sikita-btn-primer">
            Masuk
        </button>
    </form>

    <!-- Tautan Pendaftaran Pasien -->
    <div style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--warna-netral); font-size: 0.875rem; color: var(--warna-teks-muted);">
        Belum memiliki akun pasien?
        <a href="{{ route('register') }}" style="color: var(--warna-primer); font-weight: 600; text-decoration: none; margin-left: 0.25rem;">
            Daftar Sekarang
        </a>
    </div>
@endsection
