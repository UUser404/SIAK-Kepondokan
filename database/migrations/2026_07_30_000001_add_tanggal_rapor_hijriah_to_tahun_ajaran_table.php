<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rapor Arab butuh tanggal cetak dalam format Hijriah (mis. "02 رجب 1447")
 * berdampingan dengan tanggal Masehi. Sengaja diisi MANUAL (bukan konversi
 * otomatis Masehi->Hijriah) karena penanggalan Hijriah bergantung rukyat/hisab
 * yang bisa beda 1-2 hari antar lembaga -- lebih aman & akurat kalau Kurikulum
 * yang menentukan sendiri tanggal resminya, sama seperti kop rapor yang sudah
 * ada (dicetak sekali per semester, bukan per santri).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->string('tanggal_rapor_hijriah')->nullable()->after('tanggal_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->dropColumn('tanggal_rapor_hijriah');
        });
    }
};
