<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi many-to-many ke model Peran melalui peran_pengguna.
     */
    public function peran(): BelongsToMany
    {
        return $this->belongsToMany(Peran::class, 'peran_pengguna', 'pengguna_id', 'peran_id')
            ->withPivot('ditetapkan_oleh')
            ->withTimestamps();
    }

    /**
     * Periksa apakah pengguna memiliki salah satu peran yang diberikan.
     */
    public function punyaPeran(string ...$kode): bool
    {
        if (empty($kode)) {
            return false;
        }

        if ($this->relationLoaded('peran')) {
            return $this->peran->pluck('kode')->intersect($kode)->isNotEmpty();
        }

        return $this->peran()->whereIn('kode', $kode)->exists();
    }

    /**
     * Dapatkan nama rute dashboard sesuai prioritas peran: admin > dokter > pasien.
     */
    public function dashboardRoute(): string
    {
        // Prioritas 1: Administrator memiliki hak akses tertinggi
        if ($this->punyaPeran('admin')) {
            return 'admin.dashboard';
        }

        // Prioritas 2: Dokter
        if ($this->punyaPeran('dokter')) {
            return 'dokter.dashboard';
        }

        // Prioritas 3: Pasien
        return 'pasien.dashboard';
    }
}
