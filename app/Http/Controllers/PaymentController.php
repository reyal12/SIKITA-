<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'integer', 'exists:pasien,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:janji_temu,id'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'payment_method' => ['required', 'in:tunai,transfer_bank,kartu,dompet_digital'],
            'status' => ['required', 'in:menunggu,dibayar,gagal,dikembalikan'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (isset($validated['appointment_id'])) {
            $appointmentBelongsToPatient = DB::table('janji_temu')
                ->where('id', $validated['appointment_id'])
                ->where('pasien_id', $validated['patient_id'])
                ->exists();

            if (! $appointmentBelongsToPatient) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Janji temu tidak cocok dengan pasien yang dipilih.',
                ]);
            }
        }

        DB::table('pembayaran')->insert([
            'nomor_pembayaran' => 'BYR-'.Str::upper(Str::random(12)),
            'pasien_id' => $validated['patient_id'],
            'janji_temu_id' => $validated['appointment_id'] ?? null,
            'dibuat_oleh' => $request->user()->id,
            'jumlah' => $validated['amount'],
            'metode_pembayaran' => $validated['payment_method'],
            'status' => $validated['status'],
            'dibayar_pada' => $validated['status'] === 'dibayar' ? now() : null,
            'catatan' => $validated['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('sukses', 'Tagihan berhasil dicatat.');
    }

    public function updateStatus(Request $request, int $payment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:menunggu,dibayar,gagal,dikembalikan'],
        ]);

        $updated = DB::table('pembayaran')
            ->where('id', $payment)
            ->update([
                'status' => $validated['status'],
                'dibayar_pada' => $validated['status'] === 'dibayar' ? now() : null,
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            abort_unless(DB::table('pembayaran')->where('id', $payment)->exists(), 404);
        }

        return redirect()->route('admin.dashboard')
            ->with('sukses', 'Status pembayaran berhasil diperbarui.');
    }
}
