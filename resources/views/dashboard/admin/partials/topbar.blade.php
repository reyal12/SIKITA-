<header class="admin-topbar">
    <div>
        <p class="admin-topbar-eyebrow">Panel Administrator Klinik</p>
        <h1>{{ $pageTitle }}</h1>
    </div>
    <div class="admin-user">
        <span class="admin-user-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</span>
        <span class="admin-user-info">
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->email }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="admin-logout-button" type="submit" aria-label="Keluar">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/></svg>
            </button>
        </form>
    </div>
</header>
