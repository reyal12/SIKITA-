<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pasien extends Model
{
    use SoftDeletes;

    protected $table = 'pasien';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_pasien',
        'nomor_identitas',
        'nama_lengkap',
        'tanggal_lahir',
        'jenis_kelamin',
        'telepon',
        'email',
        'alamat',
        'golongan_darah',
        'pengguna_id',
    ];

    public function janjiTemu(): HasMany
    {
        return $this->hasMany(JanjiTemu::class, 'pasien_id');
    }
}
