<nav class="admin-filter-form" aria-label="Tampilan pemantauan">
    <a class="{{ $activeTab === 'pendaftaran' ? 'sikita-btn-primer' : 'sikita-btn-outline' }}" href="{{ route('admin.pemantauan.pendaftaran.index') }}">
        Pendaftaran
    </a>
    <a class="{{ $activeTab === 'pelayanan' ? 'sikita-btn-primer' : 'sikita-btn-outline' }}" href="{{ route('admin.pemantauan.pelayanan.index') }}">
        Pelayanan
    </a>
</nav>
