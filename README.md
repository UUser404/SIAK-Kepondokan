<h1 align="center">SIAK-AI — Sistem Informasi Akademik Kepondokan</h1>

<p align="center">
  <strong>Sistem akademik &amp; kesantrian modern untuk pondok pesantren, dibangun dengan Laravel</strong>
</p>

<p align="center">
  <a href="#overview">Overview</a> •
  <a href="#features">Fitur</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#installation">Instalasi</a> •
  <a href="#configuration">Konfigurasi</a> •
  <a href="#default-users">Default Users</a> •
  <a href="#project-structure">Struktur Project</a> •
  <a href="#known-limitations">Known Limitations</a> •
  <a href="#documentation">Dokumentasi</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-red?style=flat-square&logo=laravel" alt="Laravel Version">
  <img src="https://img.shields.io/badge/PHP-8.3+-blue?style=flat-square&logo=php" alt="PHP Version">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38bdf8?style=flat-square&logo=tailwindcss" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/AI-Groq%20Llama%203.3-8B5CF6?style=flat-square" alt="AI Provider">
  <img src="https://img.shields.io/badge/Status-Development-yellow?style=flat-square" alt="Status">
</p>

---

## Overview

**SIAK-AI** adalah sistem informasi akademik &amp; kesantrian untuk **Pondok Pesantren Modern Al Islam**, dibangun untuk menggantikan proses administrasi manual (rekap nilai, presensi, data santri, surat-menyurat) dengan platform digital terpusat berbasis web.

Sistem ini melayani 6 peran berbeda dalam satu platform — Mudir Pondok, Wakil Kepala Sekolah Bidang Kurikulum, Tenaga Pendidik, Bagian Kesantrian, Staf Admin, dan Administrator Sistem — masing-masing dengan akses dan dashboard sesuai tanggung jawabnya.

### Highlights

- **AI Advisor** — konsultasi akademik untuk guru berbasis Groq (Llama 3.3), dengan mekanisme fallback jika layanan AI tidak tersedia
- **Penugasan Mengajar** — guru mengajar berdasarkan penugasan kelas+mapel dari Kurikulum (tanpa jadwal hari/jam yang kaku), presensi &amp; nilai diinput manual sesuai kebutuhan
- **Penilaian fleksibel** — komponen nilai (UH, Tugas, Praktik, UTS, UAS) mendukung input berulang per komponen (mis. Tugas hingga 4x), dihitung otomatis dari rata-rata slot yang terisi
- **RBAC granular** — kombinasi role sederhana (`users.role`) dan permission library (Spatie) untuk kontrol akses halus
- **Manajemen Kesantrian** — asrama, kamar, pelanggaran (dengan sistem poin), prestasi, kegiatan &amp; presensi non-KBM
- **PPDB Online** — pendaftaran santri baru, verifikasi berkas, hingga konversi jadi data santri aktif
- **Ekspor & Cetak** — Excel (nilai, presensi, data santri) dan PDF (rapor, transkrip, surat resmi)
- **Lokal penuh** — antarmuka Bahasa Indonesia, format tanggal Indonesia, dark/light mode

> Proyek ini berangkat dari dokumen SRS akademik formal, lalu dikembangkan lebih jauh melampaui cakupan SRS aslinya (PPDB, surat-menyurat, prestasi, audit log, dsb). Sejumlah keputusan desain di sistem ini **menyimpang secara sengaja** dari SRS awal berdasarkan diskusi lanjutan — lihat [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md) untuk detail dan alasannya.

---

## Features

### Mudir Pondok
| Fitur | Deskripsi |
|---|---|
| **Dashboard Eksekutif** | Ringkasan jumlah santri aktif, grafik kehadiran, rekap nilai rata-rata |
| **Laporan Umum Pondok** | Statistik lintas unit untuk pengambilan keputusan |

### Wakil Kepala Sekolah Bidang Kurikulum
| Fitur | Deskripsi |
|---|---|
| **Kelola Kelas & Rombel** | CRUD kelas, wali kelas, kapasitas |
| **Penugasan Mengajar** | Tetapkan guru ke kombinasi kelas+mapel (1 guru bisa banyak kelas & mapel) — **ini yang jadi syarat guru boleh input presensi/nilai/jurnal** |
| **Komponen & Bobot Nilai** | Atur komponen penilaian (UH, Tugas, Praktik, UTS, UAS), bobot, dan berapa kali boleh diinput per komponen |
| **Finalisasi Nilai** | Kalkulasi nilai akhir otomatis per kelas & mapel |
| **Rapor & Transkrip** | Generate PDF rapor dan transkrip nilai santri |
| **Dashboard Analitik** | Efektivitas KBM: presensi siswa/guru, materi yang diajarkan |
| **Jadwal Pelajaran** *(nonaktif)* | Fitur penjadwalan hari/jam — dibangun tapi saat ini disembunyikan dari menu (lihat [Known Limitations](#known-limitations)) |

### Tenaga Pendidik (Guru)
| Fitur | Deskripsi |
|---|---|
| **Input Presensi KBM** | Presensi per pertemuan, tanggal diisi manual (maksimal hari ini) |
| **Input Nilai** | Grid nilai per komponen, mendukung banyak input untuk komponen seperti Tugas |
| **Jurnal Mengajar** | Catatan materi &amp; kehadiran mengajar per pertemuan |
| **AI Advisor** | Konsultasi strategi pengajaran &amp; analisis nilai dengan AI |
| **Dashboard Guru** | Ringkasan kelas &amp; mapel yang diampu, status input presensi harian |

### Bagian Kesantrian
| Fitur | Deskripsi |
|---|---|
| **Kegiatan Kesantrian** | Kelola kegiatan non-KBM (sholat berjamaah, dsb) &amp; presensinya |
| **Manajemen Asrama & Kamar** | CRUD asrama, kamar, penempatan &amp; perpindahan santri |
| **Pelanggaran Santri** | Catat pelanggaran dengan sistem poin (ambang panggilan wali/skors/dikeluarkan otomatis) |
| **Prestasi Santri** | Catat prestasi akademik/non-akademik |

### Staf Admin
| Fitur | Deskripsi |
|---|---|
| **Data Master** | Kelola santri, tenaga pendidik, mata pelajaran, kelas, tahun ajaran |
| **PPDB** | Kelola periode pendaftaran, verifikasi berkas, terima/tolak, konversi jadi santri aktif |
| **Surat-Menyurat** | Template surat, generate &amp; cetak PDF surat resmi |
| **Export Data** | Ekspor santri, nilai, dan presensi ke Excel |

### Administrator Sistem
| Fitur | Deskripsi |
|---|---|
| **Manajemen User & Role** | CRUD akun, assign role (RBAC) |
| **Audit Trail** | Log aktivitas perubahan data kritis (siapa, kapan, apa) |

### Security Features
- ✅ Role-based access control (kombinasi `users.role` + Spatie Permission)
- ✅ CSRF protection di seluruh form
- ✅ Password hashing (bcrypt)
- ✅ Middleware otorisasi per-route berbasis role
- ✅ Validasi input server-side di seluruh endpoint

---

## Tech Stack

### Backend
| Teknologi | Versi | Keterangan |
|---|---|---|
| **PHP** | 8.3+ | Server-side language |
| **Laravel** | 13.x | PHP Framework |
| **Spatie Laravel-Permission** | 8.x | Role & permission management |
| **Groq API** | Llama 3.3 70B | AI Advisor (dengan fallback graceful) |

### Frontend
| Teknologi | Keterangan |
|---|---|
| **TailwindCSS** | Utility-first CSS, custom design token (`--siakad-primary`, dst) |
| **Blade Components** | `<x-app-layout>`, komponen form reusable |
| **Dark/Light Mode** | CSS variable-based theming |

### Database
| Teknologi | Keterangan |
|---|---|
| **MySQL 8.0+ / PostgreSQL 14+** | Database utama (sesuai target NFR) |
| **SQLite** | Cocok untuk development lokal |

### Paket Tambahan yang Dibutuhkan (⚠️ belum ada di `composer.json` default)
Kode sudah memakai package berikut, tapi **wajib di-install manual** karena belum terdaftar:
```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf
```
Tanpa ini, fitur **Export Excel** (Santri/Nilai/Presensi) dan **Cetak PDF** (Rapor, Transkrip, Surat) akan gagal dengan error *Class not found*.

---

## Installation

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ / PostgreSQL 14+ (atau SQLite untuk development)
- API Key Groq (opsional, untuk fitur AI Advisor — [dapatkan di sini](https://console.groq.com/keys))

### Quick Start

```bash
# 1. Clone / extract project
cd siak-kepondokan

# 2. Install dependency PHP inti
composer install

# 3. Install dependency tambahan (export & PDF — wajib, lihat catatan Tech Stack di atas)
composer require maatwebsite/excel barryvdh/laravel-dompdf

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Isi GROQ_API_KEY di .env (opsional, untuk AI Advisor)

# 7. Install dependency frontend
npm install
npm run build

# 8. Jalankan migrasi + seeder
php artisan migrate --seed

# 9. Jalankan server development
php artisan serve
```

---

## Configuration

### Environment Variables

#### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siak_kepondokan
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### AI Advisor (Groq)
```env
AI_PROVIDER=groq
GROQ_API_KEY=your_groq_api_key
GROQ_MODEL=llama-3.3-70b-versatile
```
> Kalau `GROQ_API_KEY` kosong atau layanan Groq down, `AiAdvisorService` otomatis fallback dengan pesan yang informatif — fitur lain di sistem tetap berjalan normal (NFR-11).

### Konfigurasi Akademik (`config/siak.php`)

```php
return [
    'pondok' => [
        'nama' => 'Pondok Pesantren Modern Al Islam',
        // alamat, kota, telp, email, website, logo, kepala
    ],

    'penilaian' => [
        'kkm_default' => 70,
        'predikat'    => [ /* A/B/C/D/E berdasarkan rentang nilai */ ],
    ],

    'presensi' => [
        'status_kbm'    => ['hadir', 'sakit', 'izin', 'alpa'],
        'min_kehadiran' => 75, // Persen minimum kehadiran
    ],

    'pelanggaran' => [
        'batas_poin_panggilan_wali' => 50,
        'batas_poin_skors'          => 75,
        'batas_poin_dikeluarkan'    => 100,
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'groq'),
        'groq'     => ['model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile')],
    ],

    'ppdb'  => ['prefix_nomor' => 'PPDB'],   // Format: PPDB-2025-0001
    'surat' => ['prefix_nomor' => 'PP-AI'],  // PP-AI/[No]/[Bulan Romawi]/[Tahun]
];
```

Komponen penilaian (bobot & berapa kali boleh diinput per komponen) diatur lewat menu **Data Master → Komponen Nilai**, bukan lewat file config — lihat [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md#sistem-penilaian) untuk detail aturan kalkulasinya.

---

## Default Users

Setelah `php artisan db:seed` (atau `migrate --seed`), gunakan akun berikut untuk login (`/login`):

| Role | Email | Password |
|---|---|---|
| **Administrator Sistem** | `sysadmin@alislam.sch.id` | `password` |
| **Mudir Pondok** | `mudir@alislam.sch.id` | `password` |
| **Wakil Kurikulum** | `kurikulum@alislam.sch.id` | `password` |
| **Tenaga Pendidik** | `guru@alislam.sch.id` | `password` |
| **Bagian Kesantrian** | `kesantrian@alislam.sch.id` | `password` |
| **Staf Admin** | `admin@alislam.sch.id` | `password` |

> ⚠️ **Wajib ganti password ini sebelum deployment produksi.** Sistem saat ini belum punya fitur ganti password mandiri oleh user (lihat [Known Limitations](#known-limitations)) — penggantian password untuk sekarang hanya bisa lewat menu Sysadmin → User Management.

---

## Project Structure

```
siak-kepondokan/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Staf Admin: Santri, Pendidik, PPDB, Surat, Data Master
│   │   ├── Kurikulum/      # Waka Kurikulum: Kelas, Penugasan, Nilai, Rapor, Jadwal (nonaktif)
│   │   ├── Guru/           # Tenaga Pendidik: Presensi, Nilai, Jurnal, Dashboard
│   │   ├── Kesantrian/     # Bagian Kesantrian: Asrama, Kamar, Pelanggaran, Kegiatan
│   │   ├── Sysadmin/       # Administrator Sistem: User & Role management
│   │   └── Auth/           # Autentikasi (login/logout)
│   ├── Models/             # Eloquent models (29 model)
│   ├── Services/           # Business logic: Penilaian, PresensiKbm, AiAdvisor, ActivityLog, Surat
│   └── Exports/            # Excel exporter (Santri, Nilai, Presensi)
├── config/
│   └── siak.php            # Konfigurasi akademik & AI
├── database/
│   ├── migrations/
│   └── seeders/            # RolesPermissionsSeeder, MasterDataSeeder, UsersSeeder
├── resources/
│   └── views/
│       ├── layouts/        # app.blade.php (theme + dark mode), sidebar-nav
│       ├── <modul>/        # 1 folder per modul (santri, nilai, presensi-kbm, ppdb, dst)
│       └── dashboards/     # Dashboard per role
├── routes/
│   └── web.php             # Seluruh route, dikelompokkan per role/prefix
└── docs/
    └── DEVELOPER_GUIDE.md  # Dokumentasi teknis untuk developer & AI agent
```

---

## Database Schema (Ringkas)

```
┌──────────┐     ┌────────────────┐     ┌───────────┐
│  users   │     │ penugasan_     │     │  santri   │
├──────────┤     │ mengajar       │     ├───────────┤
│ id       │◄────┤ guru_id        │     │ id        │
│ name     │     │ mata_pelajaran │     │ nis, nisn │
│ role     │     │ _id            │     │ kelas_id  │─┐
│ email    │     │ kelas_id       │     │ kamar_id  │ │
└────┬─────┘     │ tahun_ajaran_id│     └───────────┘ │
     │           └────────────────┘                   │
     │                                                 │
     ▼                                                 ▼
┌──────────┐     ┌────────────┐     ┌───────────┐   ┌───────┐
│ tenaga_  │     │  pertemuan │     │   nilai   │   │ kelas │
│ pendidik │     ├────────────┤     ├───────────┤   ├───────┤
├──────────┤     │ guru_id    │     │ santri_id │   │ nama  │
│ user_id  │     │ kelas_id   │     │ kelas_id  │   │ wali_ │
│ nip, nik │     │ mapel_id   │     │ mapel_id  │   │ kelas │
└──────────┘     │ tanggal    │     │ komponen_ │   └───────┘
                 └─────┬──────┘     │ nilai_id  │
                       │            │ slot      │◄── input ke-n
                       ▼            │ nilai     │    (mis. Tugas 1-4)
                 ┌────────────┐     └───────────┘
                 │ presensi_  │
                 │ kbm        │     ┌────────────┐
                 ├────────────┤     │ komponen_  │
                 │ pertemuan_ │     │ nilai      │
                 │ id         │     ├────────────┤
                 │ santri_id  │     │ kode       │
                 │ status     │     │ bobot      │
                 └────────────┘     │ maks_input │◄── batas input/komponen
                                    └────────────┘
```

Tabel lain: `kamar`, `asrama`, `pelanggaran`, `kategori_pelanggaran`, `prestasi`, `kegiatan_kesantrian`, `presensi_kegiatan`, `ppdb_periode`, `ppdb_pendaftar`, `template_surat`, `surat_keluar`, `ai_conversation_logs`, `activity_logs`, `jadwal_pelajaran` *(disimpan tapi tidak lagi jadi acuan input — lihat Known Limitations)*.

Diagram lengkap & penjelasan tiap kolom ada di [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md#skema-data).

---

## Artisan Commands

```bash
php artisan migrate              # Jalankan migrasi
php artisan migrate:fresh --seed # Reset total + isi data contoh
php artisan db:seed              # Isi data contoh saja
php artisan view:clear           # Bersihkan cache Blade (jalankan ini kalau ada error "View not found" setelah update)
php artisan optimize:clear       # Bersihkan semua cache (route, config, view)
```

---

## Testing

```bash
php artisan test
# atau
./vendor/bin/pest
```

> Struktur test (`tests/Feature`, `tests/Unit`) sudah ada dari scaffolding awal, tapi **belum ada test case spesifik untuk modul akademik** (Penugasan, Nilai, Presensi, dsb) per saat dokumen ini ditulis.

---

## Known Limitations

Bagian ini sengaja ditulis jujur & rinci — supaya developer atau AI agent lain yang lanjut kerjakan project ini tidak perlu menemukan ulang hal yang sama:

| Area | Status | Catatan |
|---|---|---|
| **Notifikasi Sistem (FR-11)** | ❌ Belum ada | Tabel `notifications` & trait `Notifiable` sudah ada, tapi belum ada satupun `Notification` class atau `->notify()` yang jalan |
| **Lupa Password / Edit Profil** | ❌ Belum ada | Route bawaan Laravel Breeze (`routes/auth.php`) tidak pernah di-*load*; tidak ada halaman edit profil/ganti password mandiri untuk user |
| **Jadwal Pelajaran (hari/jam)** | 🟡 Nonaktif sengaja | Digantikan **Penugasan Mengajar** (kelas+mapel tanpa hari/jam). Kode & tabel `jadwal_pelajaran` masih ada, menu disembunyikan dari sidebar |
| **Komponen Nilai: field Mata Pelajaran/Tipe/Deskripsi** | 🐞 Bug diketahui | Form-nya ada & tervalidasi, tapi kolom `mata_pelajaran_id`, `tipe`, `deskripsi` **tidak ada di tabel** `komponen_nilai` — nilainya selalu terbuang, komponen nilai sebenarnya bersifat **global** (bukan per mapel) |
| **Export Excel & Cetak PDF** | 🐞 Dependency hilang | `maatwebsite/excel` & `barryvdh/laravel-dompdf` dipakai di kode tapi tidak ada di `composer.json` — wajib install manual |
| **Data Prestasi PPDB** | 🟡 Sebagian | FR-16 (PPDB) sudah ada CRUD periode & pendaftar, belum ada test menyeluruh untuk alur end-to-end |

Riwayat perbaikan bug (dari sesi pengembangan sebelumnya) & keputusan desain lengkap ada di [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md#riwayat--catatan-perbaikan).

---

## Documentation

| Dokumen | Untuk Siapa | Isi |
|---|---|---|
| **README.md** (ini) | Siapa saja | Overview, instalasi, fitur |
| [`docs/DEVELOPER_GUIDE.md`](docs/DEVELOPER_GUIDE.md) | Developer & AI agent | Arsitektur, aturan bisnis detail, skema data lengkap, riwayat bug/keputusan desain |

---

## Contributing

1. Fork repository
2. Buat branch fitur (`git checkout -b fitur/nama-fitur`)
3. Commit perubahan (`git commit -m 'Tambah: nama fitur'`)
4. Push ke branch (`git push origin fitur/nama-fitur`)
5. Buka Pull Request

### Panduan Pengembangan
- Ikuti standar PSR-12
- Cek `docs/DEVELOPER_GUIDE.md` sebelum mengubah alur Penugasan/Nilai/Presensi — banyak keputusan desain di situ hasil diskusi eksplisit, bukan default framework
- Update dokumentasi kalau menambah/mengubah fitur inti

---

## License

Proyek internal untuk Pondok Pesantren Modern Al Islam.

---

## Acknowledgments

- [Laravel](https://laravel.com/) — PHP Framework
- [TailwindCSS](https://tailwindcss.com/) — Utility-first CSS framework
- [Groq](https://groq.com/) — AI inference untuk AI Advisor
- [Spatie](https://spatie.be/) — Laravel Permission package

---

<p align="center">
  Dibangun untuk Pondok Pesantren Modern Al Islam
</p>
