<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\View\View;

class AdminClinicInformationController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.informasi-klinik.index', [
            'information' => DB::table('informasi_klinik')
                ->orderBy('kategori')
                ->orderBy('judul')
                ->paginate(15),
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.informasi-klinik.form', [
            'item' => null,
            'user' => request()->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        DB::table('informasi_klinik')->insert($this->attributes($validated) + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.informasi-klinik.index')
            ->with('sukses', 'Informasi klinik berhasil ditambahkan.');
    }

    public function edit(int $item): View
    {
        return view('dashboard.admin.informasi-klinik.form', [
            'item' => $this->findInformation($item),
            'user' => request()->user(),
        ]);
    }

    public function update(Request $request, int $item): RedirectResponse
    {
        $this->findInformation($item);
        $validated = $request->validate($this->rules());

        DB::table('informasi_klinik')->where('id', $item)->update(
            $this->attributes($validated) + ['updated_at' => now()],
        );

        return redirect()->route('admin.informasi-klinik.index')
            ->with('sukses', 'Informasi klinik berhasil diperbarui.');
    }

    public function destroy(int $item): RedirectResponse
    {
        $this->findInformation($item);
        DB::table('informasi_klinik')->where('id', $item)->delete();

        return redirect()->route('admin.informasi-klinik.index')
            ->with('sukses', 'Informasi klinik berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, string|In>>
     */
    private function rules(): array
    {
        return [
            'kategori' => ['required', Rule::in(['layanan', 'kegiatan'])],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
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
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'aktif' => (bool) $validated['aktif'],
        ];
    }

    private function findInformation(int $item): object
    {
        $record = DB::table('informasi_klinik')->where('id', $item)->first();

        abort_if($record === null, 404);

        return $record;
    }
}
