<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akun Pengguna &bull; SIKITA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-app-body">
    <div class="admin-app-shell">
        @include('dashboard.admin.partials.sidebar', ['activePage' => 'akun'])
        <main class="admin-main">
            @include('dashboard.admin.partials.topbar', ['pageTitle' => 'Akun Pengguna'])
            <div class="admin-page-content">
                @if (session('sukses'))
                    <div class="dashboard-alert" role="status">{{ session('sukses') }}</div>
                @endif
                @if ($errors->any())
                    <div class="dashboard-alert dashboard-alert--error" role="alert">
                        <strong>Reset kata sandi tidak berhasil.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <section class="sikita-card dashboard-card admin-patient-card">
                    <div class="section-heading admin-patient-toolbar">
                        <div>
                            <h2>Semua akun</h2>
                            <p class="muted">Daftar akun pengguna dan peran yang telah diberikan.</p>
                        </div>
                        <a class="sikita-btn-primer admin-patient-add" href="{{ route('admin.akun.create') }}">Buat akun</a>
                    </div>
                    @if ($accounts->isEmpty())
                        <p class="dashboard-empty">Belum ada akun.</p>
                    @else
                        <div class="dashboard-table-wrap">
                            <table class="dashboard-table">
                                <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Tanggal dibuat</th><th>Reset kata sandi</th></tr></thead>
                                <tbody>
                                    @foreach ($accounts as $account)
                                        <tr>
                                            <td>{{ $account->name }}</td>
                                            <td>{{ $account->email }}</td>
                                            <td>
                                                @forelse ($account->peran as $role)
                                                    <span class="dashboard-badge">{{ $role->nama }}</span>
                                                @empty
                                                    <span class="muted">Belum ada peran</span>
                                                @endforelse
                                            </td>
                                            <td>{{ $account->created_at?->format('d M Y') ?: '—' }}</td>
                                            <td>
                                                <form class="inline-actions" method="POST" action="{{ route('admin.akun.password.update', $account->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input class="sikita-input" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Kata sandi baru" aria-label="Kata sandi baru untuk {{ $account->name }}" required>
                                                    <input class="sikita-input" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" aria-label="Konfirmasi kata sandi untuk {{ $account->name }}" required>
                                                    <button class="sikita-btn-outline" type="submit">Reset</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="admin-patient-pagination">{{ $accounts->links() }}</div>
                    @endif
                </section>
            </div>
        </main>
    </div>
</body>
</html>
