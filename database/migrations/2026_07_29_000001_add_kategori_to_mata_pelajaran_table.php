<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori pengelompokan mapel di rapor (mis. "القرآن و علومه", "الشّريعة",
 * "اللغة العربية", "العقيدة و الأخلاق") -- diisi manual oleh Admin lewat form
 * Mata Pelajaran yang sudah ada. Nullable karena mapel lama belum tentu
 * langsung diisi begitu migration ini jalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->string('kategori')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
