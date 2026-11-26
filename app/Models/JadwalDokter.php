<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalDokter extends Model
{
    protected $table = 'jadwal_dokter';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'dokter_id',
        'hari_dalam_minggu',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_janji_menit',
        'maksimal_pasien',
    ];

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }
}
