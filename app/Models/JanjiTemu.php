<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JanjiTemu extends Model
{
    protected $table = 'janji_temu';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_janji',
        'pasien_id',
        'dokter_id',
        'tanggal_janji',
        'nomor_antrean',
        'waktu_mulai',
        'status',
        'keluhan_utama',
        'catatan',
        'dibuat_oleh',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_janji' => 'date',
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id')->withTrashed();
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }
}
