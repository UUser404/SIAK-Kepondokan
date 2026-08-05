<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KKM ternyata berbeda per tingkatan untuk mapel yang sama (mis. Aqidah KKM 70
 * di tingkat 1-2, tapi 75 di tingkat 3-6) -- kolom `mata_pelajaran.kkm` yang
 * lama cuma 1 angka global per mapel, tidak cukup. Tabel ini jadi SUMBER
 * KEBENARAN baru untuk KKM: 1 baris per kombinasi mapel+tingkatan.
 *
 * `mata_pelajaran.kkm` (kolom lama) SENGAJA dibiarkan ada -- dipakai sebagai
 * fallback kalau kombinasi mapel+tingkatan tertentu belum diisi di tabel baru
 * ini (supaya tidak tiba-tiba jadi NULL/error di tempat yang masih baca kolom
 * lama sebelum semua data KKM per-tingkatan diisi lengkap).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kkm_tingkatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tingkatan_id')->constrained('tingkatan')->cascadeOnDelete();
            $table->unsignedTinyInteger('kkm');
            $table->timestamps();

            $table->unique(['mata_pelajaran_id', 'tingkatan_id'], 'idx_kkm_mapel_tingkatan_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kkm_tingkatan');
    }
};
