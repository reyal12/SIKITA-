<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dokter extends Model
{
    protected $table = 'dokter';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pengguna_id',
        'spesialis_id',
        'nomor_dokter',
        'nama_lengkap',
        'nomor_izin_praktik',
        'telepon',
        'aktif',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }

    public function spesialis(): BelongsTo
    {
        return $this->belongsTo(Spesialis::class, 'spesialis_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalDokter::class, 'dokter_id');
    }
}
