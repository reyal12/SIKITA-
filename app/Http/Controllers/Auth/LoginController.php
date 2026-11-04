<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Tampilkan formulir login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Tangani permintaan autentikasi login pengguna.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // Autentikasi kredensial dan proteksi rate limiting 5 percobaan/menit
        $request->authenticate();

        // Regenerasi session untuk mencegah session fixation attacks
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Jika pengguna belum memiliki peran sama sekali, tetapkan peran 'pasien' sebagai default
        if ($user->peran()->count() === 0) {
            $peranPasien = Peran::firstOrCreate(
                ['kode' => 'pasien'],
                [
                    'nama' => 'Pasien',
                    'deskripsi' => 'Mendaftar kunjungan dan mengakses informasi layanan miliknya.',
                ]
            );

            $user->peran()->syncWithoutDetaching([$peranPasien->id]);
            $user->unsetRelation('peran');
        }

        // Redirect pengguna ke dashboard sesuai peran dengan prioritas: admin > dokter > pasien
        return redirect()->intended(route($user->dashboardRoute()));
    }

    /**
     * Tangani permintaan keluar (logout) pengguna.
     * Hanya dapat diakses melalui metode HTTP POST dengan proteksi CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate session saat ini dan regenerasi CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('sukses', 'Anda telah berhasil keluar.');
    }
}
