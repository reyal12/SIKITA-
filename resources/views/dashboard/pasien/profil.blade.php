@extends('dashboard.pasien.layout')

@section('content')
    <section class="patient-section-card patient-page-card" id="profil">
        <div class="patient-section-heading">
            <div>
                <span class="patient-card-eyebrow">Data diri</span>
                <h2>Profil Pasien</h2>
            </div>
            @if ($patient)
                <span class="dashboard-badge">{{ $patient->nomor_pasien }}</span>
            @endif
        </div>
        <p class="muted">Pastikan informasi kontak dan data diri Anda selalu terbaru.</p>
        <form class="dashboard-form patient-profile-form" method="POST" action="{{ route('pasien.profil.update') }}">
            @csrf
            <div class="dashboard-form-grid">
                <div class="dashboard-field">
                    <label for="nama_lengkap">Nama lengkap</label>
                    <input class="sikita-input" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $patient->nama_lengkap ?? $user->name) }}" maxlength="255" required>
                </div>
                <div class="dashboard-field">
                    <label for="email">Email</label>
                    <input class="sikita-input" id="email" value="{{ $user->email }}" disabled>
                </div>
                <div class="dashboard-field">
                    <label for="telepon">Nomor telepon</label>
                    <input class="sikita-input" id="telepon" name="telepon" value="{{ old('telepon', $patient->telepon ?? '') }}" maxlength="30">
                </div>
                <div class="dashboard-field">
                    <label for="tanggal_lahir">Tanggal lahir</label>
                    <input class="sikita-input" id="tanggal_lahir" name="tanggal_lahir" type="date" max="{{ today()->toDateString() }}" value="{{ old('tanggal_lahir', $patient->tanggal_lahir ?? '') }}">
                </div>
                <div class="dashboard-field">
                    <label for="jenis_kelamin">Jenis kelamin</label>
                    <select class="sikita-input" id="jenis_kelamin" name="jenis_kelamin">
                        <option value="">Pilih</option>
                        <option value="laki-laki" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'laki-laki')>Laki-laki</option>
                        <option value="perempuan" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'perempuan')>Perempuan</option>
                        <option value="lainnya" @selected(old('jenis_kelamin', $patient->jenis_kelamin ?? '') === 'lainnya')>Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="dashboard-field">
                <label for="alamat">Alamat</label>
                <textarea class="sikita-input" id="alamat" name="alamat" maxlength="2000">{{ old('alamat', $patient->alamat ?? '') }}</textarea>
            </div>
            <button class="sikita-btn-primer" type="submit">Simpan Profil</button>
        </form>
    </section>
@endsection
