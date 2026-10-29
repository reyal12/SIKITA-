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
    <div class="patient-app-shell">
        <aside class="patient-sidebar" aria-label="Navigasi pasien">
            <a class="patient-sidebar-brand" href="{{ route('pasien.dashboard') }}" aria-label="SIKITA, Dashboard Pasien">
                <span class="patient-sidebar-logo" aria-hidden="true">S</span>
                <span>SIKITA</span>
            </a>
            <p class="patient-sidebar-title">Menu Utama</p>
            <nav class="patient-sidebar-nav">
                <a class="{{ $activePage === 'dashboard' ? 'is-active' : '' }}" href="{{ route('pasien.dashboard') }}" @if ($activePage === 'dashboard') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a class="{{ $activePage === 'janji-temu' ? 'is-active' : '' }}" href="{{ route('pasien.janji-temu') }}" @if ($activePage === 'janji-temu') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                    <span>Janji Temu</span>
                </a>
                <a class="{{ $activePage === 'riwayat-medis' ? 'is-active' : '' }}" href="{{ route('pasien.riwayat-medis') }}" @if ($activePage === 'riwayat-medis') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s-8-4.4-8-11a4.5 4.5 0 0 1 8-2.8A4.5 4.5 0 0 1 20 10c0 6.6-8 11-8 11Z"/><path d="M8 12h2l1.2-2.5L13 15l1.2-3H16"/></svg>
                    <span>Riwayat Medis</span>
                </a>
                <a class="{{ $activePage === 'pembayaran' ? 'is-active' : '' }}" href="{{ route('pasien.pembayaran') }}" @if ($activePage === 'pembayaran') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    <span>Pembayaran</span>
                </a>
                <a class="{{ $activePage === 'profil' ? 'is-active' : '' }}" href="{{ route('pasien.profil') }}" @if ($activePage === 'profil') aria-current="page" @endif>
                    <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                    <span>Profil</span>
                </a>
            </nav>
            <div class="patient-sidebar-footer">
                <span class="patient-sidebar-footer-mark">+</span>
                <span>Kesehatan Anda,<br><strong>Prioritas Kami</strong></span>
            </div>
        </aside>

        <div class="patient-main">
            <header class="patient-topbar">
                <div>
                    <p class="patient-topbar-eyebrow">Portal Pasien</p>
                    <h1>{{ $pageTitle }}</h1>
                </div>
                <div class="patient-user">
                    <span class="patient-user-avatar" aria-label="Avatar {{ $user->name }}">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}
                    </span>
                    <span class="patient-user-info">
                        <strong>{{ $user->name }}</strong>
                        <span class="patient-role-badge">Patient</span>
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
