<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ResetAdminAccountPasswordRequest;
use App\Http\Requests\Admin\StoreAdminAccountRequest;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin.akun.index', [
            'accounts' => User::query()->with('peran')->orderBy('name')->paginate(20),
            'user' => request()->user(),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.admin.akun.form', [
            'roles' => Peran::query()
                ->whereIn('kode', ['admin', 'dokter', 'pasien'])
                ->orderBy('nama')
                ->get(),
            'specialties' => DB::table('spesialis')->orderBy('nama')->get(['id', 'nama']),
            'user' => request()->user(),
        ]);
    }

    public function store(StoreAdminAccountRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            $account = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $role = Peran::query()->where('kode', $validated['role'])->firstOrFail();
            $account->peran()->attach($role->id, [
                'ditetapkan_oleh' => $request->user()->id,
            ]);

            if ($validated['role'] === 'dokter') {
                DB::table('dokter')->insert([
                    'pengguna_id' => $account->id,
                    'spesialis_id' => $validated['specialty_id'],
                    'nomor_dokter' => 'DR-'.Str::upper(Str::random(12)),
                    'nama_lengkap' => $validated['name'],
                    'nomor_izin_praktik' => $validated['license_number'],
                    'telepon' => $validated['phone'] ?? null,
                    'aktif' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($validated['role'] === 'pasien') {
                DB::table('pasien')->insert([
                    'pengguna_id' => $account->id,
                    'nomor_pasien' => 'PSN-'.Str::upper(Str::random(12)),
                    'nama_lengkap' => $validated['name'],
                    'email' => $validated['email'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.akun.index')
            ->with('sukses', 'Akun dan peran berhasil dibuat.');
    }

    public function resetPassword(
        ResetAdminAccountPasswordRequest $request,
        int $account,
    ): RedirectResponse {
        $user = User::query()->findOrFail($account);
        $user->password = $request->validated('password');
        $user->save();

        return redirect()->route('admin.akun.index')
            ->with('sukses', 'Kata sandi akun '.$user->name.' berhasil direset.');
    }
}
