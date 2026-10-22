<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pasien &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--warna-latar); color: var(--warna-teks); font-family: var(--font-sans); min-height: 100vh; padding: 2.5rem 1rem;">
    <div style="max-width: 680px; margin: 0 auto;">
        <!-- Header Placeholder Dashboard -->
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background-color: var(--warna-primer); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.1rem;">
                    S
                </div>
                <div>
                    <h1 style="font-size: 1.35rem; font-weight: 700; margin: 0; color: var(--warna-teks);">SIKITA</h1>
                    <p style="font-size: 0.8rem; color: var(--warna-teks-muted); margin: 0;">Portal Pasien Terpadu</p>
                </div>
            </div>

            <!-- Tombol Logout POST + CSRF -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sikita-btn-outline" style="font-size: 0.85rem;">
                    Keluar (Logout)
                </button>
            </form>
        </div>

        <!-- Kartu Informasi Pengguna Placeholder -->
        <div class="sikita-card" style="padding: 2rem 2.25rem;">
            <div style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; background-color: var(--warna-sekunder); color: var(--warna-teks); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                Placeholder Dashboard Pasien
            </div>

            <h2 style="font-size: 1.5rem; font-weight: 700; margin: 0 0 0.5rem 0; color: var(--warna-teks);">
                Halo, {{ $user->name }}!
            </h2>
            <p style="font-size: 0.95rem; color: var(--warna-teks-muted); margin: 0 0 1.5rem 0;">
                Anda berhasil masuk ke sistem SIKITA dengan akun Pasien.
            </p>

            <div style="background-color: var(--warna-latar); border-radius: 12px; padding: 1.25rem; border: 1px solid var(--warna-netral); margin-bottom: 1.5rem;">
                <dl style="margin: 0; display: grid; grid-template-columns: 140px 1fr; row-gap: 0.75rem; font-size: 0.9rem;">
                    <dt style="font-weight: 600; color: var(--warna-teks-muted);">Nama Lengkap:</dt>
                    <dd style="margin: 0; font-weight: 600; color: var(--warna-teks);">{{ $user->name }}</dd>

                    <dt style="font-weight: 600; color: var(--warna-teks-muted);">Email:</dt>
                    <dd style="margin: 0; color: var(--warna-teks);">{{ $user->email }}</dd>

                    <dt style="font-weight: 600; color: var(--warna-teks-muted);">Peran Aktif:</dt>
                    <dd style="margin: 0;">
                        @foreach ($user->peran as $p)
                            <span style="display: inline-block; padding: 0.15rem 0.55rem; background-color: var(--warna-primer); color: #ffffff; border-radius: 6px; font-size: 0.78rem; font-weight: 600; margin-right: 0.35rem;">
                                {{ $p->nama }} ({{ $p->kode }})
                            </span>
                        @endforeach
                    </dd>
                </dl>
            </div>

            <p style="font-size: 0.85rem; color: var(--warna-teks-muted); margin: 0; font-style: italic;">
                Catatan: Ini adalah tampilan minimal placeholder. Layout dashboard master sesungguhnya akan dikembangkan oleh anggota tim lain.
            </p>
        </div>
    </div>
</body>
</html>
