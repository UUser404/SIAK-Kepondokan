<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pertemuan KBM (satu sesi mengajar)
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('pertemuan_ke')->nullable();    // Pertemuan ke-n
            $table->string('topik')->nullable();
            $table->text('materi')->nullable();
            $table->text('catatan_guru')->nullable();
            $table->enum('status', ['terlaksana', 'tidak_terlaksana', 'pengganti'])->default('terlaksana');
            $table->timestamps();
        });

        // Presensi Santri per Pertemuan KBM
        Schema::create('presensi_kbm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_id', 'santri_id']);
        });

        // Presensi Kehadiran Guru
        Schema::create('presensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();
            $table->enum('status', ['hadir', 'sakit', 'izin', 'cuti', 'alpa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']);
        });

        // Jenis Kegiatan Pondok (sholat berjamaah, muhadhoroh, dll)
        Schema::create('jenis_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->time('waktu_default')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Presensi Kegiatan Harian Pondok
        Schema::create('presensi_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('jenis_kegiatan_id')->constrained('jenis_kegiatan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['santri_id', 'jenis_kegiatan_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_kegiatan');
        Schema::dropIfExists('jenis_kegiatan');
        Schema::dropIfExists('presensi_guru');
        Schema::dropIfExists('presensi_kbm');
        Schema::dropIfExists('pertemuan');
    }
};
