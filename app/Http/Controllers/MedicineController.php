<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.obat', [
            'user' => $request->user(),
            'pageTitle' => 'Data Obat',
            'activePage' => 'obat',
            'doctor' => DB::table('dokter')
                ->where('pengguna_id', $request->user()->id)
                ->first(),
            'medicines' => DB::table('obat')->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::table('obat')->insert([
            ...$this->medicineAttributes($validated),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('obat.index')->with('sukses', 'Data obat berhasil ditambahkan.');
    }

    public function update(Request $request, int $medicine): RedirectResponse
    {
        abort_unless(DB::table('obat')->where('id', $medicine)->exists(), 404);
        $validated = $request->validate($this->rules($medicine));

        DB::table('obat')->where('id', $medicine)->update([
            ...$this->medicineAttributes($validated),
            'updated_at' => now(),
        ]);

        return redirect()->route('obat.index')->with('sukses', 'Data obat berhasil diperbarui.');
    }

    public function destroy(int $medicine): RedirectResponse
    {
        abort_unless(DB::table('obat')->where('id', $medicine)->exists(), 404);

        if (DB::table('detail_resep')->where('obat_id', $medicine)->exists()) {
            throw ValidationException::withMessages([
                'medicine' => 'Obat sudah tercatat pada resep sehingga tidak dapat dihapus.',
            ]);
        }

        DB::table('obat')->where('id', $medicine)->delete();

        return redirect()->route('obat.index')->with('sukses', 'Data obat berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function rules(?int $medicine = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('obat', 'kode')->ignore($medicine),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'stock' => ['required', 'integer', 'between:0,1000000'],
            'minimum_stock' => ['required', 'integer', 'between:0,1000000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'expiration_date' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function medicineAttributes(array $validated): array
    {
        return [
            'kode' => $validated['code'],
            'nama' => $validated['name'],
            'kategori' => $validated['category'] ?? null,
            'satuan' => $validated['unit'],
            'stok' => (int) $validated['stock'],
            'stok_minimum' => (int) $validated['minimum_stock'],
            'harga_satuan' => $validated['unit_price'],
            'tanggal_kedaluwarsa' => $validated['expiration_date'] ?? null,
            'aktif' => (bool) $validated['is_active'],
        ];
    }
}
