@extends('dashboard.dokter.layout')

@section('content')
    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Inventaris klinik</span>
                <h2>Data Obat</h2>
            </div>
            <span class="dashboard-badge">{{ $medicines->count() }} item</span>
        </div>
        <p class="doctor-page-intro">Kelola informasi obat, stok, harga, dan tanggal kedaluwarsa.</p>

        <div class="doctor-inventory-create">
            <h3 class="patient-subheading">Tambah obat baru</h3>
            <form class="dashboard-form" method="POST" action="{{ route('obat.store') }}">
                @csrf
                <div class="dashboard-form-grid">
                    <div class="dashboard-field"><label for="code">Kode</label><input class="sikita-input" id="code" name="code" value="{{ old('code') }}" required maxlength="30"></div>
                    <div class="dashboard-field"><label for="name">Nama obat</label><input class="sikita-input" id="name" name="name" value="{{ old('name') }}" required></div>
                    <div class="dashboard-field"><label for="category">Kategori</label><input class="sikita-input" id="category" name="category" value="{{ old('category') }}"></div>
                    <div class="dashboard-field"><label for="unit">Satuan</label><input class="sikita-input" id="unit" name="unit" value="{{ old('unit') }}" placeholder="tablet, botol, dll." required maxlength="30"></div>
                    <div class="dashboard-field"><label for="stock">Stok</label><input class="sikita-input" id="stock" name="stock" type="number" min="0" value="{{ old('stock', 0) }}" required></div>
                    <div class="dashboard-field"><label for="minimum_stock">Stok minimum</label><input class="sikita-input" id="minimum_stock" name="minimum_stock" type="number" min="0" value="{{ old('minimum_stock', 0) }}" required></div>
                    <div class="dashboard-field"><label for="unit_price">Harga satuan (Rp)</label><input class="sikita-input" id="unit_price" name="unit_price" type="number" min="0" step="0.01" value="{{ old('unit_price', 0) }}" required></div>
                    <div class="dashboard-field"><label for="expiration_date">Tanggal kedaluwarsa</label><input class="sikita-input" id="expiration_date" name="expiration_date" type="date" value="{{ old('expiration_date') }}"></div>
                </div>
                <input type="hidden" name="is_active" value="1">
                <button class="patient-button patient-button--primary" type="submit">Tambah obat</button>
            </form>
        </div>
    </section>

    <section class="patient-section-card patient-page-card">
        <div class="patient-section-heading doctor-medicine-heading">
            <div>
                <span class="patient-card-eyebrow">Stok dan informasi produk</span>
                <h2>Daftar Obat</h2>
            </div>
            @if ($medicines->isNotEmpty())
                <div class="doctor-medicine-search">
                    <label for="medicine-search">Cari obat</label>
                    <input class="sikita-input" id="medicine-search" type="search" placeholder="Nama, kode, kategori..." autocomplete="off">
                </div>
            @endif
        </div>
        @if ($medicines->isEmpty())
            <div class="doctor-empty-state">
                <strong>Belum ada data obat</strong>
                <span>Tambahkan obat menggunakan formulir di atas.</span>
            </div>
        @else
            <div class="doctor-medicine-list">
                @foreach ($medicines as $medicine)
                    <details class="doctor-medicine-card" data-medicine-item data-search="{{ $medicine->nama }} {{ $medicine->kode }} {{ $medicine->kategori }} {{ $medicine->satuan }}">
                        <summary class="doctor-medicine-summary">
                            <div>
                                <span class="patient-card-eyebrow">{{ $medicine->kode }} &bull; {{ $medicine->kategori ?: 'Tanpa kategori' }}</span>
                                <h3>{{ $medicine->nama }}</h3>
                            </div>
                            <div class="doctor-medicine-summary-meta">
                                <span class="dashboard-badge {{ !$medicine->aktif ? 'dashboard-badge--dibatalkan' : ($medicine->stok <= $medicine->stok_minimum ? 'dashboard-badge--menunggu' : 'dashboard-badge--selesai') }}">
                                    {{ !$medicine->aktif ? 'Nonaktif' : ($medicine->stok <= $medicine->stok_minimum ? 'Stok menipis' : 'Aktif') }}
                                </span>
                                <span class="doctor-medicine-toggle-label">Lihat informasi</span>
                            </div>
                        </summary>
                        <div class="doctor-medicine-details">
                            <h4>Informasi lengkap dan pengelolaan obat</h4>
                            <form class="dashboard-form" method="POST" action="{{ route('obat.update', $medicine->id) }}">
                                @csrf
                                @method('PATCH')
                                <div class="dashboard-form-grid">
                                    <div class="dashboard-field"><label>Kode</label><input class="sikita-input" name="code" value="{{ $medicine->kode }}" required maxlength="30"></div>
                                    <div class="dashboard-field"><label>Nama</label><input class="sikita-input" name="name" value="{{ $medicine->nama }}" required></div>
                                    <div class="dashboard-field"><label>Kategori</label><input class="sikita-input" name="category" value="{{ $medicine->kategori }}"></div>
                                    <div class="dashboard-field"><label>Satuan</label><input class="sikita-input" name="unit" value="{{ $medicine->satuan }}" required maxlength="30"></div>
                                    <div class="dashboard-field"><label>Stok</label><input class="sikita-input" name="stock" type="number" min="0" value="{{ $medicine->stok }}" required></div>
                                    <div class="dashboard-field"><label>Stok minimum</label><input class="sikita-input" name="minimum_stock" type="number" min="0" value="{{ $medicine->stok_minimum }}" required></div>
                                    <div class="dashboard-field"><label>Harga satuan (Rp)</label><input class="sikita-input" name="unit_price" type="number" min="0" step="0.01" value="{{ $medicine->harga_satuan }}" required></div>
                                    <div class="dashboard-field"><label>Kedaluwarsa</label><input class="sikita-input" name="expiration_date" type="date" value="{{ $medicine->tanggal_kedaluwarsa }}"></div>
                                    <div class="dashboard-field"><label>Status</label><select class="sikita-input" name="is_active" required><option value="1" @selected($medicine->aktif)>Aktif</option><option value="0" @selected(!$medicine->aktif)>Nonaktif</option></select></div>
                                </div>
                                <div class="doctor-medicine-actions">
                                    <button class="patient-button patient-button--primary" type="submit">Simpan perubahan</button>
                                </div>
                            </form>
                            <form class="doctor-medicine-delete" method="POST" action="{{ route('obat.destroy', $medicine->id) }}" onsubmit="return confirm('Hapus obat {{ $medicine->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="patient-button patient-button--light" type="submit">Hapus obat</button>
                            </form>
                        </div>
                    </details>
                @endforeach
            </div>
            <div class="doctor-empty-state doctor-medicine-no-results" id="medicine-search-empty" hidden>
                <strong>Obat tidak ditemukan</strong>
                <span>Coba kata kunci nama, kode, kategori, atau satuan yang berbeda.</span>
            </div>
        @endif
    </section>

    @if ($medicines->isNotEmpty())
        <script>
            const medicineSearch = document.getElementById('medicine-search');
            const medicineItems = document.querySelectorAll('[data-medicine-item]');
            const medicineSearchEmpty = document.getElementById('medicine-search-empty');

            medicineSearch.addEventListener('input', () => {
                const query = medicineSearch.value.trim().toLocaleLowerCase();
                let visibleCount = 0;

                medicineItems.forEach((item) => {
                    const matches = item.dataset.search.toLocaleLowerCase().includes(query);
                    item.hidden = !matches;
                    visibleCount += matches ? 1 : 0;
                });

                medicineSearchEmpty.hidden = visibleCount > 0;
            });
        </script>
    @endif
@endsection
