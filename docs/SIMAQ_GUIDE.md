# Developer Guide — SIMAQ (Sistem Manajemen Al-Qur'an)

> **Tujuan dokumen ini:** Referensi teknis eksklusif untuk modul SIMAQ. SIMAQ adalah sub-sistem mandiri yang menempel pada SIAK Kepondokan, khusus menangani program Tahsin dan Tahfizh. 
> 
> ⚠️ **PERINGATAN PENTING:** SIMAQ memiliki ekosistem penilaian dan otorisasi yang BERBEDA dari sistem Akademik SIAK Utama. Jangan menerapkan logika `PenugasanMengajar` atau `KomponenNilai` SIAK ke dalam modul ini.

---

## Daftar Isi
1. [Konteks & Filosofi Desain](#konteks--filosofi-desain)
2. [Otorisasi & Role](#otorisasi--role)
3. [Alur Bisnis Inti](#alur-bisnis-inti)
4. [Mesin Penilaian (Scoring Service)](#mesin-penilaian)
5. [Sinkronisasi Nilai (Hybrid System)](#sinkronisasi-nilai)

---

## 1. Konteks & Filosofi Desain

SIMAQ dirancang untuk berjalan berdampingan dengan SIAK, namun tidak terikat dengan jadwal akademik konvensional. 
* **Bebas Kelas/Penugasan Kaku:** Berbeda dengan guru SIAK yang aksesnya dikunci lewat tabel `penugasan_mengajar`, asatidz (guru) SIMAQ dapat menilai santri dari berbagai halaqoh tanpa terikat *mapping* kelas formal.
* **Fokus pada Progres, Bukan Semester:** SIMAQ melacak kelancaran, hafalan juz, dan makhraj secara berkelanjutan.

---

## 2. Otorisasi & Role

Modul SIMAQ diakses melalui *prefix route* `simaq.*`.
Akses ke modul ini dilindungi oleh *middleware* Spatie dan dibatasi secara eksklusif untuk 3 role berikut:
* `guru_tahsin_tahfizh` (Akses input nilai & dashboard asatidz)
* `admin` (Akses rekapitulasi)
* `super_admin` / `sysadmin` (Akses penuh)

**Catatan UI:** Menu SIMAQ di *sidebar* memiliki kotak hijau-oranye khusus untuk membedakannya secara visual dari menu SIAK. Pemanggilan di `.blade.php` harus selalu memverifikasi role menggunakan:
`@if(Auth::user()->hasRole(['guru_tahsin_tahfizh', 'admin', 'super_admin']))`

---

## 3. Alur Bisnis Inti

Modul SIMAQ dibagi menjadi 4 jalur input utama (`SimaqController`):
1. **Setoran Harian:** Evaluasi harian kelancaran, tajwid, dan makhraj.
2. **Ujian Pemantapan:** Evaluasi berkala untuk kenaikan tingkat/juz.
3. **Imtihan Tasmi':** Ujian hafalan sekali duduk (disimak oleh audiens).
4. **Jam'iyyatul Huffazh:** Program khusus pembinaan hafizh tingkat lanjut.

Semua data penilaian disimpan dalam satu tabel utama: **`simaq_penilaians`**.

---

## 4. Mesin Penilaian (Scoring Service)

Kalkulasi nilai SIMAQ **TIDAK** menggunakan fungsi rata-rata `avg()` bawaan Eloquent seperti pada Rapor SIAK. Seluruh kalkulasi disentralisasi di dalam:
`App\Services\SimaqScoringService`

**Konsep Kalkulasi:**
* Guru hanya menginput **jumlah kesalahan** (Kesalahan Kelancaran, Kesalahan Tajwid, Kesalahan Makhraj).
* `SimaqScoringService` akan memproses jumlah kesalahan tersebut menjadi **Nilai Angka** (1-100) berdasarkan rumus reduksi.
* Nilai Angka kemudian dikonversi secara otomatis menjadi **Predikat Huruf** (A, B, C, D) dan Bintang (Jaudah).

*Rule of thumb:* Jangan pernah melakukan operasi aritmatika nilai SIMAQ langsung di dalam *Controller*. Selalu lemparkan *array* input ke `$this->scoringService->calculatePenilaian()`.

---

## 5. Sinkronisasi Nilai (Hybrid System)

Meskipun SIMAQ memiliki fitur **Cetak Rapor SIMAQ** sendiri (PDF mandiri), hasil akhir SIMAQ (akumulasi nilai) disinkronkan ke dalam **Rapor Akademik SIAK Utama** pada akhir semester.

**Alur Sinkronisasi (`SimaqController@syncToRapor`):**
1. Sistem akan mencari Mata Pelajaran di SIAK yang namanya mengandung kata `Tahfizh`, `Tahsin`, atau `SIMAQ` menggunakan operator `LIKE`.
2. Jika mapel ditemukan, sistem akan menarik `simaq_total_nilai` dari entitas `Santri`.
3. Menggunakan `updateOrCreate()`, nilai tersebut akan disuntikkan secara paksa ke dalam tabel `nilai_akhir` milik SIAK Utama.

**Catatan Integrasi:** Jika nama mata pelajaran tahfizh di Master Data SIAK diubah menjadi nama lain (misal: "Al-Qur'an Hadits"), proses sinkronisasi ini akan terputus. Pastikan Admin SIAK mempertahankan penamaan standar tersebut.