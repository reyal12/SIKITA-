<?php

namespace Database\Seeders;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('spesialis')->upsert([
            [
                'kode' => 'SP-UMUM',
                'nama' => 'Dokter Umum',
                'deskripsi' => 'Pelayanan kesehatan umum dan pemeriksaan awal.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'SP-ANAK',
                'nama' => 'Kesehatan Anak',
                'deskripsi' => 'Pemeriksaan dan pelayanan kesehatan bayi serta anak.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'SP-DALAM',
                'nama' => 'Penyakit Dalam',
                'deskripsi' => 'Pemeriksaan dan penanganan penyakit pada orang dewasa.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'SP-KANDUNGAN',
                'nama' => 'Kebidanan dan Kandungan',
                'deskripsi' => 'Pelayanan kesehatan ibu, kehamilan, dan organ reproduksi.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'SP-GIGI',
                'nama' => 'Kesehatan Gigi',
                'deskripsi' => 'Pemeriksaan dan perawatan kesehatan gigi dan mulut.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'kode' => 'SP-MATA',
                'nama' => 'Kesehatan Mata',
                'deskripsi' => 'Pemeriksaan dan pelayanan kesehatan mata.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ], ['kode'], ['nama', 'deskripsi', 'updated_at']);

        DB::table('spesialis')
            ->whereIn('kode', ['SP-KULIT', 'SP-BEDAH'])
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('dokter')
                    ->whereColumn('dokter.spesialis_id', 'spesialis.id');
            })
            ->delete();
    }
}
