# Ajuan Perubahan: Penambahan Modul Wali Kelas

## Latar Belakang

Saat ini sistem belum menyediakan menu khusus untuk wali kelas. Padahal kebutuhan bisnis mengharuskan wali kelas memiliki akses khusus untuk:

- menginput predikat siswa
- menginput nilai ekstrakurikuler
- mengekspor raport siswa di kelas yang menjadi tanggung jawabnya

Sistem saat ini sudah memiliki fondasi data untuk hal ini, yaitu kolom `wali_kelas_id` pada tabel kelas, tetapi belum dimanfaatkan sebagai fitur terpisah di UI maupun hak akses.

## Permasalahan

- Tidak ada menu khusus wali kelas di sidebar.
- Guru biasa dan guru yang juga wali kelas tidak dibedakan secara visual maupun fungsional.
- Fitur predikat, nilai ekstrakurikuler, dan ekspor raport belum terintegrasi ke alur wali kelas.

## Usulan Solusi

Gunakan pendekatan berikut:

1. Tidak membuat role baru untuk wali kelas.
2. Tetap memakai role `guru` untuk user.
3. Menentukan status wali kelas berdasarkan relasi `kelas.wali_kelas_id = user.id`.
4. Jika user adalah wali kelas, tampilkan menu-menu khusus wali kelas di sidebar.
5. Batasi akses fitur wali kelas berdasarkan kelas yang memang menjadi tanggung jawab user tersebut.

## Alur yang Diusulkan

### 1. Deteksi Wali Kelas

Sistem akan mendeteksi apakah seorang guru adalah wali kelas dengan memeriksa apakah ada kelas yang memiliki `wali_kelas_id` yang mengarah ke user tersebut.

### 2. Menu Sidebar

- Jika user bukan wali kelas: menu wali kelas tidak muncul.
- Jika user adalah wali kelas: muncul menu khusus seperti:
    - Dashboard Wali Kelas
    - Daftar Kelas Wali Kelas
    - Input Predikat
    - Input Nilai Ekstrakurikuler
    - Export Raport

### 3. Akses Fitur

Setiap fitur wali kelas hanya boleh diakses untuk kelas yang memang menjadi tanggung jawab user tersebut.

## Implementasi yang Disarankan

### Backend

- Tambahkan helper pada model user, misalnya `isWaliKelas()`.
- Tambahkan relasi `waliKelasKelas()` pada model `User`.
- Tambahkan controller baru, misalnya `WaliKelasController`.
- Tambahkan route khusus untuk modul wali kelas.
- Tambahkan guard akses agar user hanya bisa mengakses kelasnya sendiri.

### Frontend

- Modifikasi sidebar agar menu wali kelas tampil hanya jika `Auth::user()->isWaliKelas()`.
- Buat halaman daftar kelas wali kelas.
- Buat form input predikat dan nilai ekstrakurikuler.
- Tambahkan tombol ekspor raport per kelas.

## Data yang Dibutuhkan

### Data yang sudah ada

- `kelas.wali_kelas_id`
- `kelas.tahun_ajaran_id`
- `santri` dan relasinya ke kelas
- `nilai_akhir` untuk predikat akademik

### Data tambahan yang perlu dibuat

- model untuk nilai ekstrakurikuler
- tabel pendukung untuk catatan wali kelas, jika diperlukan

## Dampak

### Manfaat

- Fitur sesuai kebutuhan operasional sekolah/pondok.
- UI lebih relevan sesuai peran guru.
- Akses lebih aman dan lebih terarah.

### Risiko

- Perlu penambahan view dan controller baru.
- Perlu desain alur data untuk predikat dan nilai ekstrakurikuler.
- Perlu pengujian akses untuk memastikan wali kelas tidak bisa mengakses kelas orang lain.

## Rekomendasi Akhir

Gunakan pendekatan “role guru + status wali kelas berbasis relasi kelas” karena sudah sesuai dengan struktur sistem saat ini dan lebih efisien dibanding menambah role baru.
