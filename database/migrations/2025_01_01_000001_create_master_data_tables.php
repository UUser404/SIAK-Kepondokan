<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tahun Ajaran
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "2024/2025"
            $table->enum('semester', ['ganjil', 'genap']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['nama', 'semester']);
        });

        // Tingkatan (setara kelas/grade: 7, 8, 9 untuk MTs)
        Schema::create('tingkatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "Kelas 7", "Kelas 8", "Kelas 9"
            $table->integer('urutan'); // untuk sorting
            $table->timestamps();
        });

        // Kelas (rombel)
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "7A", "7B", "8A"
            $table->foreignId('tingkatan_id')->constrained('tingkatan')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('kapasitas')->default(30);
            $table->timestamps();
        });

        // Mata Pelajaran
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('kkm')->default(70); // Kriteria Ketuntasan Minimal
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Jadwal Pelajaran
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
        Schema::dropIfExists('mata_pelajaran');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('tingkatan');
        Schema::dropIfExists('tahun_ajaran');
    }
};
