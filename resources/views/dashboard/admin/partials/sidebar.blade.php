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
            <span>Ringkasan</span>
        </a>
        <a
            class="{{ $activePage === 'pendaftaran' ? 'is-active' : '' }}"
            href="{{ route('admin.pendaftaran.index') }}"
            @if ($activePage === 'pendaftaran') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
            <span>Pendaftaran</span>
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
            <span>Jadwal</span>
        </a>
        <a
            class="{{ $activePage === 'spesialis' ? 'is-active' : '' }}"
            href="{{ route('admin.spesialis.index') }}"
            @if ($activePage === 'spesialis') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
            <span>Spesialis</span>
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
            class="{{ $activePage === 'obat' ? 'is-active' : '' }}"
            href="{{ route('admin.obat.index') }}"
            @if ($activePage === 'obat') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4.93 19.07a7 7 0 0 1 0-9.9l4.24-4.24a7 7 0 0 1 9.9 9.9l-4.24 4.24a7 7 0 0 1-9.9 0Z"/><path d="m8 8 8 8"/></svg>
            <span>Obat</span>
        </a>
        <a
            class="{{ $activePage === 'informasi-klinik' ? 'is-active' : '' }}"
            href="{{ route('admin.informasi-klinik.index') }}"
            @if ($activePage === 'informasi-klinik') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-4h4v4"/></svg>
            <span>Informasi Klinik</span>
        </a>
        <a
            class="{{ $activePage === 'pelayanan' ? 'is-active' : '' }}"
            href="{{ route('admin.pelayanan.index') }}"
            @if ($activePage === 'pelayanan') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/></svg>
            <span>Pelayanan</span>
        </a>
        <a
            class="{{ $activePage === 'laporan' ? 'is-active' : '' }}"
            href="{{ route('admin.laporan.index') }}"
            @if ($activePage === 'laporan') aria-current="page" @endif
        >
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 19V5M4 19h17M8 16v-4M13 16V8M18 16V5"/></svg>
            <span>Laporan</span>
        </a>
        <a href="{{ route('admin.dashboard') }}#catat-tagihan">
            <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
            <span>Catat Tagihan</span>
        </a>
        <a href="{{ route('admin.dashboard') }}#riwayat-pembayaran">
            <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 2.64-6.36L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/></svg>
            <span>Riwayat Pembayaran</span>
        </a>
    </nav>
    <div class="admin-sidebar-footer">
        <span class="admin-sidebar-footer-mark" aria-hidden="true">+</span>
        <span>Panel Administrator<br><strong>Klinik SIKITA</strong></span>
    </div>
</aside>
