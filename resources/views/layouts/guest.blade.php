<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Autentikasi' }} - SIKITA</title>

    <!-- Vite Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--warna-latar); color: var(--warna-teks); font-family: var(--font-sans); min-height: 100vh; display: flex; flex-direction: column;">
    <main style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
        <div style="width: 100%; max-width: 440px;">
            <!-- Header Brand SIKITA -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 16px; background-color: var(--warna-primer); color: #ffffff; margin-bottom: 0.85rem; box-shadow: 0 4px 10px rgba(118, 147, 130, 0.35);">
                    <!-- Ikon Medis/Klinik -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20M2 12h20"/>
                    </svg>
                </div>
                <h1 style="font-size: 1.85rem; font-weight: 700; margin: 0; letter-spacing: -0.02em; color: var(--warna-teks);">SIKITA</h1>
                <p style="font-size: 0.9rem; color: var(--warna-teks-muted); margin: 0.35rem 0 0 0;">Sistem Informasi Klinik Terintegrasi</p>
            </div>

            <!-- Kartu Form -->
            <div class="sikita-card" style="padding: 2rem 2.25rem;">
                <!-- Pesan Sukses -->
                @if (session('sukses') || session('status'))
                    <div style="background-color: var(--warna-sukses-latar); border: 1px solid var(--warna-primer); color: var(--warna-sukses); padding: 0.85rem 1rem; border-radius: 12px; font-size: 0.88rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>{{ session('sukses') ?? session('status') }}</span>
                    </div>
                @endif

                <!-- Pesan Error Umum -->
                @if ($errors->any())
                    <div style="background-color: var(--warna-bahaya-latar); border: 1px solid rgba(185, 74, 72, 0.4); color: var(--warna-bahaya); padding: 0.85rem 1rem; border-radius: 12px; font-size: 0.88rem; margin-bottom: 1.5rem;">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">Terdapat kesalahan pada input Anda:</div>
                        <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer Hak Cipta -->
            <div style="text-align: center; margin-top: 2rem; font-size: 0.825rem; color: var(--warna-teks-muted);">
                &copy; {{ date('Y') }} SIKITA &bull; Klinik Sehat Terpadu
            </div>
        </div>
    </main>
</body>
</html>
