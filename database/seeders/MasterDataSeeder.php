<?php
// ============================================================
// MasterDataSeeder.php
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use App\Models\MataPelajaran;
use App\Models\KomponenNilai;
use App\Models\JenisKegiatan;
use App\Models\KategoriPelanggaran;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Tahun Ajaran ----
        TahunAjaran::firstOrCreate(
            ['nama' => '2024/2025', 'semester' => 'genap'],
            [
                'tanggal_mulai'   => '2025-01-06',
                'tanggal_selesai' => '2025-06-20',
                'is_active'       => false,
            ]
        );
        TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026', 'semester' => 'ganjil'],
            [
                'tanggal_mulai'   => '2025-07-14',
                'tanggal_selesai' => '2025-12-19',
                'is_active'       => true,
            ]
        );

        // ---- Tingkatan (MTs setara SMP) ----
        $tingkatan = [
            ['nama' => 'Kelas 7', 'urutan' => 1],
            ['nama' => 'Kelas 8', 'urutan' => 2],
            ['nama' => 'Kelas 9', 'urutan' => 3],
        ];
        foreach ($tingkatan as $t) {
            \App\Models\Tingkatan::firstOrCreate(['nama' => $t['nama']], $t);
        }

        // ---- Mata Pelajaran ----
        $mapel = [
            ['kode' => 'QH',   'nama' => "Qur'an Hadits",        'kkm' => 75],
            ['kode' => 'AQ',   'nama' => 'Aqidah Akhlak',        'kkm' => 75],
            ['kode' => 'FIQ',  'nama' => 'Fiqih',                'kkm' => 75],
            ['kode' => 'SKI',  'nama' => 'Sejarah Kebudayaan Islam', 'kkm' => 70],
            ['kode' => 'ARB',  'nama' => 'Bahasa Arab',           'kkm' => 75],
            ['kode' => 'IND',  'nama' => 'Bahasa Indonesia',      'kkm' => 70],
            ['kode' => 'ENG',  'nama' => 'Bahasa Inggris',        'kkm' => 70],
            ['kode' => 'MAT',  'nama' => 'Matematika',            'kkm' => 70],
            ['kode' => 'IPA',  'nama' => 'Ilmu Pengetahuan Alam', 'kkm' => 70],
            ['kode' => 'IPS',  'nama' => 'Ilmu Pengetahuan Sosial', 'kkm' => 70],
            ['kode' => 'PKN',  'nama' => 'PPKn',                  'kkm' => 70],
            ['kode' => 'PJOK', 'nama' => 'PJOK',                  'kkm' => 70],
            ['kode' => 'MHD',  'nama' => 'Muhadatsah',            'kkm' => 75],
            ['kode' => 'MUT',  'nama' => "Muthala'ah",            'kkm' => 75],
            ['kode' => 'IML',  'nama' => 'Imla',                  'kkm' => 70],
            ['kode' => 'NHW',  'nama' => 'Nahwu',                 'kkm' => 75],
            ['kode' => 'SRF',  'nama' => 'Shorof',                'kkm' => 75],
            ['kode' => 'MHF',  'nama' => "Mahfudzot",             'kkm' => 70],
            ['kode' => 'TAH',  'nama' => 'Tahfidz',               'kkm' => 75],
        ];
        foreach ($mapel as $m) {
            MataPelajaran::firstOrCreate(['kode' => $m['kode']], array_merge($m, ['is_active' => true]));
        }

        // ---- Komponen Nilai ----
        $komponen = [
            ['nama' => 'Ulangan Harian', 'kode' => 'UH',      'bobot' => 20.00, 'urutan' => 1],
            ['nama' => 'Tugas',          'kode' => 'TUGAS',   'bobot' => 15.00, 'urutan' => 2],
            ['nama' => 'Praktik',        'kode' => 'PRAKTIK', 'bobot' => 15.00, 'urutan' => 3],
            ['nama' => 'UTS',            'kode' => 'UTS',     'bobot' => 20.00, 'urutan' => 4],
            ['nama' => 'UAS',            'kode' => 'UAS',     'bobot' => 30.00, 'urutan' => 5],
        ];
        foreach ($komponen as $k) {
            KomponenNilai::firstOrCreate(['kode' => $k['kode']], array_merge($k, ['is_active' => true]));
        }

        // ---- Jenis Kegiatan Harian Pondok ----
        $kegiatan = [
            ['nama' => 'Sholat Subuh Berjamaah',   'waktu_default' => '04:30'],
            ['nama' => 'Sholat Dzuhur Berjamaah',  'waktu_default' => '12:00'],
            ['nama' => 'Sholat Ashar Berjamaah',   'waktu_default' => '15:30'],
            ['nama' => 'Sholat Maghrib Berjamaah', 'waktu_default' => '18:00'],
            ['nama' => 'Sholat Isya Berjamaah',    'waktu_default' => '19:15'],
            ['nama' => 'Muhadhoroh',               'waktu_default' => '20:00'],
            ['nama' => 'Tahajud & Qiyamullail',    'waktu_default' => '03:00'],
            ['nama' => 'Halaqah Al-Quran',         'waktu_default' => '05:00'],
        ];
        foreach ($kegiatan as $k) {
            JenisKegiatan::firstOrCreate(['nama' => $k['nama']], array_merge($k, ['is_active' => true]));
        }

        // ---- Kategori Pelanggaran ----
        $kategori = [
            // Ringan
            ['nama' => 'Terlambat masuk kelas',        'tingkat' => 'ringan', 'poin' => 5],
            ['nama' => 'Tidak memakai seragam lengkap', 'tingkat' => 'ringan', 'poin' => 5],
            ['nama' => 'Tidak mengikuti piket',         'tingkat' => 'ringan', 'poin' => 5],
            ['nama' => 'Ribut di kelas',                'tingkat' => 'ringan', 'poin' => 10],
            // Sedang
            ['nama' => 'Tidak mengikuti kegiatan wajib', 'tingkat' => 'sedang', 'poin' => 20],
            ['nama' => 'Keluar pondok tanpa izin',        'tingkat' => 'sedang', 'poin' => 25],
            ['nama' => 'Membawa HP tanpa izin',           'tingkat' => 'sedang', 'poin' => 20],
            ['nama' => 'Berkelahi ringan',                'tingkat' => 'sedang', 'poin' => 30],
            // Berat
            ['nama' => 'Mencuri',                    'tingkat' => 'berat', 'poin' => 75],
            ['nama' => 'Merusak fasilitas pondok',   'tingkat' => 'berat', 'poin' => 50],
            ['nama' => 'Bullying / intimidasi',      'tingkat' => 'berat', 'poin' => 75],
            ['nama' => 'Pelanggaran syariat berat',  'tingkat' => 'berat', 'poin' => 100],
        ];
        foreach ($kategori as $k) {
            KategoriPelanggaran::firstOrCreate(['nama' => $k['nama']], $k);
        }

        $this->command->info('✅ Master data seeded.');
    }
}