<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2); // 0-100, predikat (Sangat Baik/dst) dihitung on-the-fly dari config
            $table->text('catatan')->nullable();
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['santri_id', 'ekstrakurikuler_id', 'tahun_ajaran_id'],
                'idx_nilai_ekskul_unik'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_ekstrakurikuler');
    }
};
