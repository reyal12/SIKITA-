<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPeran
{
    /**
     * Menangani permintaan masuk dan memeriksa kecocokan peran pengguna.
     *
     * Format penggunaan:
     * - ->middleware('peran:admin')
     * - ->middleware('peran:admin,dokter')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        // Pisahkan parameter jika ditulis dengan pemisah koma dalam satu argumen
        $daftarPeran = [];
        foreach ($peran as $item) {
            foreach (explode(',', $item) as $kode) {
                $trimmed = trim($kode);
                if ($trimmed !== '') {
                    $daftarPeran[] = $trimmed;
                }
            }
        }

        // Periksa apakah pengguna sedang login dan memiliki salah satu peran yang diizinkan
        if (! $user || ! $user->punyaPeran(...$daftarPeran)) {
            // Tampilkan halaman 403 berbahasa Indonesia jika peran tidak sesuai
            abort(403, 'Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
