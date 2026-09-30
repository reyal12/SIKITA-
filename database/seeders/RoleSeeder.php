<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('roles')->upsert([
            [
                'slug' => 'admin',
                'name' => 'Administrator',
                'description' => 'Mengelola operasional klinik, akun, jadwal, dan transaksi.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'slug' => 'doctor',
                'name' => 'Dokter',
                'description' => 'Mengelola jadwal praktik, kunjungan, rekam medis, dan resep.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'slug' => 'patient',
                'name' => 'Pasien',
                'description' => 'Mendaftar kunjungan dan mengakses informasi layanan miliknya.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['slug'], ['name', 'description', 'updated_at']);
    }
}
