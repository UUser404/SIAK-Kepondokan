<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Santri;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SantriDummySeeder extends Seeder
{
    /**
     * 10 santri dummy per kelas x 12 kelas = 120 santri, tahun ajaran 2026/2027.
     *
     * Idempotent: Tingkatan/TahunAjaran/Kelas pakai firstOrCreate (aman kalau
     * sebagian sudah ada dari seeder lain -- tidak akan bikin duplikat).
     * Santri & SantriKelas TIDAK di-firstOrCreate (memang niatnya generate baru
     * tiap dijalankan) -- kalau mau reset, hapus dulu datanya manual atau pakai
     * migrate:fresh.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ---- 1. Tingkatan (idempotent) ----
            $tingkatanMap = [];
            foreach ([
                ['nama' => '7',   'urutan' => 1],
                ['nama' => '8',   'urutan' => 2],
                ['nama' => '9',   'urutan' => 3],
                ['nama' => 'X',   'urutan' => 4],
                ['nama' => 'XI',  'urutan' => 5],
                ['nama' => 'XII', 'urutan' => 6],
            ] as $t) {
                $tingkatanMap[$t['nama']] = Tingkatan::firstOrCreate(
                    ['nama' => $t['nama']],
                    ['urutan' => $t['urutan']]
                );
            }

            // ---- 2. Tahun Ajaran 2026/2027 (idempotent, dijadikan aktif) ----
            $ta = TahunAjaran::firstOrCreate(
                ['nama' => '2026/2027', 'semester' => 'ganjil'],
                [
                    'tanggal_mulai'   => '2026-07-01',
                    'tanggal_selesai' => '2026-12-31',
                    'is_active'       => true,
                ]
            );
            if (!$ta->is_active) {
                TahunAjaran::where('is_active', true)->update(['is_active' => false]);
                $ta->update(['is_active' => true]);
            }

            // ---- 3. 12 Kelas (idempotent) ----
            $kelasNamaList = [
                '7A' => '7', '7B' => '7',
                '8A' => '8', '8B' => '8',
                '9A' => '9', '9B' => '9',
                'X-1' => 'X', 'X-2' => 'X',
                'XI-1' => 'XI', 'XI-2' => 'XI',
                'XII-1' => 'XII', 'XII-2' => 'XII',
            ];

            $kelasList = [];
            foreach ($kelasNamaList as $namaKelas => $namaTingkatan) {
                $kelasList[$namaKelas] = Kelas::firstOrCreate(
                    ['nama' => $namaKelas, 'tahun_ajaran_id' => $ta->id],
                    [
                        'tingkatan_id'   => $tingkatanMap[$namaTingkatan]->id,
                        'wali_kelas_id'  => null,
                        'kapasitas'      => 30,
                    ]
                );
            }

            // ---- 4. 10 santri dummy per kelas (120 total) ----
            $totalDibuat = 0;
            foreach ($kelasList as $namaKelas => $kelas) {
                $santriBaru = Santri::factory()->count(10)->create();

                foreach ($santriBaru as $santri) {
                    SantriKelas::create([
                        'santri_id'       => $santri->id,
                        'kelas_id'        => $kelas->id,
                        'tahun_ajaran_id' => $ta->id,
                        'status'          => 'aktif',
                    ]);
                }

                $totalDibuat += $santriBaru->count();
                $this->command->info("  → {$namaKelas}: 10 santri dummy dibuat.");
            }

            $this->command->info("✅ Selesai: {$totalDibuat} santri dummy dibuat & di-assign ke 12 kelas (TA {$ta->nama} {$ta->semester}).");
        });
    }
}
