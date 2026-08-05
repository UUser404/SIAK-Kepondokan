<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ternyata dari template rapor asli, 4 kategori Kepribadian yang benar itu:
 * Akhlaq (=lama: akhlak, konsepnya sama, tidak diganti), Kerajinan (=lama:
 * kerapihan -- KONSEPNYA SALAH, "kerapihan" berarti rapi/tertata fisik,
 * padahal maksudnya "kerajinan"/ketekunan), Kebersihan (sama, tidak diganti),
 * Kedisiplinan (sama sebagai label, tapi behaviour auto-hitung dari presensi
 * dihapus di migration/kode terpisah -- lihat catatan di controller).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predikat_sikap', function (Blueprint $table) {
            $table->renameColumn('kerapihan', 'kerajinan');
        });
    }

    public function down(): void
    {
        Schema::table('predikat_sikap', function (Blueprint $table) {
            $table->renameColumn('kerajinan', 'kerapihan');
        });
    }
};
