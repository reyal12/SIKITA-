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

        DB::table('peran')->upsert([
            [
                'kode' => 'administrator',
                'nama' => 'Administrator',
                'deskripsi' => 'Mengelola operasional klinik, akun, jadwal, dan transaksi.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'dokter',
                'nama' => 'Dokter',
                'deskripsi' => 'Mengelola jadwal praktik, kunjungan, rekam medis, dan resep.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'pasien',
                'nama' => 'Pasien',
                'deskripsi' => 'Mendaftar kunjungan dan mengakses informasi layanan miliknya.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['kode'], ['nama', 'deskripsi', 'updated_at']);
    }
}
