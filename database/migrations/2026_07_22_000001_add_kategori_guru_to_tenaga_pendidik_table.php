<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKategoriGuruToTenagaPendidikTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom kategori_guru dan is_simaq_active untuk mendukung modul SIMAQ.
     * Semua data existing akan menggunakan default value, sehingga no data loss.
     */
    public function up(): void
    {
        Schema::table('tenaga_pendidik', function (Blueprint $table) {
            // Kolom kategori_guru untuk membedakan jenis guru
            // Default 'mapel' untuk backward compatibility
            $table->enum('kategori_guru', ['mapel', 'tahsin', 'tahfizh'])
                ->default('mapel')
                ->after('status_kepegawaian')
                ->comment('Kategori guru: mapel (mata pelajaran), tahsin, tahfizh');

            // Flag untuk menandai guru aktif dalam modul SIMAQ
            $table->boolean('is_simaq_active')
                ->default(false)
                ->after('kategori_guru')
                ->comment('Status aktif di modul SIMAQ');

            // Index untuk query filter cepat
            $table->index('kategori_guru');
            $table->index('is_simaq_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenaga_pendidik', function (Blueprint $table) {
            $table->dropIndex(['kategori_guru']);
            $table->dropIndex(['is_simaq_active']);
            $table->dropColumn(['kategori_guru', 'is_simaq_active']);
        });
    }
}
