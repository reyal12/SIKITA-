<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spesialis extends Model
{
    protected $table = 'spesialis';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    public function dokter(): HasMany
    {
        return $this->hasMany(Dokter::class, 'spesialis_id');
    }
}
