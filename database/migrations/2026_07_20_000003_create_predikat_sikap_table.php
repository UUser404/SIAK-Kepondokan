<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predikat_sikap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();

            // Semua predikat A-E (label langsung, bukan angka), memakai skala yang sama
            // dengan predikat nilai akademik (config('siak.penilaian.predikat')).
            $table->string('kedisiplinan', 2); // Auto-hitung dari presensi gabungan, tapi bisa di-override manual
            $table->string('kebersihan', 2);   // Manual sepenuhnya
            $table->string('kerapihan', 2);    // Manual sepenuhnya
            $table->string('akhlak', 2);       // Manual sepenuhnya

            $table->text('catatan_wali_kelas')->nullable();
            $table->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['santri_id', 'tahun_ajaran_id'], 'idx_predikat_sikap_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predikat_sikap');
    }
};
