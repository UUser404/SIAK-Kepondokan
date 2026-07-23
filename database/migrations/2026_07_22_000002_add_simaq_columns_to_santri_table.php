<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSimaqColumnsToSantriTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom-kolom untuk modul SIMAQ (Sistem Penilaian Al-Quran).
     * Semua kolom NULLABLE untuk production safety - data existing tidak terdampak.
     */
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            // Target hafalan juz
            $table->integer('target_juz')
                ->nullable()
                ->after('angkatan')
                ->comment('Target juz yang ingin dihafal');

            // Total nilai SIMAQ (rata-rata dari semua penilaian)
            $table->decimal('simaq_total_nilai', 5, 2)
                ->nullable()
                ->after('target_juz')
                ->comment('Total nilai SIMAQ (rata-rata)');

            // Total bintang SIMAQ (aggregate dari semua penilaian)
            $table->integer('simaq_total_bintang')
                ->nullable()
                ->after('simaq_total_nilai')
                ->comment('Total bintang SIMAQ');

            // Total setoran (counter penilaian setoran_harian)
            $table->integer('simaq_total_setoran')
                ->nullable()
                ->after('simaq_total_bintang')
                ->comment('Total setoran hafalan');

            // Badge pencapaian (Penghafal Awal, Hafiz Muda, dsb)
            $table->string('simaq_badge')
                ->nullable()
                ->after('simaq_total_setoran')
                ->comment('Badge pencapaian SIMAQ');

            // Program yang sedang diikuti
            $table->enum('simaq_program', ['hafalan', 'tilawah', 'tahsin'])
                ->nullable()
                ->after('simaq_badge')
                ->comment('Program SIMAQ: hafalan, tilawah, tahsin');

            // Juz terakhir yang tercapai/dikuasai
            $table->integer('simaq_juz_tercapai')
                ->default(0)
                ->after('simaq_program')
                ->comment('Juz terakhir yang tercapai');

            // Indices untuk query cepat
            $table->index('simaq_program');
            $table->index('simaq_badge');
            $table->index('simaq_juz_tercapai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropIndex(['simaq_program']);
            $table->dropIndex(['simaq_badge']);
            $table->dropIndex(['simaq_juz_tercapai']);
            $table->dropColumn([
                'target_juz',
                'simaq_total_nilai',
                'simaq_total_bintang',
                'simaq_total_setoran',
                'simaq_badge',
                'simaq_program',
                'simaq_juz_tercapai',
            ]);
        });
    }
}
