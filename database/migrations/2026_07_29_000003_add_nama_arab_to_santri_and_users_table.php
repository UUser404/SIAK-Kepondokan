<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rapor & Leger butuh nama dalam tulisan Arab, terpisah dari nama Latin yang
 * sudah ada (santri.nama_lengkap, users.name) -- baik untuk santri maupun
 * staff yang tanda tangan di rapor (wali kelas, kepala sekolah, mudir ma'had).
 * Nullable karena data lama belum tentu langsung diisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->string('nama_arab')->nullable()->after('nama_lengkap');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_arab')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn('nama_arab');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nama_arab');
        });
    }
};
