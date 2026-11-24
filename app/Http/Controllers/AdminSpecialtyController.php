<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminSpecialtyController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.spesialis.index', [
            'specialties' => DB::table('spesialis as s')
                ->leftJoin('dokter as d', 'd.spesialis_id', '=', 's.id')
                ->select('s.id', 's.kode', 's.nama', 's.deskripsi')
                ->selectRaw('COUNT(d.id) as jumlah_dokter')
                ->groupBy('s.id', 's.kode', 's.nama', 's.deskripsi')
                ->orderBy('s.nama')
                ->paginate(15),
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.spesialis.form', [
            'specialty' => null,
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'alpha_dash', 'unique:spesialis,kode'],
            'nama' => ['required', 'string', 'max:255', 'unique:spesialis,nama'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('spesialis')->insert($validated + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.spesialis.index')
            ->with('sukses', 'Data spesialis berhasil ditambahkan.');
    }

    public function edit(int $specialty): View
    {
        return view('dashboard.admin.spesialis.form', [
            'specialty' => $this->findSpecialty($specialty),
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request, int $specialty): RedirectResponse
    {
        $this->findSpecialty($specialty);
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:20',
                'alpha_dash',
                Rule::unique('spesialis', 'kode')->ignore($specialty),
            ],
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('spesialis', 'nama')->ignore($specialty),
            ],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::table('spesialis')->where('id', $specialty)->update($validated + ['updated_at' => now()]);

        return redirect()->route('admin.spesialis.index')
            ->with('sukses', 'Data spesialis berhasil diperbarui.');
    }

    public function destroy(int $specialty): RedirectResponse
    {
        $this->findSpecialty($specialty);

        if (DB::table('dokter')->where('spesialis_id', $specialty)->exists()) {
            throw ValidationException::withMessages([
                'specialty' => 'Spesialis masih digunakan oleh dokter dan tidak dapat dihapus.',
            ]);
        }

        DB::table('spesialis')->where('id', $specialty)->delete();

        return redirect()->route('admin.spesialis.index')
            ->with('sukses', 'Data spesialis berhasil dihapus.');
    }

    private function findSpecialty(int $specialty): object
    {
        $record = DB::table('spesialis')->where('id', $specialty)->first();

        abort_if($record === null, 404);

        return $record;
    }
}
