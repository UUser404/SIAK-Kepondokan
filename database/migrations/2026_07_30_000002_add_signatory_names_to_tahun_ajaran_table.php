<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nama penandatangan Rapor (Kepala Sekolah & Mudir Ma'had, dalam tulisan Arab)
 * -- diisi manual per tahun ajaran, karena sistem belum punya role/konsep
 * "Kepala Sekolah" yang terpisah dari "Mudir" (role RBAC yang ada sekarang
 * cuma 1: `mudir`). Wali kelas TIDAK perlu field baru -- sudah ada lewat
 * `kelas.wali_kelas_id` + `users.nama_arab`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->string('nama_kepala_sekolah_arab')->nullable()->after('tanggal_rapor_hijriah');
            $table->string('nama_mudir_arab')->nullable()->after('nama_kepala_sekolah_arab');
        });
    }

    public function down(): void
    {
        Schema::table('tahun_ajaran', function (Blueprint $table) {
            $table->dropColumn(['nama_kepala_sekolah_arab', 'nama_mudir_arab']);
        });
    }
};
