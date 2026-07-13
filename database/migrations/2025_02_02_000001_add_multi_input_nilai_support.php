<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cek apakah sebuah index/constraint dengan nama tertentu sudah ada di suatu tabel.
     * Dipakai supaya migration ini AMAN dijalankan ulang (idempotent) — MySQL tidak
     * transaksional untuk ALTER TABLE, jadi kalau migration sebelumnya gagal di tengah
     * jalan, sebagian perubahan bisa saja sudah kepatri permanen di database walau
     * migration-nya sendiri dianggap "belum selesai" oleh Laravel.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $dbName = Schema::getConnection()->getDatabaseName();

        $result = DB::select(
            "SELECT COUNT(1) as cnt FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $indexName]
        );

        return ($result[0]->cnt ?? 0) > 0;
    }

    public function up(): void
    {
        // 1) komponen_nilai.maks_input
        if (!Schema::hasColumn('komponen_nilai', 'maks_input')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->unsignedTinyInteger('maks_input')->default(1)->after('bobot');
            });
        }

        // 2) nilai.slot
        if (!Schema::hasColumn('nilai', 'slot')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->unsignedTinyInteger('slot')->default(1)->after('komponen_nilai_id');
            });
        }

        // 3) Ganti constraint unik lama -> yang baru (menyertakan slot)
        // PENTING: index BARU harus dibuat DULU sebelum yang LAMA dihapus.
        // Index lama (idx_nilai_unik_gabungan) dipakai MySQL/InnoDB sebagai index
        // pendukung untuk foreign key (santri_id, kelas_id, dst) di tabel ini -- kalau
        // langsung di-drop tanpa ada index pengganti dengan kolom awalan yang sama,
        // MySQL menolak dengan error 1553 "needed in a foreign key constraint".
        // Index baru punya kolom awalan persis sama (santri_id, kelas_id, ...), jadi begitu
        // dia ada, index lama jadi aman untuk dihapus.
        if (!$this->indexExists('nilai', 'idx_nilai_unik_gabungan_slot')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->unique(
                    ['santri_id', 'kelas_id', 'mata_pelajaran_id', 'komponen_nilai_id', 'tahun_ajaran_id', 'slot'],
                    'idx_nilai_unik_gabungan_slot'
                );
            });
        }

        if ($this->indexExists('nilai', 'idx_nilai_unik_gabungan')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->dropUnique('idx_nilai_unik_gabungan');
            });
        }

        // 4) Set default maks_input sesuai kode komponen (aman dijalankan berkali-kali)
        DB::table('komponen_nilai')->where('kode', 'UH')->update(['maks_input' => 2]);
        DB::table('komponen_nilai')->where('kode', 'PRAKTIK')->update(['maks_input' => 2]);
        DB::table('komponen_nilai')->where('kode', 'TUGAS')->update(['maks_input' => 4]);
    }

    public function down(): void
    {
        // Sama seperti up(): buat index pengganti dulu, baru hapus yang mau dibuang
        if (!$this->indexExists('nilai', 'idx_nilai_unik_gabungan')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->unique(
                    ['santri_id', 'kelas_id', 'mata_pelajaran_id', 'komponen_nilai_id', 'tahun_ajaran_id'],
                    'idx_nilai_unik_gabungan'
                );
            });
        }

        if ($this->indexExists('nilai', 'idx_nilai_unik_gabungan_slot')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->dropUnique('idx_nilai_unik_gabungan_slot');
            });
        }

        if (Schema::hasColumn('nilai', 'slot')) {
            Schema::table('nilai', function (Blueprint $table) {
                $table->dropColumn('slot');
            });
        }

        if (Schema::hasColumn('komponen_nilai', 'maks_input')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->dropColumn('maks_input');
            });
        }
    }
};
