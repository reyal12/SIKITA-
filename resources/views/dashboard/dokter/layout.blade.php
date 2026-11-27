<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="patient-app-body">
    @php($hasDoctorRole = $user->punyaPeran('dokter'))
    <div class="patient-app-shell">
        <aside class="patient-sidebar" aria-label="Navigasi {{ $hasDoctorRole ? 'dokter' : 'admin' }}">
            <a class="patient-sidebar-brand" href="{{ route($hasDoctorRole ? 'dokter.dashboard' : 'admin.dashboard') }}">
                <span class="patient-sidebar-logo" aria-hidden="true">S</span>
                <span>SIKITA</span>
            </a>
            <p class="patient-sidebar-title">Menu Utama</p>
            <nav class="patient-sidebar-nav">
                @if ($hasDoctorRole)
                    <a class="{{ $activePage === 'dashboard' ? 'is-active' : '' }}" href="{{ route('dokter.dashboard') }}" @if ($activePage === 'dashboard') aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a class="{{ $activePage === 'jadwal-praktik' ? 'is-active' : '' }}" href="{{ route('dokter.jadwal-praktik.index') }}" @if ($activePage === 'jadwal-praktik') aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h2M14 14h2M8 18h2"/></svg>
                        <span>Jadwal Praktik</span>
                    </a>
                    <a class="{{ $activePage === 'pasien' ? 'is-active' : '' }}" href="{{ route('dokter.pasien.index') }}" @if ($activePage === 'pasien') aria-current="page" @endif>
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6M23 11h-6"/></svg>
                        <span>Daftar Pasien</span>
                    </a>
                @else
                    <a href="{{ route('admin.dashboard') }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                        <span>Dashboard Admin</span>
                    </a>
                @endif
                <a class="{{ $activePage === 'obat' ? 'is-active' : '' }}" href="{{ route('obat.index') }}" @if ($activePage === 'obat') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m10.5 3.5 10 10a4.24 4.24 0 0 1-6 6l-10-10a4.24 4.24 0 0 1 6-6ZM8 6l10 10"/></svg>
                    <span>Data Obat</span>
                </a>
            </nav>
            <div class="patient-sidebar-footer">
                <span class="patient-sidebar-footer-mark">+</span>
                <span>Layanan kesehatan,<br><strong>sepenuh hati</strong></span>
            </div>
        </aside>

        <div class="patient-main">
            <header class="patient-topbar">
                <div>
                    <p class="patient-topbar-eyebrow">{{ $hasDoctorRole ? 'Portal Dokter' : 'Portal Admin' }}</p>
                    <h1>{{ $pageTitle }}</h1>
                </div>
                <div class="patient-user">
                    @if ($hasDoctorRole)
                        <a class="patient-user-profile-link" href="{{ route('dokter.profil') }}" aria-label="Informasi akun dokter">
                            @if ($doctor?->foto_profil)
                                <img class="patient-user-avatar patient-user-photo" src="{{ asset('storage/'.$doctor->foto_profil) }}" alt="">
                            @else
                                <span class="patient-user-avatar" aria-hidden="true">
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                                </span>
                            @endif
                        </a>
                    @else
                        <span class="patient-user-avatar" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        </span>
                    @endif
                    <span class="patient-user-info">
                        <strong>{{ $user->name }}</strong>
                        <span class="patient-role-badge">{{ $hasDoctorRole ? 'Dokter' : 'Admin' }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="patient-logout-button" type="submit" aria-label="Keluar">
                            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>
                        </button>
                    </form>
                </div>
            </header>

            <main class="patient-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Periksa kembali data yang dimasukkan.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
