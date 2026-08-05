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
| Kode | Nama | Bobot | Maks Input |
|---|---|---|---|
| `UH` | Ulangan Harian | 20% | 2x |
| `TUGAS` | Tugas | 15% | 4x |
| `PRAKTIK` | Praktik | 15% | 2x |
| `UTS` | UTS | 20% | 1x |
| `UAS` | UAS | 30% | 1x |

`maks_input` bisa diubah admin/kurikulum lewat menu **Data Master → Komponen Nilai**, tidak hardcode.

### Aturan Kalkulasi ("Cara A" — hasil diskusi eksplisit, JANGAN diubah tanpa alasan kuat)

1. **Per siswa, independen dari siswa lain.** Nilai siswa A tidak pernah dipengaruhi nilai/jumlah input siswa B, walau satu kelas yang sama. *(Sempat dipertimbangkan model "class-wide" di mana pembagi rata-rata sama untuk semua siswa sekelas — **ditolak** karena terlalu kompleks & tidak lazim di sistem akademik.)*
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
```

### Penamaan Kolom yang Sering Salah Diasumsikan (cek dulu sebelum pakai!)
| Yang sering ditebak | Yang benar |
|---|---|
| `pelanggaran.tanggal_pelanggaran` | `pelanggaran.tanggal` |
| `pelanggaran->kategoriPelanggaran` (relasi) | `pelanggaran->kategori()` |
| `nilai_akhir.semester` / `tahun_ajar` (string) | tidak ada — pakai relasi `tahunAjaran()` ke tabel `tahun_ajaran` |
| `kamar.nama_kamar` | `kamar.nomor_kamar` |
| `tenaga_pendidik.nama` | tidak ada — nama guru ada di `users.name` lewat relasi `user()` |
| `jadwal_pelajaran.guru_id` mengacu ke `tenaga_pendidik.id` | **salah** — mengacu ke `users.id` langsung |
| `santri->kelasAktif()->kelas_id` | **salah** — `kelasAktif()` adalah `hasOneThrough` yang me-return model `Kelas` langsung, jadi PK-nya `id`, bukan `kelas_id` (kolom `kelas_id` tidak ada di tabel `kelas`) |
| `santri.user_id` selalu terisi | **salah (sejak 2026-07-13)** — kolom ini nullable karena santri tidak pernah login (tidak ada role `santri`); jangan asumsikan tidak pernah null |

---

## Modul per Role

| Role | Namespace Controller | Prefix Route |
|---|---|---|
| Staf Admin | `App\Http\Controllers\Admin\*` | `admin.*` |
| Wakil Kurikulum | `App\Http\Controllers\Kurikulum\*` | `kurikulum.*` |
| Tenaga Pendidik | `App\Http\Controllers\Guru\*` | `guru.*` |
| Bagian Kesantrian | `App\Http\Controllers\Kesantrian\*` | `kesantrian.*` |
| Administrator Sistem | `App\Http\Controllers\Sysadmin\*` | `sysadmin.*` |
| Mudir Pondok | `App\Http\Controllers\Mudir\*` (dashboard saja, read-only) | `mudir.*` |

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

---

## Riwayat & Catatan Perbaikan (ringkas)

Bug signifikan yang **sudah ditemukan & diperbaiki**. Kalau ketemu kode yang "terasa aneh" atau beda dari SRS, cek dulu di sini sebelum "membenarkan" sesuatu yang sebenarnya sudah sengaja begitu.

- **Routing salah/ghost route**: `JadwalController` (`guru_id` divalidasi ke `tenaga_pendidik`, harusnya `users.id`), `SantriController::export()` (view tidak ada), PPDB & Kamar (`Route::resource()` dipakai padahal controller tidak punya ke-7 method standar), `kesantrian.rekap.*` (nunjuk view yang tidak ada) — semua sudah diperbaiki jadi route eksplisit / method & view yang benar.
- **View path salah folder**: beberapa `@include('kurikulum.jadwal._form')` nunjuk folder yang tidak ada (views itu flat, bukan per-role, lihat §7) — sudah diperbaiki.
- **9 halaman yang belum pernah dibuat** (`asrama/create,edit`, `pelanggaran/show`, `pendidik/show`, `santri/profil`, `template-surat/show,edit`, `surat/edit`, `users/create,edit`) — sudah dibuat semua.
- **Sintaks Blade `{{ }}` rusak di `<script>`** — sudah 2x kejadian (`nilai/show.blade.php`, `presensi-kbm/index.blade.php`), ke-mangle jadi `{ { $x - > y } }` (kemungkinan auto-formatter) sampai bikin seluruh script block gagal parse di browser. **Hati-hati kalau ada tool auto-format jalan di file `.blade.php` yang isinya `{{ }}` di dalam `<script>`/`style=""`.**
- **Dark mode — 3 lapis masalah, semua sudah diperbaiki**: (1) `tailwind.config.js` kurang `darkMode: 'class'` — root cause, lihat §7; (2) puluhan file pakai `bg-gray-50` tanpa `dark:` di elemen form — dicover 1 aturan CSS global; (3) beberapa elemen non-form (bubble chat AI, badge, dst) punya bg statis yang bentrok sama teks token `text-siakad-dark` — ditambal satu-satu.
- **Sistem Nilai**: awalnya cuma 1 nilai/komponen/semester, diperluas jadi multi-input pakai kolom `slot` (lihat §4).
- **Jadwal → Penugasan**: `JadwalPelajaran` diganti `PenugasanMengajar` sebagai gerbang akses (lihat §3).
- **`SantriController` (create/edit/store/update)**: beberapa bug — `Undefined variable $kelasList`, salah baca `kelasAktif()->kelas_id` (harusnya `->id`, PK model `Kelas`), dan penempatan kelas yang divalidasi tapi tidak pernah tersimpan ke `santri_kelas` — semua sudah diperbaiki.
- **`santri.user_id`**: sempat NOT NULL padahal santri tidak pernah login (tidak ada role `santri`) — sudah dibikin nullable.
- **Tingkatan**: dulu cuma bisa diubah lewat seeder, sekarang ada CRUD lengkap (`Admin\TingkatanController`), dengan guard supaya tidak bisa hapus tingkatan yang masih ada kelasnya (`cascadeOnDelete` ke `kelas`).
- **Modul Wali Kelas** (Predikat Sikap + Nilai Ekstrakurikuler): sempat ada celah otorisasi — `santri_id` di body request tidak dicek benar milik kelas wali kelas yang login — sudah ditambal cross-check.
- **Rapor untuk Wali Kelas**: view rapor sempat hardcode route `kurikulum.rapor.*`, bikin wali kelas kena 403 saat lihat/cetak — sudah dibuat dinamis (`$routePrefix` berdasar `request()->routeIs(...)`).
- **Presensi/Jurnal KBM**: sempat "siloed" per guru (beda dari Nilai yang shared per kelas+mapel) — sudah disamakan; guru pengganti sekarang dapat akses penuh (lihat+edit+hapus) ke riwayat guru sebelumnya (**keputusan disengaja**, lihat §3).
- **Import Kelas & Asrama Massal**: 2 bug (baca key Excel salah format — harus slug seperti `nis` bukan `'NIS'`; kondisi update asrama salah cek field) + fitur belum ke-wire sama sekali (route/view/tombol belum ada, payload preview terlalu berat di session) — semua sudah dibuat & diperbaiki.

---

## Masalah yang Diketahui, Belum Diperbaiki

Ditulis eksplisit supaya tidak "ditemukan ulang" dari nol — dan supaya siapapun yang lanjut tahu ini **keputusan sadar untuk ditunda**, bukan terlewat begitu saja:

1. **FR-11 Notifikasi Sistem** — tabel & trait `Notifiable` ada, tidak ada satupun `Notification` class yang jalan. Perlu dirancang dulu event apa saja yang trigger notif sebelum diimplementasikan (bukan sekadar bugfix).
2. **Lupa password & edit profil mandiri** — `routes/auth.php` (scaffolding Breeze) tidak pernah di-`require`. Tidak ada halaman ganti password untuk user selain lewat Sysadmin. Perlu keputusan: mau diaktifkan penuh (perlu SMTP untuk reset-password email) atau cukup fitur "ganti password sendiri saat login" tanpa email?
3. **Komponen Nilai: field `mata_pelajaran_id`/`tipe`/`deskripsi`** — form-nya ada, tervalidasi, tapi **tidak pernah tersimpan** (kolom tidak ada di tabel `komponen_nilai`, tidak ada di `$fillable`). Komponen nilai sebenarnya **global** (bukan per-mapel) — form yang ada saat ini **menyesatkan** karena seolah-olah bisa di-scope per mapel. Perlu diputuskan: hapus field itu dari form (karena memang global), atau benar-benar bikin per-mapel (migrasi + query berubah signifikan)?
4. **Dependency `maatwebsite/excel` & `barryvdh/laravel-dompdf` hilang dari `composer.json`** — kodenya sudah pakai package ini di banyak tempat (Export, Rapor, Surat) tapi belum terdaftar sebagai dependency. Wajib `composer require` manual sebelum fitur Export/PDF bisa jalan.
5. **Kotak alert/badge status pakai warna hex hardcode** — 16 file punya `style="background: #fef2f2; ..."` yang tidak adaptif dark mode (tetap terang meski dark mode aktif). Teks tetap terbaca (bukan bug fatal seperti kasus input form), cuma kurang selaras secara visual.
6. **Belum ada test case untuk modul akademik** — struktur `tests/` ada dari scaffolding, belum ada test spesifik untuk Penugasan/Nilai/Presensi/PPDB.
7. **`santri_kelas` tidak rollover otomatis saat ganti Tahun Ajaran** — tabel ini unique per `(santri_id, tahun_ajaran_id)`, tapi `TahunAjaranController::aktifkan()` cuma flip flag `is_active`, TIDAK pernah copy baris `santri_kelas` dari TA lama ke TA baru. Akibatnya begitu TA baru diaktifkan, **semua santri (bisa 1000+) otomatis "tanpa kelas"** untuk TA itu sampai di-assign ulang satu-satu lewat form Edit Santri. Ini alasan utama kenapa fitur "Import Kelas & Asrama Massal" dibutuhkan (lihat tabel di atas) — tapi itu baru menutupi gejalanya (bantu assign cepat), bukan solusi permanen di titik `aktifkan()`.
8. **Kenaikan kelas massal — pendekatan belum diputuskan final** — sempat ditawarkan 2 opsi ke user: (a) Excel-only (download-edit-upload, sudah dibangun sebagian, lihat item Import di atas), (b) UI pemetaan cepat "7A → 8A" sekali klik untuk seluruh rombongan + Excel cuma buat pengecualian individual. User belum eksplisit memilih salah satu — yang sudah berjalan sekarang murni jalur (a). Kalau ke depan mau ditambah jalur (b) juga, itu fitur terpisah, bukan pengganti Import yang sudah ada.

---

## Kredensial Testing

Lihat [README.md § Default Users](../README.md#default-users) — semua akun pakai password `password`, dibuat dari `database/seeders/UsersSeeder.php`.

---

*Dokumen ini dibuat berdasarkan riwayat pengembangan sistem secara kolaboratif. Kalau ada keputusan desain di kode yang tidak terjelaskan di sini, kemungkinan besar itu bagian yang belum sempat didokumentasikan — bukan berarti boleh diubah bebas tanpa konfirmasi ulang ke pemilik sistem.*
