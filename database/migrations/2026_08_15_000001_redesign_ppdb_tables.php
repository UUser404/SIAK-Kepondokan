<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redesign total PPDB -- menggantikan skema lama (ppdb_periode & ppdb_pendaftar
 * dari 2025_01_01_000006_create_ppdb_surat_log_tables.php) dengan skema baru
 * yang jauh lebih lengkap: data calon siswa detail, data kesehatan (penting
 * karena santri tinggal di asrama), data keluarga lengkap per orang tua,
 * jalur reguler/prestasi, dan alur verifikasi berkas -> pembayaran -> diterima.
 *
 * SENGAJA drop & recreate (bukan alter/tambah kolom) -- disepakati mulai dari
 * 0 karena PPDB masih tahap development, belum ada data pendaftar sungguhan
 * yang perlu dipertahankan.
 *
 * Status pendaftar SENGAJA dipecah jadi 3 KOLOM TERPISAH (status_berkas,
 * status_pembayaran, status_akhir), BUKAN 1 kolom status tunggal seperti
 * skema lama -- karena berkas, pembayaran, dan keputusan akhir itu masing-
 * masing punya siklus sendiri yang bisa maju-mundur independen (mis. berkas
 * sudah "terverifikasi" tapi pembayaran masih "menunggu_verifikasi" adalah
 * kombinasi valid, tidak bisa direpresentasikan dengan 1 enum datar).
 *
 * NIK & data pribadi lain SENGAJA disimpan plaintext (bukan dienkripsi) --
 * konsisten dengan cara SELURUH sistem ini nyimpen data pribadi (NIS, tanggal
 * lahir, alamat, dst di tabel santri semuanya plaintext), dan cek status
 * publik butuh WHERE nik = ? AND tanggal_lahir = ? yang tidak bisa jalan
 * kalau nik dienkripsi (hasil enkripsi beda tiap kali walau input sama).
 * Kalau project ini suatu saat mau naikkan level keamanan data pribadi,
 * itu perubahan besar yang harus berlaku ke SEMUA data pribadi di sistem
 * (bukan cuma PPDB) -- jangan cuma diterapkan sepihak di sini.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Urutan drop PENTING -- ppdb_pendaftar punya FK ke ppdb_periode,
        // jadi harus di-drop duluan sebelum ppdb_periode.
        Schema::dropIfExists('ppdb_pendaftar');
        Schema::dropIfExists('ppdb_periode');

        Schema::create('ppdb_periode', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. "PPDB 2026/2027"
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->date('tanggal_buka');
            $table->date('tanggal_tutup');
            $table->integer('kuota')->default(0);

            // Biaya & instruksi pembayaran -- SENGAJA per-periode (bukan
            // config global), karena biaya bisa beda tiap gelombang/tahun.
            $table->unsignedInteger('biaya_pendaftaran')->default(0);
            $table->text('info_pembayaran')->nullable(); // no. rekening, a.n., dst

            $table->boolean('is_active')->default(false);
            $table->text('persyaratan')->nullable();
            $table->timestamps();
        });

        Schema::create('ppdb_pendaftar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_periode_id')->constrained('ppdb_periode')->cascadeOnDelete();

            // Nomor internal buat referensi admin (cetak, arsip) -- BUKAN lagi
            // dipakai buat cek status publik (itu sekarang pakai NIK+tanggal
            // lahir, lihat catatan di atas soal kenapa nomor urut lama rawan
            // ditebak).
            $table->string('nomor_daftar')->unique();

            // ===== Jalur Pendaftaran =====
            $table->enum('jalur', ['reguler', 'prestasi'])->default('reguler');
            $table->string('bidang_prestasi')->nullable();     // isi cuma kalau jalur=prestasi
            $table->string('tingkat_prestasi')->nullable();    // kabupaten/provinsi/nasional
            $table->string('tahun_prestasi')->nullable();

            // ===== Data Calon Siswa =====
            $table->enum('jenjang', ['smp', 'sma']);
            $table->string('nama_lengkap');
            $table->string('nama_arab')->nullable();
            $table->string('nik', 20);
            $table->string('nisn', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->unsignedTinyInteger('anak_ke')->nullable();
            $table->unsignedTinyInteger('dari_bersaudara')->nullable();
            $table->string('golongan_darah', 3)->nullable();
            $table->string('asal_sekolah')->nullable();
            $table->string('asal_provinsi');
            $table->text('alamat')->nullable();
            $table->string('foto')->nullable(); // path pas foto

            // ===== Data Kesehatan (penting krn santri tinggal di asrama) =====
            $table->text('riwayat_penyakit')->nullable();
            $table->text('alergi_makanan')->nullable();
            $table->text('alergi_obat')->nullable();
            $table->text('obat_rutin')->nullable();

            // ===== Data Keluarga =====
            $table->string('no_kk', 20)->nullable();

            $table->string('nama_ayah')->nullable();
            $table->string('nik_ayah', 20)->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->unsignedInteger('penghasilan_ayah')->nullable();
            $table->string('pendidikan_ayah')->nullable();
            $table->string('no_hp_ayah', 20)->nullable();

            $table->string('nama_ibu')->nullable();
            $table->string('nik_ibu', 20)->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->unsignedInteger('penghasilan_ibu')->nullable();
            $table->string('pendidikan_ibu')->nullable();
            $table->string('no_hp_ibu', 20)->nullable();

            // Wali -- OPSIONAL, cuma diisi kalau bukan diasuh orang tua kandung
            $table->string('nama_wali')->nullable();
            $table->string('hubungan_wali')->nullable();
            $table->string('nik_wali', 20)->nullable();
            $table->string('no_hp_wali', 20)->nullable();
            $table->text('alamat_wali')->nullable();

            // Kontak darurat -- boleh beda dari wali utama di atas
            $table->string('nama_kontak_darurat')->nullable();
            $table->string('hubungan_kontak_darurat')->nullable();
            $table->string('no_hp_kontak_darurat', 20)->nullable();

            // ===== Riwayat Pendidikan Agama =====
            $table->boolean('pernah_tpa')->default(false);
            $table->boolean('pernah_mondok')->default(false);
            $table->string('nama_pesantren_asal')->nullable(); // isi kalau pernah_mondok=true
            $table->unsignedTinyInteger('estimasi_hafalan_juz')->nullable();

            // ===== Sumber Informasi =====
            $table->string('sumber_informasi')->nullable();
            $table->string('sumber_informasi_lainnya')->nullable(); // isi kalau sumber_informasi='lainnya'

            // ===== Status Berkas =====
            $table->enum('status_berkas', ['menunggu', 'ditolak', 'terverifikasi'])->default('menunggu');
            $table->text('catatan_berkas')->nullable();
            $table->foreignId('diverifikasi_berkas_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_berkas_at')->nullable();

            // ===== Status Pembayaran =====
            $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_verifikasi', 'ditolak', 'lunas'])
                ->default('belum_bayar');
            $table->string('bukti_pembayaran')->nullable(); // path upload bukti transfer
            $table->text('catatan_pembayaran')->nullable();
            $table->foreignId('diverifikasi_pembayaran_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_pembayaran_at')->nullable();

            // ===== Status Akhir =====
            $table->enum('status_akhir', ['proses', 'diterima', 'ditolak', 'mengundurkan_diri'])
                ->default('proses');
            $table->text('catatan_admin')->nullable();

            // Token QR -- di-generate begitu status_akhir jadi 'diterima'.
            // Detail pemakaian (kartu peserta, dsb) dibahas belakangan --
            // kolomnya disiapkan dulu di sini biar tidak perlu migration
            // tambahan lagi nanti.
            $table->string('qr_token')->nullable()->unique();

            // Link ke santri kalau sudah dikonversi (pola sama seperti skema lama)
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();

            $table->timestamps();

            // 1 NIK cuma boleh daftar 1x PER PERIODE -- tapi tetap boleh
            // daftar ulang di periode lain (mis. gagal tahun ini, coba lagi
            // tahun depan), jadi uniqueness di-scope ke kombinasi ini, bukan
            // NIK sendirian secara global.
            $table->unique(['ppdb_periode_id', 'nik']);
        });

        // ===== Berkas per pendaftar -- 1 baris per dokumen, verifikasi =====
        // ===== independen per dokumen (bukan 1 status buat semua berkas)  =====
        Schema::create('ppdb_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_pendaftar_id')->constrained('ppdb_pendaftar')->cascadeOnDelete();
            $table->enum('jenis', [
                'akta_kelahiran',
                'kartu_keluarga',
                'ktp_ayah',
                'ktp_ibu',
                'ktp_wali',
                'ijazah_skl',
                'rapor_semester_1',
                'rapor_semester_2',
                'surat_keterangan_sehat',
                'piagam_prestasi',
            ]);
            $table->string('file_path');
            $table->enum('status', ['menunggu', 'valid', 'tidak_valid'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diverifikasi_at')->nullable();
            $table->timestamps();

            // 1 pendaftar cuma boleh punya 1 baris aktif per jenis dokumen --
            // upload ulang WAJIB update baris yang sama (lewat updateOrCreate
            // di kode nanti), bukan numpuk baris baru per jenis yang sama.
            $table->unique(['ppdb_pendaftar_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_berkas');
        Schema::dropIfExists('ppdb_pendaftar');
        Schema::dropIfExists('ppdb_periode');
    }
};
