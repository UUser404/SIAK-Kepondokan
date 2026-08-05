<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ketidakhadiran (sakit/izin/tanpa keterangan) yang tampil di Rapor & Leger --
 * default-nya dihitung otomatis dari PresensiKbm gabungan semua mapel
 * (PenilaianService::getPersentaseKehadiranTotal()), tapi wali kelas bisa
 * override manual di form Predikat Sikap yang sama (pola sama seperti
 * kategori kepribadian lainnya). Nullable: null berarti "pakai hasil
 * auto-hitung", diisi angka berarti override manual.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('predikat_sikap', function (Blueprint $table) {
            $table->unsignedSmallInteger('sakit_override')->nullable()->after('akhlak');
            $table->unsignedSmallInteger('izin_override')->nullable()->after('sakit_override');
            $table->unsignedSmallInteger('alpa_override')->nullable()->after('izin_override');
        });
    }

    public function down(): void
    {
        Schema::table('predikat_sikap', function (Blueprint $table) {
            $table->dropColumn(['sakit_override', 'izin_override', 'alpa_override']);
        });
    }
};
