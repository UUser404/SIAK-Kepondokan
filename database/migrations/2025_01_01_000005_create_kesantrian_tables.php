<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asrama / Gedung
        Schema::create('asrama', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['putra', 'putri']);
            $table->string('pengurus')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Kamar
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asrama_id')->constrained('asrama')->cascadeOnDelete();
            $table->string('nomor_kamar');
            $table->integer('kapasitas')->default(4);
            $table->string('lantai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['asrama_id', 'nomor_kamar']);
        });

        // Penempatan Santri di Kamar
        Schema::create('penempatan_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Kategori Pelanggaran
        Schema::create('kategori_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tingkat', ['ringan', 'sedang', 'berat']);
            $table->integer('poin')->default(0);            // Poin pelanggaran
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Catatan Pelanggaran Santri
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kategori_pelanggaran_id')->constrained('kategori_pelanggaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('deskripsi');
            $table->string('sanksi')->nullable();
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Catatan Prestasi / Penghargaan Santri
        Schema::create('prestasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('nama_prestasi');
            $table->enum('jenis', ['akademik', 'non_akademik', 'hafalan', 'lainnya']);
            $table->enum('tingkat', ['pondok', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional']);
            $table->enum('peringkat', ['juara_1', 'juara_2', 'juara_3', 'harapan', 'peserta', 'lainnya'])->nullable();
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi');
        Schema::dropIfExists('pelanggaran');
        Schema::dropIfExists('kategori_pelanggaran');
        Schema::dropIfExists('penempatan_kamar');
        Schema::dropIfExists('kamar');
        Schema::dropIfExists('asrama');
    }
};
