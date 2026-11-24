<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminMedicineController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.obat.index', [
            'medicines' => DB::table('obat')
                ->orderBy('nama')
                ->paginate(15),
            'lowStockCount' => DB::table('obat')
                ->where('aktif', true)
                ->whereColumn('stok', '<=', 'stok_minimum')
                ->count(),
            'expiredCount' => DB::table('obat')
                ->where('aktif', true)
                ->whereNotNull('tanggal_kedaluwarsa')
                ->whereDate('tanggal_kedaluwarsa', '<', today())
                ->count(),
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.obat.form', [
            'medicine' => null,
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::table('obat')->insert($this->attributes($validated) + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.obat.index')
            ->with('sukses', 'Data obat berhasil ditambahkan.');
    }

    public function edit(int $medicine): View
    {
        return view('dashboard.admin.obat.form', [
            'medicine' => $this->findMedicine($medicine),
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request, int $medicine): RedirectResponse
    {
        $this->findMedicine($medicine);
        $validated = $request->validate($this->rules($medicine));

        DB::table('obat')->where('id', $medicine)->update(
            $this->attributes($validated) + ['updated_at' => now()],
        );

        return redirect()->route('admin.obat.index')
            ->with('sukses', 'Data obat berhasil diperbarui.');
    }

    public function destroy(int $medicine): RedirectResponse
    {
        $this->findMedicine($medicine);

        if (DB::table('detail_resep')->where('obat_id', $medicine)->exists()) {
            throw ValidationException::withMessages([
                'medicine' => 'Obat sudah tercatat pada resep dan tidak dapat dihapus. Nonaktifkan obat agar tidak digunakan lagi.',
            ]);
        }

        DB::table('obat')->where('id', $medicine)->delete();

        return redirect()->route('admin.obat.index')
            ->with('sukses', 'Data obat berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function rules(?int $medicine = null): array
    {
        return [
            'kode' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('obat', 'kode')->ignore($medicine),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'satuan' => ['required', 'string', 'max:30'],
            'stok' => ['required', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'harga_satuan' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'tanggal_kedaluwarsa' => ['nullable', 'date_format:Y-m-d'],
            'aktif' => ['required', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function attributes(array $validated): array
    {
        return [
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'kategori' => $validated['kategori'] ?? null,
            'satuan' => $validated['satuan'],
            'stok' => $validated['stok'],
            'stok_minimum' => $validated['stok_minimum'],
            'harga_satuan' => $validated['harga_satuan'],
            'tanggal_kedaluwarsa' => $validated['tanggal_kedaluwarsa'] ?? null,
            'aktif' => (bool) $validated['aktif'],
        ];
    }

    private function findMedicine(int $medicine): object
    {
        $record = DB::table('obat')->where('id', $medicine)->first();

        abort_if($record === null, 404);

        return $record;
    }
}
