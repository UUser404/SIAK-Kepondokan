<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sebelumnya kategori mata pelajaran (dipakai untuk pengelompokan di rapor)
 * di-hardcode sebagai array literal di resources/views/mata-pelajaran/_form.blade.php.
 * Tabel ini menjadikannya master data yang bisa di-CRUD lewat role admin.
 *
 * SENGAJA TIDAK mengubah kolom `mata_pelajaran.kategori` (string) menjadi
 * foreign key -- kolom itu kemungkinan dibaca oleh RaporArabService untuk
 * pengelompokan cetak rapor, dan tidak ada visibilitas penuh ke service itu
 * saat migration ini dibuat. `mata_pelajaran.kategori` tetap string dan tetap
 * jadi sumber kebenaran untuk rapor; tabel ini hanya jadi sumber daftar pilihan
 * (dropdown) yang bisa dikelola, dan controller-nya bertanggung jawab menjaga
 * konsistensi string itu (cascade-rename saat kategori di-edit, larang hapus
 * kategori yang masih dipakai mapel).
 *
 * 4 baris awal di-seed di sini supaya nilai `mata_pelajaran.kategori` yang
 * SUDAH ada di data lama tetap cocok dengan salah satu pilihan di tabel baru
 * ini setelah migration jalan (tidak ada mapel yang tiba-tiba "kategorinya
 * hilang dari daftar pilihan").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->unsignedTinyInteger('urutan')->nullable();
            $table->timestamps();
        });

        // Seed kategori yang sebelumnya hardcoded di _form.blade.php,
        // supaya data mata_pelajaran.kategori yang sudah ada tetap valid.
        DB::table('kategori_mata_pelajaran')->insert([
            ['nama' => 'القرآن و علومه', 'urutan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'العقيدة و الأخلاق', 'urutan' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'الشّريعة', 'urutan' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'اللغة العربية', 'urutan' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_mata_pelajaran');
    }
};
