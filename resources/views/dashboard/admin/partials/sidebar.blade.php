<aside class="admin-sidebar" aria-label="Navigasi administrator">
    <a class="admin-sidebar-brand" href="{{ route('admin.dashboard') }}" aria-label="SIKITA, ringkasan klinik">
        <span class="admin-sidebar-logo" aria-hidden="true">S</span>
        <span>SIKITA</span>
    </a>
    <p class="admin-sidebar-title">Menu Utama</p>
    <nav class="admin-sidebar-nav">
        <a
            class="{{ $activePage === 'dashboard' ? 'is-active' : '' }}"
            href="{{ route('admin.dashboard') }}#ringkasan"
            @if ($activePage === 'dashboard') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg>
            <span>Dashboard</span>
        </a>
        <a
            class="{{ $activePage === 'pasien' ? 'is-active' : '' }}"
            href="{{ route('admin.pasien.index') }}"
            @if ($activePage === 'pasien') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
            <span>Pasien</span>
        </a>
        <a
            class="{{ $activePage === 'dokter' ? 'is-active' : '' }}"
            href="{{ route('admin.dokter.index') }}"
            @if ($activePage === 'dokter') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2M12 11v6M9 14h6"/></svg>
            <span>Dokter</span>
        </a>
        <a
            class="{{ $activePage === 'jadwal' ? 'is-active' : '' }}"
            href="{{ route('admin.jadwal-dokter.index') }}"
            @if ($activePage === 'jadwal') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h3M8 17h6"/></svg>
            <span>Jadwal Dokter</span>
        </a>
        <a
            class="{{ $activePage === 'obat' ? 'is-active' : '' }}"
            href="{{ route('admin.obat.index') }}"
            @if ($activePage === 'obat') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4.93 19.07a7 7 0 0 1 0-9.9l4.24-4.24a7 7 0 0 1 9.9 9.9l-4.24 4.24a7 7 0 0 1-9.9 0Z"/><path d="m8 8 8 8"/></svg>
            <span>Obat</span>
        </a>
        <a
            class="{{ $activePage === 'akun' ? 'is-active' : '' }}"
            href="{{ route('admin.akun.index') }}"
            @if ($activePage === 'akun') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M17 8h4M19 6v4"/></svg>
            <span>Akun</span>
        </a>
        <a
            class="{{ $activePage === 'pemantauan' ? 'is-active' : '' }}"
            href="{{ route('admin.pemantauan.pendaftaran.index') }}"
            @if ($activePage === 'pemantauan') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
            <span>Pantau Pendaftaran &amp; Pelayanan</span>
        </a>
    </nav>
    <div class="admin-sidebar-footer">
        <span class="admin-sidebar-footer-mark" aria-hidden="true">+</span>
        <span>Administrator<br><strong>Klinik SIKITA</strong></span>
    </div>
</aside>
