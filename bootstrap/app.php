<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Daftarkan alias middleware hak akses peran
        $middleware->alias([
            'peran' => \App\Http\Middleware\CheckPeran::class,
        ]);

        // Arahkan tamu yang belum login ke rute login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // Arahkan pengguna yang sudah login ke dashboard sesuai peran jika membuka halaman login/registrasi
        $middleware->redirectUsersTo(function () {
            /** @var \App\Models\User|null $user */
            $user = auth()->user();

            return $user ? route($user->dashboardRoute()) : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
