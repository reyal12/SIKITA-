<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Peran extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     *
     * @var string
     */
    protected $table = 'peran';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    /**
     * Relasi many-to-many ke model User melalui tabel peran_pengguna.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'peran_pengguna', 'peran_id', 'pengguna_id')
            ->withPivot('ditetapkan_oleh')
            ->withTimestamps();
    }
}
