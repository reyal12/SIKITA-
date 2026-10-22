<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Tampilkan formulir pendaftaran akun pasien baru.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Tangani pendaftaran pengguna baru (khusus pasien).
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        // Buat akun pengguna baru dengan password yang di-hash
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // Berikan peran pasien secara otomatis (tanpa membuat baris di tabel pasien)
        $peranPasien = Peran::firstOrCreate(
            ['kode' => 'pasien'],
            [
                'nama' => 'Pasien',
                'deskripsi' => 'Mendaftar kunjungan dan mengakses informasi layanan miliknya.',
            ]
        );

        $user->peran()->attach($peranPasien->id);

        // Arahkan kembali ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('sukses', 'Pendaftaran akun pasien berhasil! Silakan masuk dengan akun Anda.');
    }
}
