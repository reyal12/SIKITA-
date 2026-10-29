<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan,lainnya'],
            'alamat' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            DB::table('users')->where('id', $request->user()->id)->lockForUpdate()->first();
            $patient = DB::table('pasien')->where('pengguna_id', $request->user()->id)->first();
            $profile = [
                'nama_lengkap' => $validated['nama_lengkap'],
                'telepon' => $validated['telepon'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'updated_at' => now(),
            ];

            if ($patient === null) {
                DB::table('pasien')->insert($profile + [
                    'nomor_pasien' => 'PSN-'.Str::upper(Str::random(12)),
                    'email' => $request->user()->email,
                    'pengguna_id' => $request->user()->id,
                    'created_at' => now(),
                ]);
            } else {
                DB::table('pasien')->where('id', $patient->id)->update($profile);
            }

            $request->user()->update(['name' => $validated['nama_lengkap']]);
        });

        return redirect()->route('pasien.dashboard')
            ->with('sukses', 'Profil pasien berhasil diperbarui.');
    }
}
