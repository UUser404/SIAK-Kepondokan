<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PERBAIKAN: NISN sekarang jadi identitas utama santri (wajib -- lihat
 * validasi di SantriController::store()/update()), NIS jadi opsional.
 * Migration awal (2025_01_01_000002_create_santri_pendidik_tables.php)
 * bikin kolom `nis` NOT NULL -- kalau tidak diubah, submit form/insert
 * tanpa NIS lolos validasi Laravel tapi GAGAL di level database
 * (SQLSTATE: Column 'nis' cannot be null). Ini juga blocker langsung buat
 * fitur Import Santri Baru (SantriCreateService) yang memang tidak
 * menyertakan NIS sama sekali.
 *
 * Unique constraint tetap dipertahankan -- aman untuk banyak baris NULL
 * sekaligus (MySQL/PostgreSQL/SQLite semua memperlakukan tiap NULL sebagai
 * nilai berbeda pada unique index, jadi tidak akan bentrok "duplicate
 * entry" walau ratusan santri sama-sama NULL di kolom nis).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->string('nis')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->string('nis')->nullable(false)->change();
        });
    }
};
