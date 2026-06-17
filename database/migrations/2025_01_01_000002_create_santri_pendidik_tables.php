<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenaga Pendidik
        Schema::create('tenaga_pendidik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip')->unique()->nullable();     // Nomor Induk Pegawai
            $table->string('nik')->unique()->nullable();     // NIK KTP
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('pendidikan_terakhir')->nullable(); // S1, S2, dst
            $table->string('jurusan')->nullable();
            $table->enum('status_kepegawaian', ['tetap', 'kontrak', 'honorer'])->default('tetap');
            $table->date('tanggal_masuk')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        // Santri
        Schema::create('santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nis')->unique();                 // Nomor Induk Santri
            $table->string('nisn')->unique()->nullable();    // NISN Nasional
            $table->string('nama_lengkap');
            $table->string('nama_panggilan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat')->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('no_hp_santri')->nullable();
            // Wali
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('no_hp_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            // Pondok
            $table->enum('status', ['aktif', 'alumni', 'keluar', 'pindah'])->default('aktif');
            $table->integer('angkatan')->nullable();        // Tahun masuk
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        // Relasi Santri - Kelas (per tahun ajaran)
        Schema::create('santri_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->enum('status', ['aktif', 'naik', 'tinggal', 'keluar'])->default('aktif');
            $table->timestamps();

            $table->unique(['santri_id', 'tahun_ajaran_id']); // 1 santri = 1 kelas per TA
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_kelas');
        Schema::dropIfExists('santri');
        Schema::dropIfExists('tenaga_pendidik');
    }
};
