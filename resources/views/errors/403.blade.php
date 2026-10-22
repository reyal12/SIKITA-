<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--warna-latar); color: var(--warna-teks); font-family: var(--font-sans); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
    <div style="max-width: 480px; width: 100%; text-align: center;">
        <div class="sikita-card" style="padding: 2.5rem 2rem;">
            <!-- Ikon Akses Ditolak -->
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background-color: var(--warna-sekunder); color: var(--warna-primer-hover); margin-bottom: 1.25rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>

            <div style="display: inline-block; padding: 0.3rem 0.85rem; border-radius: 999px; background-color: rgba(185, 74, 72, 0.12); color: var(--warna-bahaya); font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem;">
                KODE KESALAHAN 403
            </div>

            <h1 style="font-size: 1.6rem; font-weight: 700; margin: 0 0 0.5rem 0; color: var(--warna-teks);">
                Akses Ditolak
            </h1>

            <p style="font-size: 0.95rem; color: var(--warna-teks-muted); line-height: 1.5; margin: 0 0 2rem 0;">
                {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman ini.' }}
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @auth
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="sikita-btn-primer">
                        Kembali ke Dashboard Saya
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sikita-btn-outline" style="width: 100%;">
                            Keluar dari Akun
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="sikita-btn-primer">
                        Masuk ke Akun
                    </a>
                @endauth
            </div>
        </div>

        <div style="margin-top: 1.5rem; font-size: 0.825rem; color: var(--warna-teks-muted);">
            SIKITA &bull; Sistem Informasi Klinik Terintegrasi
        </div>
    </div>
</body>
</html>
