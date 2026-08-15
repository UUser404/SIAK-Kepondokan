# ✅ Full `DEVELOPER_GUIDE.md` dengan Perubahan Terbaru

Berikut adalah file lengkap `DEVELOPER_GUIDE.md` yang sudah diperbarui dengan **Riwayat & Catatan Perbaikan** poin 15 tentang Rapor Arab:

---

````markdown
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

| Yang sering ditebak                                               | Yang benar                                                                                                                                                                                                                                            |
| ----------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `pelanggaran.tanggal_pelanggaran`                                 | `pelanggaran.tanggal`                                                                                                                                                                                                                                 |
| `pelanggaran->kategoriPelanggaran` (relasi)                       | `pelanggaran->kategori()`                                                                                                                                                                                                                             |
| `nilai_akhir.semester` / `tahun_ajar` (string)                    | tidak ada — pakai relasi `tahunAjaran()` ke tabel `tahun_ajaran`                                                                                                                                                                                      |
| `kamar.nama_kamar`                                                | `kamar.nomor_kamar`                                                                                                                                                                                                                                   |
| `tenaga_pendidik.nama`                                            | tidak ada — nama guru ada di `users.name` lewat relasi `user()`                                                                                                                                                                                       |
| `jadwal_pelajaran.guru_id` mengacu ke `tenaga_pendidik.id`        | **salah** — mengacu ke `users.id` langsung                                                                                                                                                                                                            |
| `santri->kelasAktif()->kelas_id`                                  | **salah** — `kelasAktif()` adalah `hasOneThrough` yang me-return model `Kelas` langsung, jadi PK-nya `id`, bukan `kelas_id` (kolom `kelas_id` tidak ada di tabel `kelas`)                                                                             |
| `santri.user_id` selalu terisi                                    | **salah (sejak 2026-07-13)** — kolom ini nullable karena santri tidak pernah login (tidak ada role `santri`); jangan asumsikan tidak pernah null                                                                                                      |
| `mata_pelajaran.kategori` adalah foreign key                      | **salah (sejak 2026-08-11)** — tetap kolom string biasa, dicocokkan by nilai `nama` ke tabel `kategori_mata_pelajaran`, bukan `kategori_mata_pelajaran_id`; lihat §9 poin 16                                                                          |
| `mata_pelajaran.kkm` adalah sumber KKM yang dipakai sistem        | **salah (sejak 2026-08-11)** — kolom ini cuma fallback; sumber utama KKM adalah tabel `kkm_tingkatan` lewat method `MataPelajaran::kkmUntukTingkatan($tingkatanId)`. Jangan baca `->kkm` langsung kalau ada konteks tingkatan/kelas; lihat §9 poin 17 |
| `mata_pelajaran.tingkat` membatasi mapel ke satu tingkat tertentu | **salah (dead field sejak 2026-08-11)** — kolom masih ada di DB tapi tidak dipakai UI/validasi manapun, dan kemungkinan besar sudah lama gagal ke-save (bukan di `$fillable`). Jangan diandalkan buat filter apapun; lihat §9 poin 21                 |

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

```

---

## 📋 **Ringkasan Perubahan**

| Bagian | Perubahan |
|--------|-----------|
| **Riwayat & Catatan Perbaikan** | ✅ Ditambahkan poin 15 tentang Rapor Arab (migrasi Dompdf → mPDF, perbaikan layout, helper angka Arab, tanggal Hijriah fallback) |
| **Masalah yang Diketahui** | ✅ Poin 9 dicoret dan ditandai **SUDAH DIPERBAIKI** dengan referensi ke poin 15 |

---

**Sekarang `DEVELOPER_GUIDE.md` sudah terupdate dengan semua perubahan rapor Arab!** 😊
```
