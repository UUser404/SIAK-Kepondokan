<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Komponen Penilaian (UH, Tugas, Praktik, UTS, UAS)
        Schema::create('komponen_nilai', function (Blueprint $table) {
            $table->id();
            $table->string('nama');              // e.g. "Ulangan Harian", "UTS"
            $table->string('kode');              // e.g. "UH", "UTS", "UAS"
            $table->decimal('bobot', 5, 2);      // Bobot persentase (e.g. 20.00)
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Nilai Santri per Komponen
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('komponen_nilai_id')->constrained('komponen_nilai')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // UBAH MENJADI:
            $table->unique(
                ['santri_id', 'kelas_id', 'mata_pelajaran_id', 'komponen_nilai_id', 'tahun_ajaran_id'],
                'idx_nilai_unik_gabungan' // 🚀 Nama kustom pendek (hanya 23 karakter, aman dari batas 64 karakter MySQL)
            );
        });

        // Rekap Nilai Akhir (hasil kalkulasi otomatis)
        Schema::create('nilai_akhir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->decimal('nilai_uh', 5, 2)->nullable();       // Rata-rata UH
            $table->decimal('nilai_tugas', 5, 2)->nullable();    // Rata-rata Tugas
            $table->decimal('nilai_praktik', 5, 2)->nullable();  // Rata-rata Praktik
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();    // Kalkulasi otomatis
            $table->string('predikat')->nullable();              // A, B, C, D
            $table->boolean('tuntas')->default(false);           // >= KKM
            $table->text('catatan_guru')->nullable();
            $table->timestamps();

            $table->unique(
                ['santri_id', 'kelas_id', 'mata_pelajaran_id', 'tahun_ajaran_id'],
                'idx_nilai_akhir_unik' // 🚀 Nama kustom pendek baru (aman dari limit MySQL)
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir');
        Schema::dropIfExists('nilai');
        Schema::dropIfExists('komponen_nilai');
    }
};
