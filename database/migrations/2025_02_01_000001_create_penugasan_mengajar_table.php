<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penugasan Mengajar: pengganti "Jadwal Pelajaran" sebagai syarat guru
        // boleh input presensi/nilai/jurnal. Tanpa hari/jam — cuma menyatakan
        // "guru ini mengampu mapel X di kelas Y pada tahun ajaran Z".
        Schema::create('penugasan_mengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['guru_id', 'mata_pelajaran_id', 'kelas_id', 'tahun_ajaran_id'],
                'idx_penugasan_mengajar_unik'
            );
        });

        // jadwal_pelajaran_id sebelumnya WAJIB diisi setiap Pertemuan dibuat.
        // Karena presensi/jurnal sekarang tidak lagi berdasarkan jadwal
        // (guru input manual berdasarkan penugasan_mengajar), kolom ini
        // dibuat opsional. Data lama (yang masih terhubung ke jadwal) tidak
        // berubah/tidak hilang, hanya syarat "wajib" untuk pertemuan BARU
        // yang dihapus.
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->foreignId('jadwal_pelajaran_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->foreignId('jadwal_pelajaran_id')->nullable(false)->change();
        });

        Schema::dropIfExists('penugasan_mengajar');
    }
};
