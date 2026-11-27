<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class DoctorProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $doctor = DB::table('dokter as d')
            ->join('spesialis as s', 's.id', '=', 'd.spesialis_id')
            ->where('d.pengguna_id', $request->user()->id)
            ->first([
                'd.id',
                'd.nama_lengkap',
                'd.nomor_dokter',
                'd.nomor_izin_praktik',
                'd.telepon',
                'd.foto_profil',
                's.nama as nama_spesialis',
            ]);
        abort_if($doctor === null, 404);

        return view('dashboard.dokter.profil', [
            'user' => $request->user(),
            'pageTitle' => 'Profil Dokter',
            'activePage' => 'profil',
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $this->doctorForUser($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('photo') && (bool) ($validated['remove_photo'] ?? false)) {
            throw ValidationException::withMessages([
                'photo' => 'Pilih unggah foto baru atau hapus foto yang ada, bukan keduanya.',
            ]);
        }

        $newPhotoPath = null;
        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->storePublicly('doctor-profiles', 'public');

            if ($newPhotoPath === false) {
                throw new RuntimeException('Foto profil dokter gagal disimpan.');
            }
        }

        $oldPhotoPath = $doctor->foto_profil;
        $photoPath = (bool) ($validated['remove_photo'] ?? false)
            ? null
            : ($newPhotoPath ?? $oldPhotoPath);

        DB::transaction(function () use ($request, $doctor, $validated, $photoPath): void {
            DB::table('dokter')->where('id', $doctor->id)->update([
                'nama_lengkap' => $validated['name'],
                'telepon' => $validated['phone'] ?? null,
                'foto_profil' => $photoPath,
                'updated_at' => now(),
            ]);

            $request->user()->update(['name' => $validated['name']]);
        });

        if ($oldPhotoPath !== null && $oldPhotoPath !== $photoPath
            && Storage::disk('public')->exists($oldPhotoPath)
            && ! Storage::disk('public')->delete($oldPhotoPath)) {
            throw new RuntimeException('Foto profil lama tidak dapat dihapus dari penyimpanan.');
        }

        return redirect()->route('dokter.profil')
            ->with('sukses', 'Profil dokter berhasil diperbarui.');
    }

    private function doctorForUser(Request $request): object
    {
        $doctor = DB::table('dokter')
            ->where('pengguna_id', $request->user()->id)
            ->first();

        abort_if($doctor === null, 404);

        return $doctor;
    }
}
