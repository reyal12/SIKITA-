<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        // Jalankan dalam transaksi agar pembuatan pengguna dan penugasan peran bersifat atomik
        DB::transaction(function () use ($request): void {
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
        });

        // Arahkan kembali ke halaman login dengan pesan sukses
        return redirect()->route('login')->with('sukses', 'Pendaftaran akun pasien berhasil! Silakan masuk dengan akun Anda.');
    }

    public function showDoctorRegistrationForm(): View
    {
        return view('auth.register-doctor');
    }

    public function registerDoctor(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_number' => ['required', 'string', 'max:50', 'unique:dokter,nomor_izin_praktik'],
            'specialty' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $specialty = DB::table('spesialis')
                ->where('nama', $validated['specialty'])
                ->first();

            if ($specialty === null) {
                $specialtyId = DB::table('spesialis')->insertGetId([
                    'nama' => $validated['specialty'],
                    'kode' => 'SP-'.Str::upper(Str::random(8)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $specialtyId = $specialty->id;
            }

            DB::table('dokter')->insert([
                'pengguna_id' => $user->id,
                'spesialis_id' => $specialtyId,
                'nomor_dokter' => 'DR-'.Str::upper(Str::random(10)),
                'nama_lengkap' => $validated['name'],
                'nomor_izin_praktik' => $validated['license_number'],
                'telepon' => $validated['phone'] ?? null,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $doctorRole = Peran::firstOrCreate(
                ['kode' => 'dokter'],
                [
                    'nama' => 'Dokter',
                    'deskripsi' => 'Mengelola jadwal praktik, kunjungan, rekam medis, dan resep.',
                ]
            );

            $user->peran()->attach($doctorRole->id);
        });

        return redirect()->route('login.dokter')
            ->with('sukses', 'Pendaftaran dokter berhasil. Silakan masuk menggunakan email dan kata sandi Anda.');
    }
}
