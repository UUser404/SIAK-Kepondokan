# Developer & AI Agent Guide — SIAK-AI Kepondokan

> **Tujuan dokumen ini:** referensi teknis lengkap supaya developer manusia maupun AI coding agent (Claude Code, dsb.) bisa langsung paham arsitektur sistem ini tanpa perlu membaca ulang seluruh kode dari nol — termasuk keputusan desain yang **sengaja menyimpang** dari dokumen SRS awal, dan bug-bug yang **sudah pernah ditemukan & diperbaiki** (supaya tidak diperbaiki ulang atau, lebih buruk, diperkenalkan lagi).
>
> **Cara pakai:** baca bagian [Aturan Main Sebelum Mengubah Kode](#aturan-main-sebelum-mengubah-kode) dulu sebelum menyentuh modul Penugasan/Nilai/Presensi. Sisanya bisa dibaca sesuai kebutuhan per modul.

---

## Daftar Isi

1. [Konteks & Sejarah Proyek](#konteks--sejarah-proyek)
2. [Arsitektur & RBAC](#arsitektur--rbac)
3. [Alur Bisnis Inti: Penugasan → Presensi/Nilai/Jurnal](#alur-bisnis-inti-penugasan--presensinilaijurnal)
4. [Sistem Penilaian](#sistem-penilaian)
5. [Skema Data](#skema-data)
6. [Modul per Role](#modul-per-role)
7. [Konvensi Kode & Folder](#konvensi-kode--folder)
8. [Aturan Main Sebelum Mengubah Kode](#aturan-main-sebelum-mengubah-kode)
9. [Riwayat & Catatan Perbaikan](#riwayat--catatan-perbaikan)
10. [Masalah yang Diketahui, Belum Diperbaiki](#masalah-yang-diketahui-belum-diperbaiki)
11. [Kredensial Testing](#kredensial-testing)
12. [Modul/ Dokumentasi SIMAQ] Untuk pengembangan, perubahan, dan logika sistem khusus program Tahsin/Tahfizh (SIMAQ), silakan merujuk secara eksklusif ke dokumen terpisah: [SIMAQ_GUIDE.md](./SIMAQ_GUIDE.md).

---

## Konteks & Sejarah Proyek

Sistem ini bermula dari dokumen **System Requirement Specification (SRS)** formal untuk "Sistem Informasi Akademik Kepondokan" di Pondok Pesantren Modern Al Islam (tugas kuliah Rekayasa Perangkat Lunak). Implementasi aktual **berkembang jauh melampaui** SRS aslinya:

- SRS merancang ERD dengan field JSONB (`data_penugasan`, `nilai_data`, `presensi_data`) — implementasi aktual **ternormalisasi penuh** (tabel relasional per entitas), lebih baik untuk query & validasi.
- SRS tidak menyebut fitur **PPDB Online**, **Surat-Menyurat**, **Prestasi Santri**, **Activity Log** — semua ini ditambahkan di implementasi karena kebutuhan nyata.
- Modul **Jadwal Pelajaran** (hari/jam) yang dirancang di SRS awal **digantikan** oleh **Penugasan Mengajar** (lihat [§3](#alur-bisnis-inti-penugasan--presensinilaijurnal)) setelah didiskusikan ulang — pondok ini ternyata tidak butuh penjadwalan kaku per jam, cukup "guru X mengajar mapel Y di kelas Z".
- Sistem penilaian berkembang dari "1 nilai per komponen per semester" (sesuai SRS) menjadi **multi-input per komponen** (lihat [§4](#sistem-penilaian)) setelah kebutuhan riil pondok terungkap: guru bisa kasih Tugas berkali-kali dalam satu semester.

**Prinsip penting:** kalau menemukan sesuatu di kode yang terasa "tidak sesuai SRS", itu **kemungkinan besar disengaja** — cek dulu bagian [Riwayat & Catatan Perbaikan](#riwayat--catatan-perbaikan) sebelum mengembalikannya ke perilaku SRS asli.

---

## Arsitektur & RBAC

### Stack

Laravel 13, PHP 8.3+, Blade + TailwindCSS (bukan SPA/API-first), MySQL/PostgreSQL/SQLite.

### 6 Role

`sysadmin`, `mudir`, `wakil_kurikulum`, `guru`, `kesantrian`, `admin` — disimpan di kolom `users.role` (string tunggal).

### RBAC Ganda (Sengaja, Bukan Bug)

Sistem punya **dua mekanisme role** yang berjalan bersamaan:

1. **`users.role`** (string) — dipakai untuk routing dashboard (`getDashboardRoute()`), helper cepat (`$user->isGuru()`, dst), dan ditampilkan di UI.
2. **Spatie `laravel-permission`** (`model_has_roles`, `permissions`) — dipakai di **middleware route** (`role:guru`, `role:wakil_kurikulum|sysadmin`, dst) untuk penegakan akses yang sesungguhnya.

Kedua mekanisme ini **disinkronkan manual** setiap kali user dibuat/diedit lewat `Sysadmin\UserController` (`$user->role = ...` **dan** `$user->syncRoles([...])` dipanggil bersamaan). Kalau menambah cara baru untuk membuat/mengedit user (mis. seeder baru, import massal), **wajib** sinkronkan keduanya, atau middleware `role:` akan gagal mendeteksi akses meski `users.role` sudah benar.

### Struktur Route

Semua route di `routes/web.php`, dikelompokkan per prefix + middleware role:

```php
Route::prefix('admin')->name('admin.')->middleware('role:admin|sysadmin')->group(...);
Route::prefix('kurikulum')->name('kurikulum.')->middleware('role:wakil_kurikulum|sysadmin')->group(...);
Route::prefix('guru')->name('guru.')->middleware('role:guru')->group(...);
Route::prefix('kesantrian')->name('kesantrian.')->middleware('role:kesantrian|sysadmin')->group(...);
Route::prefix('sysadmin')->name('sysadmin.')->middleware('role:sysadmin')->group(...);
```

````

⚠️ **Jangan pakai `Route::resource()` untuk controller yang tidak punya ke-7 method standarnya** (`index/create/store/show/edit/update/destroy`). Ini sudah dua kali jadi sumber bug ("ghost route" — route terdaftar tapi controller-nya tidak punya method itu, jadi 500 error kalau diakses). Lihat [§9](#riwayat--catatan-perbaikan) poin PPDB & Kamar.

---

## Alur Bisnis Inti: Penugasan → Presensi/Nilai/Jurnal

Ini **aturan akses paling penting** di seluruh sistem — banyak modul bergantung padanya.

### Model: `PenugasanMengajar`

```
penugasan_mengajar
- guru_id            (FK users.id)
- mata_pelajaran_id  (FK mata_pelajaran.id)
- kelas_id           (FK kelas.id)
- tahun_ajaran_id    (FK tahun_ajaran.id)
- unique(guru_id, mata_pelajaran_id, kelas_id, tahun_ajaran_id)
```

**Cara kerja (dari sisi Kurikulum):**

1. Wakil Kurikulum buka menu **Penugasan** (`kurikulum.penugasan.*`), pilih guru.
2. Pilih **1 mata pelajaran**, centang **kelas mana saja** yang diampu guru itu untuk mapel tersebut (boleh banyak kelas sekaligus).
3. Simpan → sistem generate satu baris `PenugasanMengajar` per kelas yang dicentang.
4. Ulangi langkah 2-3 kalau guru itu juga mengampu mapel lain.

**Cara kerja (dari sisi Guru):** guru **tidak bisa** input presensi, nilai, atau jurnal untuk kelas+mapel manapun **kecuali** ada baris `PenugasanMengajar` yang cocok (`guru_id` + `kelas_id` + `mata_pelajaran_id`). Ini ditegakkan di:

- `Guru\NilaiController::show()` / `bulkStore()` — `PenugasanMengajar::where(...)->firstOrFail()`
- `Guru\PresensiController::create()` — route model binding ke `PenugasanMengajar`, plus `abort_if($penugasan->guru_id !== Auth::id(), 403)`

**Kalau butuh cek "guru ini boleh akses kelas+mapel X atau tidak", selalu query lewat `PenugasanMengajar` — jangan pernah asumsikan dari `TenagaPendidik` atau `JadwalPelajaran`.**

### Kenapa Bukan Lagi `JadwalPelajaran`?

`JadwalPelajaran` (hari, jam_mulai, jam_selesai, ruangan) **masih ada di database dan kode**, tapi **sengaja tidak dipakai** sebagai gerbang akses lagi (menu disembunyikan dari sidebar). Alasan: pondok ini tidak butuh penjadwalan kaku — guru cukup tahu kelas & mapel yang diampu, lalu input presensi/nilai kapan saja sesuai kebutuhan riil, tanggal diisi manual (dibatasi `before_or_equal:today`).

Kalau suatu saat fitur jadwal hari/jam mau diaktifkan lagi: kodenya utuh di `Kurikulum\JadwalController` + route `kurikulum.jadwal.*` (cuma di-comment dari sidebar `resources/views/layouts/partials/sidebar-nav.blade.php`), tapi **jangan otomatis jadikan gerbang akses lagi** tanpa didiskusikan — itu perubahan arsitektur, bukan sekadar toggle UI.

### Dampak Berantai

Karena `Pertemuan` (dasar Presensi & Jurnal) awalnya **wajib** referensi `jadwal_pelajaran_id` (NOT NULL constraint), migrasi `2025_02_01_000001_create_penugasan_mengajar_table.php` mengubahnya jadi **nullable**. Pertemuan baru dibuat dengan `jadwal_pelajaran_id => null`, data lama (kalau ada) tetap utuh.

---

## Sistem Penilaian

### Struktur Dasar

```
komponen_nilai          nilai
- kode (UH/TUGAS/...)   - santri_id, kelas_id, mata_pelajaran_id, tahun_ajaran_id
- bobot (%)             - komponen_nilai_id
- maks_input             - slot           <- input ke berapa untuk komponen ini
```

### Default Komponen (dari `MasterDataSeeder`)

| Kode      | Nama           | Bobot | Maks Input |
| --------- | -------------- | ----- | ---------- |
| `UH`      | Ulangan Harian | 20%   | 2x         |
| `TUGAS`   | Tugas          | 15%   | 4x         |
| `PRAKTIK` | Praktik        | 15%   | 2x         |
| `UTS`     | UTS            | 20%   | 1x         |
| `UAS`     | UAS            | 30%   | 1x         |

`maks_input` bisa diubah admin/kurikulum lewat menu **Data Master → Komponen Nilai**, tidak hardcode.

### Aturan Kalkulasi ("Cara A" — hasil diskusi eksplisit, JANGAN diubah tanpa alasan kuat)

1. **Per siswa, independen dari siswa lain.** Nilai siswa A tidak pernah dipengaruhi nilai/jumlah input siswa B, walau satu kelas yang sama. _(Sempat dipertimbangkan model "class-wide" di mana pembagi rata-rata sama untuk semua siswa sekelas — **ditolak** karena terlalu kompleks & tidak lazim di sistem akademik.)_
2. **Slot kosong = dilewati, BUKAN dianggap 0.** Kalau guru cuma isi Tugas 1-3 dari maksimal 4, nilai akhir komponen Tugas siswa itu = rata-rata dari 3 nilai yang ada saja. **Ini keputusan sadar** — mengisi 0 untuk yang tidak dikerjakan adalah **tanggung jawab disiplin guru secara manual**, bukan otomatis dari sistem.
3. **Bobot dihitung per-komponen-sebagai-kesatuan, bukan per-slot.** Kontribusi Tugas ke nilai akhir = `rata-rata(semua slot Tugas yang terisi) × 15%` — **bukan** `bobot 15% dibagi 4 lalu dikali tiap slot`. Ini penting: siswa yang cuma dapat 3 tugas (bukan salahnya) tidak dirugikan dibanding yang dapat 4 tugas.

Implementasi rumus ada di `PenilaianService::hitungNilaiAkhir()` — method ini query `Nilai::where([santri, kelas, mapel, komponen, tahun_ajaran])->avg('nilai')` per komponen (otomatis merata-ratakan berapapun slot yang ada), lalu jumlahkan `rata × bobot/100` untuk semua komponen yang punya data. **Method ini sudah benar dan tidak perlu diubah** kalau menambah/mengubah komponen baru — cukup pastikan constraint unik tabel `nilai` (`santri+kelas+mapel+komponen+tahun_ajaran+slot`) tetap dihormati saat insert.

### Alur Guru Input Nilai

`Guru\NilaiController::bulkStore()` menerima form nested: `nilai[santri_id][komponen_id][slot] = angka`. Validasi memastikan `slot` tidak melebihi `maks_input` komponen tersebut (slot di luar batas **diabaikan diam-diam**, bukan error keras — supaya tidak mempersulit guru kalau ada race condition kecil di form).

### Tempat yang TIDAK perlu disentuh kalau menambah slot/komponen baru

`PenilaianService::hitungNilaiAkhir()` dan `PenilaianService::getRekapNilaiKelas()` (dipakai halaman rekap Kurikulum + `NilaiExport`) **keduanya sudah generic** — pakai `avg('nilai')` per komponen tanpa asumsi jumlah slot. Aman dari perubahan `maks_input` kapan saja.

---

## Skema Data

### Entitas Inti

```
Users ──┬── TenagaPendidik (1:1, guru_id = user_id)
        ├── PenugasanMengajar (1:banyak, sebagai guru)
        └── Rombel/Kelas (1:banyak, sebagai wali_kelas)

Kelas ──┬── Santri (1:banyak)
        └── PenugasanMengajar (1:banyak)

Santri ──┬── Nilai (1:banyak)
         ├── PresensiKbm (lewat Pertemuan)
         ├── Pelanggaran (1:banyak)
         ├── Prestasi (1:banyak)
         ├── PresensiKegiatan (1:banyak, kesantrian)
         └── PenempatanKamar (riwayat kamar)

PenugasanMengajar ──┬── (dipakai sebagai gerbang akses, TIDAK punya relasi langsung ke Pertemuan)

Pertemuan ──┬── PresensiKbm (1:banyak, per santri)
            └── guru_id/kelas_id/mata_pelajaran_id (kolom langsung, TIDAK cuma dari jadwal_pelajaran_id)

Nilai ── unique(santri, kelas, mapel, komponen_nilai, tahun_ajaran, slot)

KomponenNilai ── maks_input (batas slot), bobot (%)

Kamar ──┬── PenempatanKamar (riwayat, ada status aktif/keluar)
        └── Asrama (belongsTo)

Pelanggaran ── belongsTo KategoriPelanggaran (punya `poin`, dipakai hitung ambang panggilan wali/skors/dikeluarkan)

PpdbPeriode ──── PpdbPendaftar (1:banyak, status: pending/diterima/ditolak)

SuratKeluar ──── belongsTo TemplateSurat (opsional), belongsTo Santri (opsional)

MataPelajaran ── kolom `kategori` (string) dicocokkan by NAMA (bukan foreign key)
                 ke KategoriMataPelajaran.nama — lihat §9 poin 16 untuk alasannya.
```

### Penamaan Kolom yang Sering Salah Diasumsikan (cek dulu sebelum pakai!)

| Yang sering ditebak                                                    | Yang benar                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| ---------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `pelanggaran.tanggal_pelanggaran`                                      | `pelanggaran.tanggal`                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| `pelanggaran->kategoriPelanggaran` (relasi)                            | `pelanggaran->kategori()`                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| `nilai_akhir.semester` / `tahun_ajar` (string)                         | tidak ada — pakai relasi `tahunAjaran()` ke tabel `tahun_ajaran`                                                                                                                                                                                                                                                                                                                                                                                                |
| `kamar.nama_kamar`                                                     | `kamar.nomor_kamar`                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| `tenaga_pendidik.nama`                                                 | tidak ada — nama guru ada di `users.name` lewat relasi `user()`                                                                                                                                                                                                                                                                                                                                                                                                 |
| `jadwal_pelajaran.guru_id` mengacu ke `tenaga_pendidik.id`             | **salah** — mengacu ke `users.id` langsung                                                                                                                                                                                                                                                                                                                                                                                                                      |
| `santri->kelasAktif()->kelas_id`                                       | **salah** — `kelasAktif()` adalah `hasOneThrough` yang me-return model `Kelas` langsung, jadi PK-nya `id`, bukan `kelas_id` (kolom `kelas_id` tidak ada di tabel `kelas`)                                                                                                                                                                                                                                                                                       |
| `santri.user_id` selalu terisi                                         | **salah (sejak 2026-07-13)** — kolom ini nullable karena santri tidak pernah login (tidak ada role `santri`); jangan asumsikan tidak pernah null                                                                                                                                                                                                                                                                                                                |
| `mata_pelajaran.kategori` adalah foreign key                           | **salah (sejak 2026-08-11)** — tetap kolom string biasa, dicocokkan by nilai `nama` ke tabel `kategori_mata_pelajaran`, bukan `kategori_mata_pelajaran_id`; lihat §9 poin 16                                                                                                                                                                                                                                                                                    |
| `mata_pelajaran.kkm` adalah sumber KKM yang dipakai sistem             | **salah (sejak 2026-08-11)** — kolom ini cuma fallback; sumber utama KKM adalah tabel `kkm_tingkatan` lewat method `MataPelajaran::kkmUntukTingkatan($tingkatanId)`. Jangan baca `->kkm` langsung kalau ada konteks tingkatan/kelas; lihat §9 poin 17                                                                                                                                                                                                           |
| `mata_pelajaran.tingkat` membatasi mapel ke satu tingkat tertentu      | **salah (dead field sejak 2026-08-11)** — kolom masih ada di DB tapi tidak dipakai UI/validasi manapun, dan kemungkinan besar sudah lama gagal ke-save (bukan di `$fillable`). Jangan diandalkan buat filter apapun; lihat §9 poin 21                                                                                                                                                                                                                           |
| `NilaiAkhir.nilai_akhir = 0` berarti santri dinilai nol                | **belum tentu** — bisa juga berarti "mapel ini tidak pernah diajarkan ke santri itu" (tidak ada baris `Nilai` sama sekali). Sejak §9 poin 22, `finalize()` sudah dijaga supaya baris `NilaiAkhir` untuk kombinasi kelas+mapel yang tidak ditugaskan (`PenugasanMengajar`) TIDAK PERNAH dibuat sama sekali — tapi kalau ada data lama dari sebelum fix ini, kemungkinan masih ada `nilai_akhir=0` yang sebenarnya "tidak diajarkan", bukan "dinilai nol beneran" |
| `bg-siakad-primary`/`from-siakad-primary` dkk itu class Tailwind valid | **salah** — cuma ada sebagai CSS custom property (`var(--siakad-primary)`), bukan warna resmi di `tailwind.config.js`. Kalau kepakai sebagai nama class, Tailwind diam-diam skip (tidak error), elemen jatuh ke background default sementara `text-white` di dalamnya tetap render → teks putih di atas putih. Sudah 2x kejadian (§9 poin 24), grep `bg-siakad-\|from-siakad-\|to-siakad-` untuk cari sisa yang belum ketemu                                    |
| `now()`/tanggal di sistem ini otomatis WIB                             | **salah sampai §9 poin 26 diterapkan** — `config('app.timezone')` sempat `UTC`. Kalau nemu jam/tanggal yang kelihatan geser beberapa jam dari yang seharusnya, cek dulu apakah `config:clear` sudah dijalankan setelah fix ini, bukan asumsi ada bug baru                                                                                                                                                                                                       |
| `ActivityLog->action` layak ditampilkan langsung ke UI                 | **salah** — untuk model 2 kata+ hasilnya satu kata nyambung tanpa spasi (`kategorimatapelajaran.created`), bukan cuma "teknis" tapi nyaris tidak kebaca. Selalu pakai `$log->deskripsi` (accessor baru, §9 poin 29), bukan `$log->action` mentah                                                                                                                                                                                                                |

---

## Modul per Role

| Role                 | Namespace Controller                                       | Prefix Route   |
| -------------------- | ---------------------------------------------------------- | -------------- |
| Staf Admin           | `App\Http\Controllers\Admin\*`                             | `admin.*`      |
| Wakil Kurikulum      | `App\Http\Controllers\Kurikulum\*`                         | `kurikulum.*`  |
| Tenaga Pendidik      | `App\Http\Controllers\Guru\*`                              | `guru.*`       |
| Bagian Kesantrian    | `App\Http\Controllers\Kesantrian\*`                        | `kesantrian.*` |
| Administrator Sistem | `App\Http\Controllers\Sysadmin\*`                          | `sysadmin.*`   |
| Mudir Pondok         | `App\Http\Controllers\Mudir\*` (dashboard saja, read-only) | `mudir.*`      |

Tiap modul view ada di `resources/views/<nama-modul>/` (flat, **bukan** nested per-role — mis. `resources/views/nilai/` dipakai baik oleh Guru maupun Kurikulum lewat view berbeda: `nilai/show.blade.php` untuk guru, `nilai/kurikulum-show.blade.php` untuk kurikulum).

---

## Konvensi Kode & Folder

- **Views flat per modul**, bukan per role: `resources/views/jadwal/`, `resources/views/santri/`, dst. Kalau butuh view khusus role, kasih prefix nama file: `kurikulum-index.blade.php`, `guru-index.blade.php`.
- **`@include('nama-folder._form', [...])`** — path Blade **relatif ke `resources/views/`**, TIDAK ada folder `kurikulum/` atau role lain di depannya. (Ini sumber bug berulang — lihat §9.)
- **Layout**: `<x-app-layout>` → merender `resources/views/layouts/app.blade.php` → include `layouts/partials/sidebar-nav.blade.php`. **`layouts/navigation.blade.php` adalah sisa scaffolding Laravel Breeze yang TIDAK PERNAH dipakai** — jangan edit file itu mengira itu navigasi aktif.
- **Styling**: pakai CSS variable custom (`text-siakad-dark`, `text-siakad-secondary`, `text-siakad-primary`, class `card-saas`) untuk elemen yang perlu adaptif dark/light mode. **Elemen `<input>/<select>/<textarea>` sudah ditangani otomatis** lewat aturan global `.dark input, .dark select, .dark textarea` di `layouts/app.blade.php` — tidak perlu tambah `dark:` prefix manual di tiap form baru.
- **⚠️ `tailwind.config.js` WAJIB punya `darkMode: 'class'`** — jangan pernah dihapus. Tanpa ini, Tailwind balik ke default `darkMode: 'media'` dan semua utility `dark:*` di seluruh view (dark:text-white, dark:bg-gray-800, dst) akan mengikuti preferensi OS/browser, bukan toggle dark/light manual di app (yang berbasis `localStorage` + class `.dark` di `<html>`) — menyebabkan teks tidak terbaca kalau OS & toggle app tidak sinkron (lihat §9).
- **Export Excel**: taruh class baru di `app/Exports/`, implementasikan `FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle` mengikuti pola `NilaiExport.php`/`PresensiExport.php` yang sudah ada — jangan bikin ulang dari nol.
- **PDF**: pakai `Barryvdh\DomPDF\Facade\Pdf::loadView(...)`, pola sudah ada di `RaporController` & `SuratController`.
- **Service layer**: logika bisnis kompleks (kalkulasi, rekap) taruh di `app/Services/`, controller cuma orkestrasi + validasi + response.
- **Relasi "lunak" (soft relation) tanpa foreign key**: kalau ada alasan kuat untuk TIDAK bikin foreign key sungguhan (mis. kolom itu dipakai service lain yang belum sepenuhnya dipahami, lihat kasus `KategoriMataPelajaran` di §9 poin 16), boleh pakai `hasMany`/`belongsTo` dengan custom local/foreign key yang menunjuk ke kolom non-ID (`$this->hasMany(Model::class, 'kolom_string', 'nama')`). Eloquent tidak mewajibkan constraint DB untuk relasi jalan. **Ini pengecualian, bukan pola default** — kalau tidak ada alasan spesifik, tetap pakai foreign key + `constrained()` seperti modul lain.
- **Soft-deactivate vs hard delete di `destroy()`**: pilih berdasarkan apakah entitas itu punya data akademik/historis yang bergantung padanya. Ada nilai/presensi/rapor/penugasan yang referensi ke entitas ini (mis. `MataPelajaran`) → pakai `update(['is_active' => false])`, JANGAN hard delete (lihat §9 poin 20 untuk alasan lengkap). Murni master data tanpa histori yang perlu dijaga (mis. `Tingkatan`, `KategoriMataPelajaran`) → boleh `->delete()` sungguhan, TAPI wajib guard: cek dulu relasi terkait `->exists()`, tolak hapus kalau masih dipakai (lihat §9 poin 10 & 16 untuk pola guard-nya).

---

## Aturan Main Sebelum Mengubah Kode

Checklist ini lahir dari bug-bug nyata yang sudah ditemukan di project ini — bukan teori:

1. **Sebelum pakai `Route::resource()`**, pastikan controllernya benar-benar punya ke-7 method standar. Kalau tidak, daftarkan route eksplisit satu-satu.
2. **Sebelum menambah `@include(...)` atau `return view(...)` baru**, cek dulu path-nya benar-benar ada filenya (`resources/views/<path-dengan-titik-jadi-slash>.blade.php`) — jangan asumsikan ada folder per-role.
3. **Sebelum mengasumsikan nama kolom/relasi**, cek migration aslinya dulu (`database/migrations/`) — proyek ini beberapa kali punya nama kolom yang "masuk akal tapi salah" (lihat tabel di §5).
4. **Sebelum mengubah alur Nilai/Presensi/Jurnal**, ingat semuanya bergantung ke `PenugasanMengajar`, bukan `JadwalPelajaran` — lihat §3.
5. **Sebelum mengubah kalkulasi nilai**, ingat aturan "Cara A" di §4 — per siswa independen, slot kosong dilewati bukan 0, bobot per-komponen bukan per-slot.
6. **Kalau membuat/mengedit user lewat cara baru**, sinkronkan `users.role` **dan** Spatie role sekaligus.
7. **Jalankan `php artisan view:clear`** setelah menambah/mengubah file Blade kalau ada cache lama (`storage/framework/views/`) — beberapa kali error "View not found" ternyata cuma cache basi, bukan file yang benar-benar hilang.
8. **Sebelum menyentuh apapun yang berhubungan dengan KKM**, `grep -rn "\->kkm\b" app/ resources/views/` dulu — kalau ketemu yang baca `$mataPelajaran->kkm` langsung (bukan lewat `kkmUntukTingkatan()`), itu kemungkinan besar bug yang sama seperti §9 poin 17, bukan kode baru yang sengaja begitu.

---

## Riwayat & Catatan Perbaikan

Bug signifikan yang **sudah ditemukan & diperbaiki**. Kalau ketemu kode yang "terasa aneh" atau beda dari SRS, cek dulu di sini sebelum "membenarkan" sesuatu yang sebenarnya sudah sengaja begitu.

1. **Routing salah/ghost route**: `JadwalController` (`guru_id` divalidasi ke `tenaga_pendidik`, harusnya `users.id`), `SantriController::export()` (view tidak ada), PPDB & Kamar (`Route::resource()` dipakai padahal controller tidak punya ke-7 method standar), `kesantrian.rekap.*` (nunjuk view yang tidak ada) — semua sudah diperbaiki jadi route eksplisit / method & view yang benar.
2. **View path salah folder**: beberapa `@include('kurikulum.jadwal._form')` nunjuk folder yang tidak ada (views itu flat, bukan per-role, lihat §7) — sudah diperbaiki.
3. **9 halaman yang belum pernah dibuat** (`asrama/create,edit`, `pelanggaran/show`, `pendidik/show`, `santri/profil`, `template-surat/show,edit`, `surat/edit`, `users/create,edit`) — sudah dibuat semua.
4. **Sintaks Blade `{{ }}` rusak di `<script>`** — sudah 2x kejadian (`nilai/show.blade.php`, `presensi-kbm/index.blade.php`), ke-mangle jadi `{ { $x - > y } }` (kemungkinan auto-formatter) sampai bikin seluruh script block gagal parse di browser. **Hati-hati kalau ada tool auto-format jalan di file `.blade.php` yang isinya `{{ }}` di dalam `<script>`/`style=""`.**
5. **Dark mode — 3 lapis masalah, semua sudah diperbaiki**: (1) `tailwind.config.js` kurang `darkMode: 'class'` — root cause, lihat §7; (2) puluhan file pakai `bg-gray-50` tanpa `dark:` di elemen form — dicover 1 aturan CSS global; (3) beberapa elemen non-form (bubble chat AI, badge, dst) punya bg statis yang bentrok sama teks token `text-siakad-dark` — ditambal satu-satu.
6. **Sistem Nilai**: awalnya cuma 1 nilai/komponen/semester, diperluas jadi multi-input pakai kolom `slot` (lihat §4).
7. **Jadwal → Penugasan**: `JadwalPelajaran` diganti `PenugasanMengajar` sebagai gerbang akses (lihat §3).
8. **`SantriController` (create/edit/store/update)**: beberapa bug — `Undefined variable $kelasList`, salah baca `kelasAktif()->kelas_id` (harusnya `->id`, PK model `Kelas`), dan penempatan kelas yang divalidasi tapi tidak pernah tersimpan ke `santri_kelas` — semua sudah diperbaiki.
9. **`santri.user_id`**: sempat NOT NULL padahal santri tidak pernah login (tidak ada role `santri`) — sudah dibikin nullable.
10. **Tingkatan**: dulu cuma bisa diubah lewat seeder, sekarang ada CRUD lengkap (`Admin\TingkatanController`), dengan guard supaya tidak bisa hapus tingkatan yang masih ada kelasnya (`cascadeOnDelete` ke `kelas`).
11. **Modul Wali Kelas** (Predikat Sikap + Nilai Ekstrakurikuler): sempat ada celah otorisasi — `santri_id` di body request tidak dicek benar milik kelas wali kelas yang login — sudah ditambal cross-check.
12. **Rapor untuk Wali Kelas**: view rapor sempat hardcode route `kurikulum.rapor.*`, bikin wali kelas kena 403 saat lihat/cetak — sudah dibuat dinamis (`$routePrefix` berdasar `request()->routeIs(...)`).
13. **Presensi/Jurnal KBM**: sempat "siloed" per guru (beda dari Nilai yang shared per kelas+mapel) — sudah disamakan; guru pengganti sekarang dapat akses penuh (lihat+edit+hapus) ke riwayat guru sebelumnya (**keputusan disengaja**, lihat §3).
14. **Import Kelas & Asrama Massal**: 2 bug (baca key Excel salah format — harus slug seperti `nis` bukan `'NIS'`; kondisi update asrama salah cek field) + fitur belum ke-wire sama sekali (route/view/tombol belum ada, payload preview terlalu berat di session) — semua sudah dibuat & diperbaiki.
15. **Rapor Arab (PDF) — migrasi Dompdf → mPDF + perbaikan format tampilan**:
    - Awalnya menggunakan `barryvdh/laravel-dompdf` untuk cetak rapor Arab (format KMI 2 halaman). Hasilnya huruf Arab tidak tersambung (terpotong-potong) karena Dompdf tidak support Arabic shaping secara native.
    - Diubah ke `mpdf/mpdf` dengan konfigurasi:
        - `$mpdf->autoArabic = true` — mengaktifkan Arabic shaping, huruf Arab menjadi tersambung.
        - `$mpdf->SetDirectionality('rtl')` — mengatur arah teks menjadi kanan-ke-kiri.
    - Judul rapor diubah dari `كشف الدرجة` menjadi `تقرير نتائج التعلم` (sesuai format "Laporan Hasil Belajar" yang diinginkan user).
    - Layout tabel nilai disederhanakan dari 8 kolom menjadi 4 kolom: `الرقم`, `المادة الدراسية`, `الدرجة النهائية`, `وصف الإنجاز`.
    - Header/footer menggunakan mekanisme native mPDF (`<htmlpageheader>`/`<htmlpagefooter>`) agar otomatis berulang di semua halaman, dengan nomor halaman `{PAGENO}/{nbpg}`.
    - Fungsi helper `toArabicDigits()` ditambahkan di view untuk konversi angka Latin → Arab (0→٠, 1→١, 2→٢, dst).
    - Fungsi `masehiKeHijriahArab()` ditambahkan sebagai fallback untuk generate tanggal Hijriah otomatis jika database kosong (tidak bergantung ekstensi `calendar` PHP).
    - Fungsi `formatMasehiArab()` untuk menampilkan tanggal Masehi dengan nama bulan dalam bahasa Arab (tanpa bergantung `translatedFormat()` yang kadang tidak konsisten).
    - Tanda tangan diubah menjadi 4 kolom dengan urutan kanan-ke-kiri: `مدير المعهد`, `رئيس المدرسة`, `ولي الفصل`, `ولي الطالب`.
    - Margin diatur di `@page` (`margin-top: 34mm`, `margin-bottom: 20mm`, `margin-header: 8mm`, `margin-footer: 8mm`) untuk memberi ruang header/footer.
    - File view: `resources/views/rapor/cetak-arab-pdf.blade.php`.
    - File service: `app/Services/RaporArabService.php` (ditambah data pendukung: `sekolah_nama`, `fase`, `tempat`, `tanggal_masehi`, `tanggal_hijriah`, `mudir`, `wali_kelas`, `kepala_sekolah`).
    - Controller: `app/Http/Controllers/Kurikulum/RaporController.php` method `cetakArab()`.
    - **Status**: ✅ Selesai — huruf Arab tersambung, layout sesuai format "Laporan Hasil Belajar", header/footer berulang otomatis, angka Arab otomatis.
16. **Kategori Mata Pelajaran — dari hardcoded jadi CRUD (Admin)**:
    - Sebelumnya, 4 pilihan kategori mapel (`القرآن و علومه`, `العقيدة و الأخلاق`, `الشّريعة`, `اللغة العربية`) di-hardcode sebagai array literal langsung di `resources/views/mata-pelajaran/_form.blade.php`. Tidak bisa ditambah/diubah tanpa edit kode.
    - Dibuat tabel master baru `kategori_mata_pelajaran` (`nama` unique, `urutan` nullable) + CRUD lengkap: `App\Http\Controllers\Admin\KategoriMataPelajaranController`, model `App\Models\KategoriMataPelajaran`, views di `resources/views/kategori-mata-pelajaran/`, route `admin.kategori-mata-pelajaran.*`. Migration seed 4 kategori lama supaya data existing tetap valid setelah deploy.
    - **Keputusan desain penting — dibaca dulu sebelum "membetulkan" jadi foreign key sungguhan**: kolom `mata_pelajaran.kategori` **SENGAJA TIDAK** diubah jadi `kategori_mata_pelajaran_id`. Alasannya: `RaporArabService` (lihat poin 15 di atas) kemungkinan besar query berdasarkan kolom `kategori` untuk pengelompokan cetak rapor, dan saat fitur ini dibuat tidak ada visibilitas penuh ke isi service tersebut untuk memastikan migrasi FK aman. Jadi `mata_pelajaran.kategori` **tetap kolom string**, dan `KategoriMataPelajaran::mataPelajaran()` adalah relasi Eloquent "lunak" (`hasMany` dengan custom key `kategori`/`nama`, bukan foreign key di database) — Eloquent tetap bisa `withCount`, `exists()`, dst tanpa constraint DB.
    - Konsekuensi dari keputusan ini, **wajib dijaga** kalau ada yang menyentuh kode ini lagi:
        - `KategoriMataPelajaranController::update()` — kalau `nama` kategori diubah, WAJIB cascade-update semua `mata_pelajaran.kategori` yang match nama lama → nama baru (sudah diimplementasikan, dibungkus `DB::transaction`). Kalau ini dihapus/lupa, mapel-mapel lama jadi "yatim" (string kategorinya tidak cocok pilihan manapun).
        - `KategoriMataPelajaranController::destroy()` — WAJIB cek `mataPelajaran()->exists()` dulu sebelum hapus (pola sama seperti `TingkatanController::destroy()` mencegah hapus tingkatan yang masih ada kelasnya).
        - `MataPelajaranController::store()`/`update()` — validasi `kategori` pakai `Rule::exists('kategori_mata_pelajaran', 'nama')` (bukan `exists('kategori_mata_pelajaran', 'id')`), karena yang disimpan memang string nama, bukan ID.
    - **Kalau suatu saat mau benar-benar migrasi ke foreign key** (`kategori_mata_pelajaran_id`): baca dulu `RaporArabService.php` dan `resources/views/rapor/cetak-arab-pdf.blade.php` secara menyeluruh untuk pastikan semua query kategori ikut di-update, baru lakukan migrasi data (backfill ID dari string, baru drop kolom string lama) — jangan lakukan sebagai "quick refactor" tanpa cek itu dulu.
17. **KKM: dari kolom global `mata_pelajaran.kkm` jadi per-tingkatan (`kkm_tingkatan`)**:
    - Sejak migration `kkm_tingkatan` dibuat (lihat §5 & poin terkait di riwayat ini), sempat ada **2 sumber KKM berjalan paralel**: kolom lama `mata_pelajaran.kkm` (dipakai UI form/index/show mapel & semua kalkulasi nilai) dan tabel baru `kkm_tingkatan` (dipakai di menu `admin.kkm`, tapi **tidak dibaca sistem manapun**). KKM ternyata beda per tingkatan untuk mapel yang sama (mis. Aqidah Akhlak KKM 70 di kelas 7-9, 75 di kelas 10-12).
    - `MataPelajaran::kkmUntukTingkatan(?int $tingkatanId): ?int` adalah **satu-satunya cara yang benar** untuk baca KKM sekarang — prioritas cek `kkm_tingkatan` dulu, fallback ke kolom `kkm` lama kalau kombinasi mapel+tingkatan itu belum diisi. **Jangan pernah baca `$mataPelajaran->kkm` langsung** kalau konteksnya ada tingkatan/kelas yang jelas — itu tandanya kode lama yang belum di-migrasi.
    - **UI dibersihkan** (tidak lagi ada field/kolom KKM langsung di mapel): `mata-pelajaran/_form.blade.php` (field + grid 2 kolom dihapus), `mata-pelajaran/index.blade.php` (kolom tabel + `colspan` disesuaikan), `mata-pelajaran/show.blade.php` (baris detail dihapus). Validasi `kkm` dihapus dari `MataPelajaranController::store()`/`update()`. `kkm` dihapus dari `MataPelajaran::$fillable`.
    - **Kolom `kkm` & `$casts` di tabel/model `MataPelajaran` SENGAJA TIDAK dihapus** — tetap dipakai sebagai fallback di `kkmUntukTingkatan()`. Jangan drop kolom ini tanpa mastikan semua kombinasi mapel+tingkatan sudah terisi penuh di `kkm_tingkatan`.
    - **Titik integrasi yang sudah diperbaiki** (cari `->kkm` langsung sebelum nambah kode baru — kalau ketemu, kemungkinan besar itu bug yang sama):
        - `PenilaianService::hitungNilaiAkhir()` — status `tuntas` sekarang pakai `$mataPelajaran->kkmUntukTingkatan($kelas->tingkatan_id)`.
        - `PenilaianService::getRekapNilaiKelas()` — angka KKM di rekap spreadsheet kelas ikut per-tingkatan.
        - `Guru\NilaiController::show()` — hitung `$kkm` sekali di controller (bukan di blade), dikirim ke `nilai/show.blade.php` (dipakai di teks header & variabel JS `const KKM`).
        - `Kurikulum\RaporController::show()` & `::cetak()` — looping `$nilaiAkhir` dan tempel atribut sementara `$na->kkm_tingkatan` per baris (pola sama seperti `RaporArabService::rakit()`), dipakai di `rapor/show.blade.php` & `rapor/cetak-pdf.blade.php`.
        - `RaporArabService::rakit()` — **sudah benar dari awal**, sudah pakai `kkmUntukTingkatan()` sebelum migrasi ini dikerjakan; tidak perlu diubah.
        - `cetak-arab-pdf.blade.php` — **tidak menampilkan KKM sama sekali** (formatnya cuma 4 kolom, lihat poin 15), jadi tidak relevan/tidak disentuh.
    - **File yang SUDAH dicek dan TIDAK butuh perubahan** (dikonfirmasi lewat grep `->kkm`, bukan asumsi): `WaliKelas\DashboardController.php`, `wali-kelas/dashboard.blade.php`, `Guru\NilaiController.php` (method-method selain `show()`) — semuanya bergantung ke `NilaiAkhir.tuntas` yang sudah dihitung `PenilaianService`, bukan baca `kkm` langsung.
18. **Bug `{{ }}` mangled di `<script>` — kejadian ke-3** (lihat poin 4 di riwayat ini untuk 2 kejadian sebelumnya): ditemukan lagi di `resources/views/nilai/show.blade.php` saat mengerjakan poin 17 di atas — `{{ $mataPelajaran->kkm }}` dan loop `bobotMap` ke-mangle jadi `{ { $mataPelajaran - > kkm } }` (spasi disisipkan di sekitar `{{`, `}}`, dan `->`), bikin seluruh `<script>` block gagal parse di browser (kalkulasi nilai akhir live `hitungAkhir()` mati total). Sudah diperbaiki sekaligus dengan migrasi ke `kkmUntukTingkatan()` di poin 17. **Kalau nemu pola serupa di file lain**, sangat mungkin ini bukan kebetulan — kemungkinan ada tool/editor tertentu di alur kerja yang menyisipkan spasi di sekitar `{{`/`}}`/`->` saat menyimpan file `.blade.php`. Selalu cek dengan `grep -n "{ *{" resources/views/**/*.blade.php` setelah save kalau curiga.
19. **Sticky header ganda bertabrakan di `rapor/show.blade.php`**: div sticky khusus halaman rapor (ringkasan nilai + tombol cetak) pakai `sticky top-0 z-20` — persis sama dengan topbar utama di `layouts/app.blade.php` (`header` dengan `h-16 sticky top-0 z-20`). Karena keduanya bukan nested (sibling, bukan parent-child), dua elemen sticky di offset `top-0` yang sama akan **rebutan posisi** dan saling tutup pas di-scroll, bukan menumpuk rapi. Fix: div sticky di rapor diubah jadi `top-16 z-10` — `top-16` (64px) menyamai tinggi topbar (`h-16`) supaya nempel PAS DI BAWAHNYA, `z-10` (lebih rendah dari topbar `z-20`) supaya topbar tetap menang kalau ada overlap sesaat. **Pola ini berlaku umum**: kalau bikin sticky element baru di halaman manapun yang dirender di dalam `<x-app-layout>`, selalu offset `top`-nya minimal `top-16`, jangan `top-0` — karena `top-0` sudah "dipakai" topbar utama.
20. **`MataPelajaranController::destroy()` sengaja soft-deactivate, BUKAN hard delete** — dan ini pola yang disengaja, bukan celah yang belum sempat diperbaiki:

    ```php
    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update(['is_active' => false]);
        ActivityLogService::logDelete($mataPelajaran);
        return back()->with('success', 'Mata pelajaran dinonaktifkan.');
    }
    ```

    - Beda dengan `TingkatanController::destroy()` dan `KategoriMataPelajaranController::destroy()` yang memang `->delete()` sungguhan (dan karena itu butuh guard cek relasi dulu, lihat §9 poin 10 & 16) — mapel **tidak pernah** di-hard-delete lewat controller ini, jadi tidak butuh guard serupa.
    - **Alasan**: `Nilai`, `NilaiAkhir`, `PenugasanMengajar`, `KkmTingkatan`, `Pertemuan` semuanya referensi `mata_pelajaran_id`. Data akademik historis (rapor tahun-tahun lalu) harus tetap bisa dibuka/dicetak ulang kapan saja — hard delete berisiko bikin data itu orphaned atau ikut ke-cascade-delete. `is_active = false` juga gampang di-toggle balik kalau admin salah klik; hard delete tidak bisa di-undo tanpa restore backup.
    - **Konsekuensi yang perlu disadari** (bukan bug, tapi trade-off desain): tabel `mata_pelajaran` tidak pernah benar-benar mengecil — mapel yang "dihapus" tetap ada selamanya, cuma disembunyikan lewat scope `aktif()`. Untuk jumlah mapel yang wajar di satu pondok, ini tidak masalah.
    - **Pola umum untuk entitas baru ke depannya**: kalau entitas punya data akademik/historis yang bergantung padanya (nilai, presensi, rapor, dst) → soft-deactivate (`is_active`), JANGAN hard delete. Kalau entitas murni master data tanpa histori yang perlu dijaga (Tingkatan, Kategori Mapel, dst) → boleh hard delete, TAPI wajib guard cek relasi dulu (pola `->exists()` sebelum `->delete()`).
    - **Belum ada fitur hard-delete permanen** untuk mapel yang benar-benar salah input dan belum pernah dipakai sama sekali (nol relasi). Kalau suatu saat dibutuhkan, itu fitur terpisah yang wajib pakai guard serupa Tingkatan/Kategori — bukan modifikasi `destroy()` yang sudah ada.

21. **Kolom `mata_pelajaran.tingkat` dideprecate — dihapus dari UI & validasi, kolom DB dibiarkan**:
    - Sebelumnya form create/edit mapel punya field `tingkat` (single-select, wajib, nilai `'7'`–`'12'`), ditampilkan juga di index & show. **Dicek satu-satu ke semua tempat yang masuk akal memakainya**, dan ternyata **tidak dikonsultasikan sama sekali** oleh sistem manapun:
        - `KkmController::index()` — matrix KKM nampilin SEMUA mapel aktif × SEMUA tingkatan, tidak difilter oleh `mata_pelajaran.tingkat` sama sekali.
        - `Kurikulum\PenugasanController::show()` — `$mapelList = MataPelajaran::orderBy('nama')->get();` dan `$kelasList = Kelas::...->get();` dua query independen, tidak saling filter oleh `tingkat` ataupun `tingkatan_id`. Wakil Kurikulum pilih mapel & kelas manapun secara manual, sistem tidak membatasi berdasarkan `tingkat`.
        - `Guru\NilaiController`, `RaporController`, `RaporArabService` — semua pakai `Kelas->tingkatan_id` (entitas `Tingkatan`, beda sama sekali dari kolom string `tingkat` di `mata_pelajaran`), tidak pernah baca `mata_pelajaran.tingkat`.
    - **Temuan tambahan yang memperkuat keputusan ini**: `mata_pelajaran.tingkat` **tidak pernah ada** di `$fillable` model `MataPelajaran` (dicek dari versi paling awal yang ada), padahal `MataPelajaranController::store()`/`update()` selalu ikut kirim `tingkat` lewat `array_merge($validated, [...])`. Karena Laravel secara default **diam-diam membuang** (silently discard) atribut non-fillable saat mass-assignment (bukan throw exception, kecuali `Model::preventSilentlyDiscardingAttributes()` diaktifkan eksplisit di `AppServiceProvider` — dan itu tidak dilakukan di project ini), kemungkinan besar **perubahan `tingkat` lewat form edit sudah lama tidak benar-benar tersimpan ke database**, dari sebelum sesi ini pun.
    - **Kesimpulan**: field ini kemungkinan peninggalan desain awal (migration `penugasan_mengajar` dibuat 2025-02, jauh sebelum sistem `kkm_tingkatan` per-tingkatan ada di 2026-07) — sebelum sistem pivot ke model "1 mapel bisa lintas banyak tingkatan dengan KKM beda-beda", mungkin desain awalnya "1 mapel = 1 tingkat spesifik". Field-nya tidak pernah dibersihkan waktu arsitektur berubah.
    - **Yang sudah dilakukan**: field dihapus dari `mata-pelajaran/_form.blade.php`, kolom dihapus dari `index.blade.php` & `show.blade.php`, validasi `'tingkat' => [...]` dihapus dari `MataPelajaranController::store()`/`update()`. **Kolom `tingkat` di database SENGAJA TIDAK di-drop** — mengikuti pola yang sama seperti kolom `kkm` di poin 17, biar reversible kalau ternyata masih relevan buat sesuatu yang belum ketahuan.
    - **Kalau suatu saat butuh konsep "mapel ini cuma relevan di tingkat tertentu"** (mis. buat filter otomatis di Penugasan Mengajar), jangan hidupkan lagi kolom string `tingkat` yang lama — bikin ulang sebagai relasi many-to-many yang benar (tabel pivot, mirip pola `kkm_tingkatan`), karena satu mapel pada dasarnya memang bisa relevan di lebih dari satu tingkat sekaligus.
22. **Finalisasi nilai akhir WAJIB dibatasi ke mapel yang benar ditugaskan (`PenugasanMengajar`) — jangan pernah hitung untuk kombinasi kelas+mapel yang tidak ada penugasannya**:
    - **Masalah yang dicegah**: `PenilaianService::hitungNilaiAkhirBulk()` loop ke SEMUA santri fisik di sebuah kelas, lalu untuk tiap santri panggil `hitungNilaiAkhir()` yang formula-nya `$nilaiAkhirHitung = 0` di awal dan cuma nambah kalau ada baris `Nilai` yang cocok. Kalau mapel itu memang TIDAK diajarkan di kelas itu (santri tidak pernah punya baris `Nilai` untuk kombinasi itu), hasilnya tetap kesimpan sebagai `NilaiAkhir` dengan `nilai_akhir = 0.00` — **secara data tidak bisa dibedakan** dari santri yang beneran dinilai dan hasilnya nol. Ini juga berlaku untuk SEMUA komponen (UH/Tugas/Praktik/UTS/UAS) karena validasi `nilai.*.*.*` di `Guru\NilaiController::bulkStore()` semuanya `nullable` — tidak ada satupun komponen yang wajib diisi, formula sengaja skip komponen kosong (bukan dianggap 0), jadi "0 karena tidak pernah dinilai" dan "0 karena benar-benar dinilai nol" numeriknya identik.
    - **Sumber kebenaran "mapel apa diajarkan di kelas mana" adalah `PenugasanMengajar`** (guru ditugaskan mengajar mapel X di kelas Y untuk tahun ajaran tertentu) — BUKAN ada-tidaknya baris di `kkm_tingkatan`. Sempat dipertimbangkan pakai ada-tidaknya KKM sebagai penanda "mapel tidak diajarkan", tapi itu bertentangan langsung dengan desain `kkmUntukTingkatan()` di poin 17 (yang sengaja fallback ke kolom `kkm` lama kalau `kkm_tingkatan` belum diisi admin — beda makna sama sekali dari "mapel tidak diajarkan"), dan admin bisa saja lupa isi KKM untuk mapel yang sebenarnya memang diajarkan (typo/kelupaan), yang salah menyembunyikan nilai santri yang sah.
    - **Status pengecekan di `Kurikulum\NilaiController` (per method yang manggil `hitungNilaiAkhirBulk()`)**:
        - `finalizeKelas()` — ✅ sudah aman dari awal, mapel-nya sudah difilter dari `PenugasanMengajar` sebelum loop.
        - `finalizeAll()` — ✅ sudah aman dari awal, sama.
        - `finalize()` (finalisasi 1 kelas + 1 mapel spesifik, dipanggil dari `kurikulum-show.blade.php`) — ❌ **ini yang bolong**, cuma validasi `exists:mata_pelajaran,id` (mapel-nya ada di database), TIDAK cek apakah mapel itu benar ditugaskan ke kelas tersebut. Sudah diperbaiki: ditambah guard `PenugasanMengajar::where('kelas_id',...)->where('mata_pelajaran_id',...)->where('tahun_ajaran_id',...)->exists()` sebelum manggil `hitungNilaiAkhirBulk()`, `return back()->with('error', ...)` kalau tidak ketemu — **kalkulasi dibatalkan total**, tidak ada baris `NilaiAkhir` yang kebuat sama sekali (bukan `NilaiAkhir` dengan nilai 0).
    - **Efek turunan yang perlu disadari**: karena rapor, leger nilai, dan rangking (`getRankingKelas()`) semuanya query dari tabel `NilaiAkhir`, begitu kombinasi kelas+mapel yang tidak ditugaskan konsisten TIDAK PERNAH punya baris `NilaiAkhir`, otomatis mapel itu juga tidak akan muncul di rapor/leger santri manapun di kelas itu — tanpa perlu filter tambahan di sisi rapor/leger, karena sumbernya (`NilaiAkhir`) memang sudah bersih dari awal.
    - **Kalau nambah cara baru buat trigger `hitungNilaiAkhirBulk()`/`hitungNilaiAkhir()` di masa depan** (mis. tombol finalisasi baru, auto-finalize terjadwal, dsb): WAJIB pasang guard `PenugasanMengajar` yang sama sebelum manggil, jangan asumsikan pemanggilnya "pasti aman" karena UI-nya sudah scoped dengan benar — `finalize()` di atas contoh nyata kenapa validasi cuma di level UI/dropdown itu tidak cukup, backend tetap harus jaga sendiri.
23. **Jurnal Mengajar — dari 2 file rusak jadi fitur lengkap dengan halaman detail sendiri**:
    - **Kondisi awal, 2 bug independen yang kebetulan nutupin satu sama lain**: (a) `resources/views/presensi-kbm/jurnal.blade.php` **tidak ada sama sekali** di disk padahal `JurnalController::index()` sudah lama `return view('presensi-kbm.jurnal', ...)` — jadi menu "Jurnal Mengajar" selalu error "View not found" dari awal; (b) `resources/views/presensi-kbm/edit.blade.php` **isinya bukan form edit** — ke-isi salah dengan copy dari `index.blade.php` (butuh `$penugasanList`/`$riwayat`, bukan `$pertemuan`/`$santriList` yang sebenarnya dikirim `PresensiController::edit()`). Karena jurnal-nya sendiri error duluan, satu-satunya jalan guru "lihat" riwayat pertemuan adalah lewat bagian Riwayat di halaman Presensi (yang linknya emang ke `guru.presensi.show`) — inilah yang awalnya kelihatan seperti "tombol lihat jurnal salah arah ke presensi", padahal akar masalahnya jurnal-nya sendiri tidak pernah kebuka.
    - Kedua file itu sudah dibuat/dibenerin. `jurnal.blade.php` juga sempat kena **kejadian ke-4 bug mangled syntax** (lihat poin 4 & 18) — kali ini bentuknya beda dari sebelumnya: bukan `{ { $x - > y } }`, tapi potongan `dark:text-{{ ` hilang dari tengah class, nyisain `text-{{ $color }}-600 $color }}-400` (harusnya `text-{{ $color }}-600 dark:text-{{ $color }}-400`). Juga ada **bug `colspan` klasik** (pola sama seperti leger-nilai) — header 8 kolom tapi baris kosong pakai `colspan="9"`.
    - **Halaman detail jurnal sekarang terpisah dari detail presensi**, by design, atas permintaan eksplisit: `presensi-kbm/jurnal-show.blade.php` (route `guru.jurnal.show`) fokus ke topik/materi/catatan mengajar + ringkasan kehadiran (cuma angka H/S/I/A), dan **detail nama santri cuma ditampilkan untuk yang TIDAK hadir** (sakit/izin/alpa) — santri yang hadir tidak disebut satu-satu karena dianggap "kondisi normal", sudah terwakili di angka ringkasan. Beda dari `presensi-kbm/show.blade.php` (route `guru.presensi.show`) yang nampilin tabel lengkap SEMUA santri termasuk yang hadir — 2 halaman ini SENGAJA dibedakan tujuannya, bukan duplikat. Ada link silang di `jurnal-show.blade.php` ke `presensi.show` buat yang butuh lihat semua santri.
    - **Guard akses `guruBolehAkses()` di `JurnalController` SENGAJA duplikat** dari method private yang sama persis di `PresensiController` (bukan di-share lewat trait/helper) — konsisten dengan pola project ini yang memang belum punya shared-helper untuk guard semacam ini di modul manapun (Rapor, Leger, Presensi semua punya method authorize-nya sendiri-sendiri). Kalau suatu saat mau dirapikan jadi 1 trait, jangan cuma rapikan yang ini — rapikan sekalian 3 tempat lain yang polanya identik.
    - **Pola dropdown `<select>` tanpa `width` eksplisit jadi kelihatan kekecilan** — sudah 2x kejadian di sesi yang sama (predikat sikap: dropdown A/B/C, dan filter bulan/tahun di jurnal). `<select>` native browser otomatis nyempit ngikutin opsi yang lagi kepilih (bukan opsi terpanjang), dan butuh ruang ekstra buat ikon panah bawaan browser. **Aturan umum ke depan**: setiap bikin `<select>` baru, SELALU kasih `w-*` eksplisit (bukan andalkan auto-sizing), lebar disesuaikan konten terpanjang yang mungkin muncul (mis. `w-36` cukup buat nama bulan Indonesia terpanjang "September"/"Desember", `w-24` buat 4 digit tahun, `w-20` buat kode 1 huruf kayak A/B/C).
24. **Bug `bg-gradient-to-r from-siakad-primary to-siakad-dark` — sudah 2x kejadian, kemungkinan lebih banyak lagi belum ketemu** (lihat juga catatan di `santri/show.blade.php` sebelumnya): beberapa halaman pakai nama warna brand (`siakad-primary`, `siakad-dark`, dst) sebagai **nama class Tailwind** (`from-siakad-primary`, `bg-siakad-primary`, dsb), padahal warna-warna itu di project ini **cuma ada sebagai CSS custom property** (`var(--siakad-primary)` dari `:root` di `app.blade.php`), BUKAN didaftarkan sebagai warna resmi di `tailwind.config.js`. Akibatnya Tailwind diam-diam mengabaikan class itu (tidak generate CSS, tidak error/warning apapun) — elemen jatuh balik ke background default (biasanya putih), sementara teks `text-white` di dalamnya tetap render normal (itu class Tailwind standar, bukan warna kustom) → **teks putih di atas background putih, tidak kebaca sama sekali**. Sudah ketemu & diperbaiki di `santri/show.blade.php` (header card avatar) dan `nilai/guru-index.blade.php` (banner Tahun Ajaran Aktif) — fix-nya selalu sama: ganti ke inline `style="background: linear-gradient(to right, var(--siakad-primary), var(--siakad-dark));"`.
    - **Cara cari sisa halaman yang mungkin masih kena bug ini**: `grep -rln "from-siakad-\|to-siakad-\|bg-siakad-" resources/views/` — kalau ada hasil, hampir pasti kena bug yang sama (beda dari `text-siakad-dark`/`text-siakad-secondary` yang memang valid dipakai luas sebagai class biasa di banyak file, itu BUKAN bug, jangan disamakan).
    - **Aturan umum ke depan**: warna brand (`siakad-primary`, `siakad-dark`, `siakad-secondary`, `siakad-light`) SELALU lewat `style="...: var(--nama-variabel);"`, JANGAN PERNAH sebagai nama class Tailwind (`bg-siakad-*`, `text-siakad-primary` sebagai class -- kecuali yang sudah lama dipakai luas dan terbukti jalan, cross-check dulu sebelum asumsi), `from-siakad-*`, `to-siakad-*`, dsb).
25. **AI Advisor — dari kebocoran data lintas kelas jadi ter-scope dengan benar, plus context diperluas**:
    - **Bug keamanan nyata (bukan cuma UX)**: dropdown "Analisis santri spesifik" di `ai/index.blade.php` sebelumnya `\App\Models\Santri::aktif()->orderBy('nama_lengkap')->get()` — SEMUA santri aktif di seluruh pondok, tidak difilter berdasarkan siapa yang login. `AiAdvisorController::chat()` juga TIDAK ADA guard sama sekali (`santri_id` cuma divalidasi `exists:santri,id`, bukan "milik guru ini"). Kombinasi keduanya: guru mana pun bisa lihat & minta AI analisis santri MANAPUN di pondok (nilai, KKM, poin pelanggaran aktif), bukan cuma yang dia ajar — beda dari pola guard yang sudah konsisten dipakai di Rapor/Leger/Presensi/Nilai (`PenugasanMengajar` check).
    - **Sudah diperbaiki**: `AiAdvisorController::scopeSantriUntukUser()` (dropdown, guru cuma lihat santri di kelas yang benar dia ajar via `PenugasanMengajar`) + `boleAksesSantri()` (guard backend independen dari UI, dipanggil di `chat()` sebelum data santri masuk context AI). Role `wakil_kurikulum`/`kesantrian`/`admin`/`sysadmin` tetap full akses (`ROLE_AKSES_PENUH` const) -- itu memang cakupan kerja mereka.
    - **KKM juga kena bug yang sama seperti modul lain** (`$n->mataPelajaran->kkm` langsung, bukan `kkmUntukTingkatan()`) -- `AiAdvisorService` kelihatannya dibuat/terakhir diubah SEBELUM migrasi KKM per-tingkatan (§9 poin 17), belum ikut ter-update. Sudah diperbaiki.
    - **Route `ai.chat`/`ai.index` cuma ada di grup `guru` & `kurikulum`** — padahal view sudah lama nyiapin kondisi buat role `kesantrian`/`admin`/`sysadmin` juga (`in_array($role, [...])` buat nampilin dropdown), route-nya belum pernah dibuatkan. Sudah ditambahkan ke 3 grup itu. JS `CHAT_ROUTE` yang tadinya ternary rapuh (`role==='guru' ? guru.ai.chat : kurikulum.ai.chat`, salah buat 3 role lain) diganti jadi dihitung SEKALI di controller lewat peta `ROLE_ROUTE_PREFIX`, dikirim ke view sebagai `$chatRoute` -- kalau nambah role baru yang boleh akses AI Advisor, WAJIB update peta ini DAN bikin grup route baru, 2 tempat itu harus sinkron.
    - **Gap fungsional, bukan cuma keamanan**: sebelum diperbaiki, `buildContext()` TANPA `santri_id` cuma ngasih AI 1 angka (`pertemuan_bulan_ini`) -- padahal tombol "Pertanyaan Cepat" di UI nawarin pertanyaan agregat kayak "Siapa santri dengan nilai terendah?", "Tren kehadiran kelas bulan ini?" yang secara struktural TIDAK MUNGKIN dijawab AI dari data sebegitu sedikit. Ditambahkan `AiAdvisorService::buildKelasSummary()` -- agregat per kelas (rata-rata, 3 nilai tertinggi/terendah, kehadiran bulan ini, jumlah pelanggaran aktif), dipanggil cuma kalau TIDAK ada `santri_id` dipilih. SENGAJA agregat, bukan dump semua santri mentah (buat role akses-penuh yang bisa punya puluhan kelas, itu bisa membengkakkan context jauh melebihi token wajar) -- dibatasi maks 30 kelas sebagai safety cap.
    - **Bug tampilan yang juga diperbaiki**: balon chat user (`addMessage()` di JS) nempel di ujung kiri wrapper `flex-1`-nya sendiri, jauh dari avatar di kanan -- `flex-row-reverse` cuma balik urutan avatar/konten, TIDAK ngatur perataan ISI di dalam wrapper konten. Fix: tambah `text-right` ke wrapper itu khusus pesan user.
    - **Fitur baru**: kartu "Riwayat Terakhir" sebelumnya cuma teks statis (tidak bisa diklik apapun, `chatHistory` selalu mulai kosong tiap buka halaman). Ditambahkan `loadRiwayat()` -- klik kartu riwayat memuat ulang 1 pasang pertanyaan+jawaban itu ke jendela chat + `chatHistory`, dengan konfirmasi dulu kalau ada chat aktif yang belum "disimpan". **Batasan yang perlu disadari**: ini cuma resume 1 PASANG Q&A, bukan seluruh sesi percakapan -- `AiConversationLog` nyimpen per pasang tanya-jawab individual, kolom `session_id`-nya ID sesi PHP (bukan pengelompokan percakapan yang bersih), jadi tidak ada cara ambil "semua obrolan dalam 1 sesi" tanpa desain ulang skema (kolom/konsep "percakapan" eksplisit).
    - **Layout viewport-fit khusus desktop (`lg:` ke atas saja, mobile SENGAJA belum disentuh/dibahas)**: wrapper terluar `lg:h-[calc(100vh-8rem)] lg:flex lg:flex-col` (8rem = topbar h-16/4rem + padding `<main>` md:p-8 atas+bawah/4rem), header row `lg:flex-shrink-0`, grid chat+sidebar `lg:flex-1 lg:min-h-0`, kolom chat `h-[70vh] lg:h-full` (ganti dari inline `style="height:70vh"` yang dulu fixed di semua breakpoint), kolom sidebar `lg:h-full lg:overflow-y-auto` -- kalau konten sidebar (quick prompts + riwayat + info box) lebih tinggi dari ruang tersedia, dia scroll SENDIRI, bukan bikin seluruh halaman lebih tinggi dari layar. Pola ini (hitung tinggi lewat `calc(100vh - <topbar> - <padding main>)`, bukan px/vh sembarangan) bisa dipakai lagi kalau ada halaman lain yang butuh viewport-fit serupa.
26. **`config/app.php` timezone `UTC` seharusnya `Asia/Jakarta` — 1 baris, dampaknya app-wide**: ditemukan lewat gejala "dashboard guru bilang Selamat Siang jam 21:31 WIB" -- `now()`/`Carbon::now()` di SELURUH aplikasi ngikutin config ini, jadi bukan cuma teks sapaan yang salah, tapi SEMUA logic yang bergantung jam/tanggal "sekarang" (validasi `before_or_equal:today`, filter `whereMonth/whereYear(..., now()->...)` di Jurnal & AI Advisor `ringkasan_kelas`, timestamp `created_at`/`updated_at` baru). Sudah diperbaiki ke `Asia/Jakarta`, WAJIB `php artisan config:clear` setelah timpa file (config bisa ke-cache). **Efek samping yang perlu disadari**: data lama (timestamp yang sudah kesimpan SEBELUM fix ini) TIDAK ikut berubah otomatis -- `DATETIME` MySQL tidak nyimpen info timezone, jadi record lama yang aslinya kejadian jam 21:31 WIB (kesimpan sebagai "14:31" waktu app masih UTC) akan terbaca mundur 7 jam kalau dibandingkan ulang dengan config baru. Kolom `tanggal` (dipilih manual, bukan otomatis dari jam) TIDAK kena efek ini. Kalau butuh histori lama akurat, perlu migration data terpisah buat geser +7 jam -- belum dikerjakan, dianggap tidak krusial untuk kebanyakan kasus pemakaian sistem ini.
27. **Dashboard Guru — 3 gap ditemukan & diperbaiki, semuanya soal representasi data, bukan bug crash**:
    - `$penugasanList` (kombinasi kelas+mapel yang diampu) tidak diurutkan sama sekali sebelum di-`take(6)` di view -- kalau guru punya >6 kombinasi, yang tampil di dashboard bisa jadi 6 yang SUDAH beres presensi hari itu, sementara yang masih perlu dikerjakan kesembunyi di balik "Lihat semua". Diperbaiki: `sortBy('sudahPresensi')` setelah flag itu dihitung, yang `false` (belum presensi) otomatis duluan.
    - `$nilaiPending = 0;` di-HARDCODE dengan komentar `// Kalkulasi ringan untuk dashboard` -- bukan cuma "kurang informatif", ini SELALU salah, tiap guru tiap saat lihat "0 Nilai Pending" apapun kondisi sebenarnya. Diganti kalkulasi sungguhan: loop tiap penugasan, hitung santri aktif di kelas itu dikurangi yang sudah punya `NilaiAkhir` untuk kombinasi kelas+mapel+TA itu.
    - Tidak ada jejak apapun soal tugas Wali Kelas di dashboard utama guru, padahal fitur itu baru saja di-highlight besar-besaran di sidebar (§ sidebar wali kelas). Ditambahkan kartu pengingat -- muncul cuma kalau `$user->isWaliKelas()`, kalau cuma 1 kelas arahkan langsung ke Predikat Sikap (tugas manual yang paling gampang kelupaan), kalau >1 kelas arahkan ke Dashboard Wali Kelas overview. Query & filter tahun ajaran SENGAJA sama persis dengan yang di `sidebar-nav.blade.php` (guru-sidebar-section) -- kalau salah satu diubah, ubah juga yang lain supaya jumlah kelas yang kehitung tetap konsisten di 2 tempat itu.
    - Stat card "Pertemuan Bulan Ini" & "Nilai Pending" sekarang bisa diklik (ke `guru.jurnal.index` & `guru.nilai.index`) -- "Mapel Diampu"/"Total Kelas" sengaja dibiarkan statis, tidak ada halaman detail tunggal yang pas dituju untuk itu.
28. **Konfirmasi: `users.nama_arab` sudah ada** (bukan cuma di `$fillable` model tanpa kolom sungguhan) -- kolomnya ditambahkan lewat migration terpisah `2026_07_29_000003_add_nama_arab_to_santri_and_users_table.php` (satu migration buat 2 tabel sekaligus: `santri` DAN `users`), bukan di migration awal `create_users_table`. Dipakai di `RaporArabService` buat nama wali kelas (`$kelas->waliKelas?->nama_arab ?? $kelas->waliKelas?->name`) -- kolomnya valid, tapi kalau field itu dibiarkan kosong sama admin waktu isi data user, tetap jatuh ke fallback nama Latin tanpa tanda apapun (bukan bug, cuma soal kelengkapan data).
29. **Dashboard Sysadmin — bahasa log teknis dibenahi + 2 gap representasi ditambal**:
    - **`ActivityLog.action` tidak layak ditampilkan mentah ke UI**: disimpan lewat `strtolower(class_basename($model))` di `ActivityLogService`. Buat model 1 kata (`Santri`) hasilnya rapi (`santri.created`), tapi buat model 2 kata+ (`KategoriMataPelajaran`, `PenugasanMengajar`, `KkmTingkatan`) `strtolower()` menghapus SEMUA sinyal pemisah kata (huruf besar), jadi satu kata nyambung tanpa spasi (`kategorimatapelajaran.created`) -- nyaris tidak kebaca bahkan buat developer, apalagi sysadmin non-teknis.
    - **Fix-nya BUKAN parsing ulang `action`** (percuma, sinyal pemisah katanya sudah hilang duluan), tapi accessor baru `ActivityLog::getDeskripsiAttribute()` yang ambil nama model dari kolom `model_type` (nama class ASLI, PascalCase utuh, tidak pernah kena `strtolower()`), dipisah per kata pakai regex `preg_replace('/(?<!^)[A-Z]/', ' $0', ...)`. Karena nama model di project ini sudah Bahasa Indonesia, hasil pisah-per-kata otomatis biasanya sudah jadi kalimat benar TANPA perlu daftar terjemahan manual per model (`MataPelajaran` → "Mata Pelajaran", `PenugasanMengajar` → "Penugasan Mengajar", dst) -- cuma 3 override manual yang ditambah: `Kkm`→`KKM`, `Kbm`→`KBM` (singkatan, regex cuma pertahanin huruf besar pertama), `User`→`Pengguna` (satu-satunya nama model non-Indonesia). Kata kerja (menambahkan/mengubah/menghapus) diambil dari SEGMEN TERAKHIR `action` (`explode('.', $action)` lalu `end()`) -- ini konsisten dipakai baik buat action otomatis dari `logCreate()`/`logUpdate()`/`logDelete()` MAUPUN action manual custom kayak `'presensi_kbm.updated'` (segmen terakhirnya tetap `updated` walau segmen depannya beda pola/tidak match nama model aslinya).
    - **Kalau bikin halaman lain yang nampilin `ActivityLog`** (mis. halaman `sysadmin.activity-log` yang lebih lengkap, belum sempat dicek isinya): pakai `$log->deskripsi`, JANGAN `$log->action` mentah lagi.
    - **2 gap representasi ditambal**: (a) tidak ada peringatan kalau `TahunAjaran::aktif()` kosong -- padahal itu prasyarat buat SEBAGIAN BESAR fitur akademik di seluruh sistem (lihat pola `abort_if(!$ta, ...)` yang tersebar di puluhan controller), sysadmin harus jadi yang pertama tahu, bukan nunggu laporan user; (b) "Total User: 47" polos tanpa breakdown per role, ditambah grid kecil per role (`User::selectRaw('role, count(*) as total')->groupBy('role')`) + quick-link "Kelola User →" ke `sysadmin.users.index` (route-nya ternyata sudah ada dari awal, cuma belum pernah ditautkan dari dashboard).
    - **Masih terbuka, belum dikerjakan**: (a) visibilitas error/log teknis (`storage/logs/laravel.log`) di dashboard -- butuh desain hati-hati (ukuran file, baca N baris terakhir, risiko kebocoran info sensitif kalau ditampilkan mentah ke UI); (b) belum dipastikan apakah percobaan login gagal tercatat di `ActivityLog` sama sekali -- perlu cek `LoginRequest`/`AuthenticatedSessionController` yang belum pernah dilihat.
30. **Import Kelas & Asrama Massal — 500 error kalau kelas kosong, + 2 gap kecil ditambal**:
    - **Bug utama (penyebab 500)**: `santri/import-bulk.blade.php` dulu menampilkan banner peringatan kalau kelas kosong untuk TA aktif, dan banner itu berisi link `route('admin.kelas.create')` -- route ini **tidak pernah terdaftar** (`KelasController` didaftarkan di bawah prefix `kurikulum` sebagai `kurikulum.kelas.create`, bukan `admin`). Begitu Blade coba resolve nama route yang tidak ada, langsung `RouteNotFoundException` (500) -- dan karena banner itu cuma muncul saat kelas kosong, bug ini nggak pernah kelihatan selama testing dilakukan dengan kelas sudah dibuat duluan.
    - **Fix, dipindah jadi 2 lapis, keduanya sebelum sempat ada request ke `import-bulk` sama sekali**:
        1. **Sisi tombol** ("Import Massal" di `santri/index.blade.php`): `SantriController::index()` sekarang kirim `$kelasKosong` (dari `Kelas::where('tahun_ajaran_id', $ta->id)->count() === 0`) ke view. Tombol dikasih `onclick="return cekKelasSebelumBulk(event)"` -- kalau kelas kosong, klik dicegat (`e.preventDefault()`) dan popup modal muncul ("Kelas masih kosong, silakan hubungi Kurikulum"), **tidak pindah halaman sama sekali**.
        2. **Sisi controller** (`SantriController::importBulk()`, jaring pengaman kalau ada yang akses URL `import-bulk` langsung tanpa lewat tombol): cek TA aktif & jumlah kelas dilakukan di awal method, **sebelum** `view('santri.import-bulk', ...)` dipanggil -- kalau kelas kosong (atau belum ada TA aktif), langsung `redirect()->route('admin.santri.index')->with('error', ...)`. Banner-dengan-link-rusak di `import-bulk.blade.php` sudah dihapus total -- halaman itu sekarang selalu dirender dalam kondisi kelas dipastikan sudah ada isinya.
    - **Gap tambahan yang ketahuan pas nambal ini**: `santri/index.blade.php` sebelumnya **tidak punya tempat menampilkan flash message sama sekali** -- jadi redirect `->with('error', ...)` dari controller (mis. kasus "belum ada TA aktif") selama ini silent, user cuma lihat halaman reload tanpa penjelasan. Ditambal sekalian: blok `@if(session('error'))` yang menampilkan popup modal berisi teks error dari session, dipakai bareng oleh kedua guard di atas (jaring pengaman URL langsung maupun kasus TA belum aktif) supaya wording-nya konsisten tanpa duplikasi di 2 tempat.
    - **Bug turunan kecil di `SantriImportService::processRow()`** (ditemukan pas review, bukan penyebab 500 di atas): `kelas_valid` dan `asrama_valid` default `true` di inisialisasi `$record`, tapi cabang "tidak ketemu" (`if (!$kelas)` / `if (!$asrama)`) cuma nambah ke `errors[]`, tidak pernah balikin flag itu ke `false`. Tidak menyebabkan salah tampil di preview (karena `validateAndPrepare()` cek `errors` duluan sebelum cek flag ini), tapi datanya sendiri tidak akurat -- diperbaiki supaya kedua flag konsisten dengan kondisi sebenarnya, buat jaga-jaga kalau ke depan ada kode lain yang baca flag ini langsung tanpa lewat cek `errors`.
31. **Identitas santri untuk Import Kelas & Asrama Massal (dan form satuan) — diganti dari NIS jadi NISN**:
    - **Keputusan**: NISN sekarang jadi identitas utama santri (wajib + unik), NIS jadi opsional (boleh kosong, tetap unik kalau diisi). Sebelumnya kebalikannya (NIS wajib, NISN opsional) -- diubah karena NISN yang sifatnya nasional/permanen, cocok jadi kunci pencocokan yang lebih stabil dibanding NIS yang bisa beda skema penomoran antar tahun ajaran/pondok.
    - **`SantriController::store()` & `update()`**: validasi `nisn` jadi `['required', 'string', 'unique:santri,nisn']` (atau `,{$santri->id}` di update), `nis` jadi `['nullable', 'string', 'unique:santri,nis']`.
    - **`SantriImportService::processRow()`**: pencocokan santri dari Excel sekarang `Santri::where('nisn', $nisn)->first()`, bukan `where('nis', ...)` lagi. Key array `$record['nis']` diganti jadi `$record['nisn']` di seluruh service (termasuk pesan-pesan di `errors[]`/`messages[]` pada `save()`).
    - **`SantriImportTemplateExport.php`**: header kolom Excel pertama diganti dari "NIS" jadi "NISN" -- **kalau ada admin yang masih pakai template lama (header "NIS")**, upload akan gagal cocok karena `WithHeadingRow` nge-slug jadi `nis`, sedangkan `SantriImportService` sekarang baca key `nisn`. Perlu sosialisasi ke admin supaya download ulang template kalau masih pakai file lama.
    - **View yang ikut disesuaikan**: `santri/import-preview.blade.php` (kolom tabel "NISN"), `santri/_form.blade.php` (field NISN dipindah ke urutan pertama + jadi wajib, NIS jadi opsional), `santri/edit.blade.php` (subtitle header tampilkan NISN, bukan NIS lagi -- karena NIS sekarang bisa `null`).
    - **Belum disentuh, masih pakai NIS**: filter pencarian santri di `santri/index.blade.php` -- placeholder input sudah menyebut "Cari nama, NIS, NISN..." tapi query di `SantriController::index()` cuma `orWhere('nis', 'like', ...)`, belum ditambah `orWhere('nisn', ...)`. Placeholder saat ini sedikit menyesatkan (menjanjikan bisa cari by NISN padahal belum).
32. **IP pengunjung selalu tercatat sebagai IP server (127.0.0.1), bukan IP asli — kasus deployment via Cloudflare Tunnel**:
    - **Konteks deployment**: project ini dijalankan lokal (`php artisan serve`, default bind ke `127.0.0.1`) lalu diekspos ke internet lewat **Cloudflare Tunnel** (`cloudflared`) yang jalan di komputer yang sama. Alur trafiknya: pengunjung → Cloudflare Edge → `cloudflared` (lokal) → Laravel (`127.0.0.1`).
    - **Root cause**: `cloudflared` connect ke Laravel lewat `127.0.0.1`, jadi dari sudut pandang Laravel, koneksi selalu datang dari localhost. Cloudflare **sudah** mengirim IP asli pengunjung lewat header (`X-Forwarded-For`, `CF-Connecting-IP`), tapi Laravel **default-nya tidak mempercayai header itu dari siapapun** (termasuk dari `127.0.0.1`) -- ini fitur keamanan bawaan supaya orang tidak bisa kirim header palsu buat nyamar jadi IP lain. Akibatnya `$request->ip()` (dipakai di `ActivityLogService` dan tempat lain) selalu balikin `127.0.0.1` / IP komputer server, bukan IP pengunjung asli.
    - **Fix**: project ini pakai struktur Laravel 11+/13 (`bootstrap/app.php`, TIDAK ADA `app/Http/Kernel.php` maupun `TrustProxies.php`) -- jadi konfigurasi trusted proxy ditaruh di `bootstrap/app.php`, di dalam closure `->withMiddleware()`:
      ```php
      $middleware->trustProxies(
          at: '127.0.0.1',
          headers: Request::HEADER_X_FORWARDED_FOR |
              Request::HEADER_X_FORWARDED_HOST |
              Request::HEADER_X_FORWARDED_PORT |
              Request::HEADER_X_FORWARDED_PROTO,
      );
      ```
      `at: '127.0.0.1'` artinya "cuma percaya header forward yang datang dari proxy yang connect dari localhost" -- setelah ini, `$request->ip()` baca IP asli dari `X-Forwarded-For`, bukan lagi dari koneksi TCP langsung (`127.0.0.1`).
    - **Wajib `php artisan optimize:clear` setelah ubah `bootstrap/app.php`** kalau ada `config:cache` yang aktif, karena config Laravel di-cache dan perubahan tidak kepakai sebelum di-clear.
    - **Catatan keamanan, jangan diabaikan**: `at: '127.0.0.1'` ini aman SELAMA Laravel benar-benar cuma bisa diakses lewat `cloudflared` (satu pintu masuk). Kalau nanti ada yang ganti jadi `at: '*'` (percaya semua proxy) supaya lebih fleksibel, itu cuma aman selama server tidak juga kebuka ke jaringan lain di luar `cloudflared` (mis. lewat port forwarding tambahan) -- kalau `'*'` dipasang dan server ternyata bisa diakses langsung dari jaringan lokal, siapapun di jaringan itu bisa kirim `X-Forwarded-For` palsu langsung ke Laravel dan nyamar jadi IP manapun.
    - **Belum dicek**: apakah konfigurasi `cloudflared` (named tunnel `config.yml` atau Quick Tunnel) perlu setting tambahan supaya `CF-Connecting-IP`/`X-Forwarded-For` terkirim -- secara default Cloudflare sudah otomatis mengirim header ini tanpa setting khusus, jadi kemungkinan besar tidak perlu, tapi belum ada verifikasi langsung dari log tunnel yang bersangkutan.

---

## Masalah yang Diketahui, Belum Diperbaiki

Ditulis eksplisit supaya tidak "ditemukan ulang" dari nol — dan supaya siapapun yang lanjut tahu ini **keputusan sadar untuk ditunda**, bukan terlewat begitu saja:

1. **FR-11 Notifikasi Sistem** — tabel & trait `Notifiable` ada, tidak ada satupun `Notification` class yang jalan. Perlu dirancang dulu event apa saja yang trigger notif sebelum diimplementasikan (bukan sekadar bugfix).
2. **Lupa password & edit profil mandiri** — `routes/auth.php` (scaffolding Breeze) ada di repo, tapi tidak pernah di-`require` dari `routes/web.php`. Akibatnya, route login/register/password reset default tidak aktif, dan tidak ada halaman ganti password untuk user selain lewat Sysadmin. Perlu keputusan: mau diaktifkan penuh (dengan SMTP untuk reset-password email) atau cukup fitur "ganti password sendiri saat login" tanpa email?
3. **Komponen Nilai: field `mata_pelajaran_id`/`tipe`/`deskripsi`** — form-nya ada, tervalidasi, tapi **tidak pernah tersimpan** (kolom tidak ada di tabel `komponen_nilai`, tidak ada di `$fillable`). Komponen nilai sebenarnya **global** (bukan per-mapel) — form yang ada saat ini **menyesatkan** karena seolah-olah bisa di-scope per mapel. Perlu diputuskan: hapus field itu dari form (karena memang global), atau benar-benar bikin per-mapel (migrasi + query berubah signifikan)?
4. **Dependency `maatwebsite/excel` & `barryvdh/laravel-dompdf` sudah kembali ada di `composer.json`** — item ini sudah diperbaiki. Pastikan `composer install` dijalankan setelah checkout untuk memasang dependensi, tapi catatan ini sekarang usang.
5. **Kotak alert/badge status pakai warna hex hardcode** — 16 file punya `style="background: #fef2f2; ..."` yang tidak adaptif dark mode (tetap terang meski dark mode aktif). Teks tetap terbaca (bukan bug fatal seperti kasus input form), cuma kurang selaras secara visual.
6. **Belum ada test case untuk modul akademik** — struktur `tests/` ada dari scaffolding, belum ada test spesifik untuk Penugasan/Nilai/Presensi/PPDB.
7. **`santri_kelas` tidak rollover otomatis saat ganti Tahun Ajaran** — tabel ini unique per `(santri_id, tahun_ajaran_id)`, tapi `TahunAjaranController::aktifkan()` cuma flip flag `is_active`, TIDAK pernah copy baris `santri_kelas` dari TA lama ke TA baru. Akibatnya begitu TA baru diaktifkan, **semua santri (bisa 1000+) otomatis "tanpa kelas"** untuk TA itu sampai di-assign ulang satu-satu lewat form Edit Santri. Ini alasan utama kenapa fitur "Import Kelas & Asrama Massal" dibutuhkan (lihat tabel di atas) — tapi itu baru menutupi gejalanya (bantu assign cepat), bukan solusi permanen di titik `aktifkan()`.
8. **Kenaikan kelas massal — pendekatan belum diputuskan final** — sempat ditawarkan 2 opsi ke user: (a) Excel-only (download-edit-upload, sudah dibangun sebagian, lihat item Import di atas), (b) UI pemetaan cepat "7A → 8A" sekali klik untuk seluruh rombongan + Excel cuma buat pengecualian individual. User belum eksplisit memilih salah satu — yang sudah berjalan sekarang murni jalur (a). Kalau ke depan mau ditambah jalur (b) juga, itu fitur terpisah, bukan pengganti Import yang sudah ada.
9. ~~**View eksport raport arab - masih belum menemukan titik temu dari format yang diinginkan** — sempat dibentuk dengan view yang sederhana, namun masih belum menemukan komposisi yang tepat, terutama ketika menggunakan barryvdh/pdf, sehingga harus diganti menjadi mpdf/mpdf untuk menangani permasalahan dimana format bahasa arabnya sudah benar, namun masih belum menyelesaikan titik temu dari format yang diinginkan oleh client.~~ **✅ SUDAH DIPERBAIKI** — migrasi ke mPDF dengan layout baru selesai. Lihat Riwayat & Catatan Perbaikan poin 15.

---

## Kredensial Testing

Lihat [README.md § Default Users](../README.md#default-users) — semua akun pakai password `password`, dibuat dari `database/seeders/UsersSeeder.php`.

---

_Dokumen ini dibuat berdasarkan riwayat pengembangan sistem secara kolaboratif. Kalau ada keputusan desain di kode yang tidak terjelaskan di sini, kemungkinan besar itu bagian yang belum sempat didokumentasikan — bukan berarti boleh diubah bebas tanpa konfirmasi ulang ke pemilik sistem._
````
