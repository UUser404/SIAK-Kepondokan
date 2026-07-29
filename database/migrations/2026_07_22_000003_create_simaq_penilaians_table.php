<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimaqPenilaiansTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * Membuat tabel simaq_penilaians untuk menyimpan semua catatan penilaian SIMAQ.
     * Menggunakan SoftDeletes untuk audit trail, dan constraints yang ketat untuk data integrity.
     */
    public function up(): void
    {
        Schema::create('simaq_penilaians', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('santri_id')
                ->constrained('santri')
                ->cascadeOnDelete()
                ->comment('Santri yang dinilai');

            $table->foreignId('guru_id')
                ->constrained('tenaga_pendidik')
                ->restrictOnDelete()
                ->comment('Guru yang membuat penilaian');

            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->restrictOnDelete()
                ->comment('Kelas santri saat penilaian');

            // Program & Jenis Penilaian
            $table->enum('program', ['hafalan', 'tilawah', 'tahsin'])
                ->comment('Program SIMAQ');

            $table->enum('jenis', ['setoran_harian', 'tasmi', 'pemantapan'])
                ->comment('Jenis penilaian');

            $table->date('tanggal')
                ->comment('Tanggal penilaian');

            // Data Hafalan (untuk program hafalan)
            $table->string('surah_ayat')
                ->nullable()
                ->comment('Surah dan ayat (misal: Al-Fatihah:1-7)');

            $table->integer('halaman')
                ->nullable()
                ->comment('Halaman dalam Al-Quran');

            $table->integer('juz')
                ->nullable()
                ->comment('Juz yang dihafal');

            // Komponen Kesalahan (count kesalahan per kategori)
            $table->integer('kesalahan_kelancaran')
                ->default(0)
                ->comment('Jumlah kesalahan kelancaran');

            $table->integer('kesalahan_tajwid')
                ->default(0)
                ->comment('Jumlah kesalahan tajwid');

            $table->integer('kesalahan_makhraj')
                ->default(0)
                ->comment('Jumlah kesalahan makharijul huruf');

            // Nilai Kalkulasi (menggunakan decimal untuk presisi 2 desimal)
            $table->decimal('nilai_kelancaran', 5, 2)
                ->nullable()
                ->comment('Nilai kelancaran (100 - kesalahan)');

            $table->decimal('nilai_tajwid', 5, 2)
                ->nullable()
                ->comment('Nilai tajwid (tier-based: 100/99/97/95)');

            $table->decimal('nilai_makhraj', 5, 2)
                ->nullable()
                ->comment('Nilai makharijul huruf (tier-based)');

            $table->decimal('nilai_akhir', 5, 2)
                ->nullable()
                ->comment('Nilai akhir penilaian (rata 3 komponen)');

            // Kriteria Penilaian
            $table->integer('bintang')
                ->nullable()
                ->comment('Bintang (1-5)');

            $table->string('huruf')
                ->nullable()
                ->comment('Huruf nilai (A+, A, B+, B, C+, C, D)');

            $table->string('predikat')
                ->nullable()
                ->comment('Predikat (Mumtaz, Jayyid, Maqbul)');

            // Status Kelulusan (hanya untuk ujian: tasmi, pemantapan)
            $table->enum('status_kelulusan', ['Lulus (Mutqin)', 'Lulus', 'Tidak Lulus'])
                ->nullable()
                ->comment('Status kelulusan (NULL untuk setoran_harian)');

            // Audit & Catatan
            $table->text('catatan')
                ->nullable()
                ->comment('Catatan penilaian dari guru');

            $table->timestamps();
            $table->softDeletes();

            // Indices untuk query cepat
            $table->index(['santri_id', 'tanggal']);
            $table->index(['guru_id', 'tanggal']);
            $table->index(['jenis', 'tanggal']);
            $table->index('program');
            $table->index('status_kelulusan');

            // Unique constraint untuk mencegah duplikat penilaian per santri/guru/jenis/tanggal
            $table->unique(['santri_id', 'guru_id', 'jenis', 'tanggal', 'deleted_at'], 'simaq_penilaian_unique_idx');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simaq_penilaians');
    }
}
