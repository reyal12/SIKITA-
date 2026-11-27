<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreSpesialisRequest;
use App\Http\Requests\Admin\UpdateSpesialisRequest;
use App\Models\Spesialis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminSpecialtyController extends Controller
{
    public function store(StoreSpesialisRequest $request): RedirectResponse
    {
        Spesialis::query()->create($request->validated());

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Spesialisasi berhasil ditambahkan.');
    }

    public function update(UpdateSpesialisRequest $request, int $specialty): RedirectResponse
    {
        $specialtyRecord = Spesialis::query()->findOrFail($specialty);
        $specialtyRecord->fill($request->validated())->save();

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Spesialisasi berhasil diperbarui.');
    }

    public function destroy(int $specialty): RedirectResponse
    {
        DB::transaction(function () use ($specialty): void {
            $specialtyRecord = Spesialis::query()->lockForUpdate()->findOrFail($specialty);

            if ($specialtyRecord->dokter()->exists()) {
                throw ValidationException::withMessages([
                    'specialty' => 'Spesialisasi masih digunakan oleh dokter dan tidak dapat dihapus.',
                ]);
            }

            $specialtyRecord->delete();
        });

        return redirect()->route('admin.dokter.index')
            ->with('sukses', 'Spesialisasi berhasil dihapus.');
    }
}
