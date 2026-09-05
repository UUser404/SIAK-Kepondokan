<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Simaq\PemantapanController;
use App\Http\Controllers\Simaq\TasmiController;
use App\Http\Controllers\Simaq\HuffazhController;
// ============================================================
// Guest routes
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// ============================================================
// Authenticated routes
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard redirect berdasarkan role
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // MUDIR PONDOK
    // ==========================================
    Route::prefix('mudir')->name('mudir.')->middleware('role:mudir|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Mudir\DashboardController::class, 'index'])->name('dashboard');
        Route::get('laporan/akademik', [\App\Http\Controllers\Mudir\LaporanController::class, 'akademik'])->name('laporan.akademik');
        Route::get('laporan/kesantrian', [\App\Http\Controllers\Mudir\LaporanController::class, 'kesantrian'])->name('laporan.kesantrian');
        Route::get('laporan/presensi', [\App\Http\Controllers\Mudir\LaporanController::class, 'presensi'])->name('laporan.presensi');
    });

    // ==========================================
    // WAKIL KURIKULUM
    // ==========================================
    Route::prefix('kurikulum')->name('kurikulum.')->middleware('role:wakil_kurikulum|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Kurikulum\DashboardController::class, 'index'])->name('dashboard');

        // Kelas & Jadwal
        Route::resource('kelas', \App\Http\Controllers\Kurikulum\KelasController::class)
            ->parameters(['kelas' => 'kelas']);
        // Catatan: fitur Jadwal Pelajaran (hari/jam) untuk sementara TIDAK dipakai
        // sebagai syarat guru input presensi/nilai/jurnal (digantikan oleh Penugasan
        // Mengajar di bawah). Route & controller-nya tetap ada, cuma menu di sidebar
        // disembunyikan dulu.
        Route::resource('jadwal', \App\Http\Controllers\Kurikulum\JadwalController::class);

        // Penugasan Mengajar: guru + mapel + kelas (tanpa jadwal hari/jam)
        Route::get('penugasan', [\App\Http\Controllers\Kurikulum\PenugasanController::class, 'index'])->name('penugasan.index');
        Route::get('penugasan/{guru}', [\App\Http\Controllers\Kurikulum\PenugasanController::class, 'show'])->name('penugasan.show');
        Route::post('penugasan/{guru}', [\App\Http\Controllers\Kurikulum\PenugasanController::class, 'store'])->name('penugasan.store');
        Route::delete('penugasan-hapus/{penugasan}', [\App\Http\Controllers\Kurikulum\PenugasanController::class, 'destroy'])->name('penugasan.destroy');

        // Penilaian
        Route::get('nilai', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/detail', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'show'])->name('nilai.show');
        Route::post('nilai/finalize', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'finalize'])->name('nilai.finalize');
        Route::post('nilai/finalize-kelas', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'finalizeKelas'])->name('nilai.finalize-kelas');
        Route::post('nilai/finalize-semua', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'finalizeAll'])->name('nilai.finalize-all');
        Route::get('nilai/export', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'exportNilai'])->name('nilai.export');
        Route::get('presensi/export', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'exportPresensi'])->name('presensi.export');

        // Rapor
        Route::get('rapor', [\App\Http\Controllers\Kurikulum\RaporController::class, 'index'])->name('rapor.index');
        Route::get('rapor/{santri}', [\App\Http\Controllers\Kurikulum\RaporController::class, 'show'])->name('rapor.show');
        Route::get('rapor/{santri}/cetak', [\App\Http\Controllers\Kurikulum\RaporController::class, 'cetak'])->name('rapor.cetak');
        Route::get('rapor/{santri}/cetak-arab', [\App\Http\Controllers\Kurikulum\RaporController::class, 'cetakArab'])->name('rapor.cetak-arab');

        // Leger Nilai
        Route::get('leger-nilai', [\App\Http\Controllers\Kurikulum\LegerController::class, 'index'])->name('leger-nilai.index');
        Route::get('leger-nilai/{kelas}', [\App\Http\Controllers\Kurikulum\LegerController::class, 'show'])->name('leger-nilai.show');
        Route::get('leger-nilai/{kelas}/cetak', [\App\Http\Controllers\Kurikulum\LegerController::class, 'cetak'])->name('leger-nilai.cetak');
        Route::get('leger-nilai/{kelas}/export', [\App\Http\Controllers\Kurikulum\LegerController::class, 'export'])->name('leger-nilai.export');

        // AI Advisor
        Route::get('ai-advisor', [\App\Http\Controllers\AiAdvisorController::class, 'index'])->name('ai.index');
        Route::post('ai-advisor/chat', [\App\Http\Controllers\AiAdvisorController::class, 'chat'])->name('ai.chat');
    });

    // ==========================================
    // GURU
    // ==========================================
    Route::prefix('guru')->name('guru.')->middleware('role:guru|wakil_kurikulum|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');

        // Presensi KBM
        Route::get('presensi', [\App\Http\Controllers\Guru\PresensiController::class, 'index'])->name('presensi.index');
        Route::get('presensi/create/{penugasan}', [\App\Http\Controllers\Guru\PresensiController::class, 'create'])->name('presensi.create');
        Route::post('presensi', [\App\Http\Controllers\Guru\PresensiController::class, 'store'])->name('presensi.store');
        Route::get('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'show'])->name('presensi.show');
        Route::get('presensi/{pertemuan}/edit', [\App\Http\Controllers\Guru\PresensiController::class, 'edit'])->name('presensi.edit');
        Route::put('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'update'])->name('presensi.update');
        Route::delete('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'destroy'])->name('presensi.destroy');

        // Nilai (hanya mapel sendiri)
        Route::get('nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/{kelas}/{mataPelajaran}', [\App\Http\Controllers\Guru\NilaiController::class, 'show'])->name('nilai.show');
        Route::post('nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'store'])->name('nilai.store');
        Route::put('nilai/{nilai}', [\App\Http\Controllers\Guru\NilaiController::class, 'update'])->name('nilai.update');
        Route::post('nilai/bulk', [\App\Http\Controllers\Guru\NilaiController::class, 'bulkStore'])->name('nilai.bulk');

        // Jurnal Mengajar
        Route::get('jurnal', [\App\Http\Controllers\Guru\JurnalController::class, 'index'])->name('jurnal.index');
        Route::get('jurnal/{pertemuan}', [\App\Http\Controllers\Guru\JurnalController::class, 'show'])->name('jurnal.show');

        // AI Advisor
        Route::get('ai-advisor', [\App\Http\Controllers\AiAdvisorController::class, 'index'])->name('ai.index');
        Route::post('ai-advisor/chat', [\App\Http\Controllers\AiAdvisorController::class, 'chat'])->name('ai.chat');
    });

    // ==========================================
    // WALI KELAS (status berbasis relasi kelas.wali_kelas_id, bukan role baru
    // -- lihat docs/DEVELOPER_GUIDE.md untuk konteks keputusan ini)
    // ==========================================
    Route::prefix('wali-kelas')->name('wali-kelas.')->middleware('role:guru')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\WaliKelas\DashboardController::class, 'index'])->name('dashboard');

        // Predikat Sikap
        Route::get('predikat-sikap/{kelas}', [\App\Http\Controllers\WaliKelas\PredikatSikapController::class, 'index'])->name('predikat-sikap.index');
        Route::post('predikat-sikap/{kelas}', [\App\Http\Controllers\WaliKelas\PredikatSikapController::class, 'store'])->name('predikat-sikap.store');

        // Nilai Ekstrakurikuler
        Route::get('nilai-ekstrakurikuler/{kelas}', [\App\Http\Controllers\WaliKelas\NilaiEkstrakurikulerController::class, 'index'])->name('nilai-ekstrakurikuler.index');
        Route::post('nilai-ekstrakurikuler/{kelas}', [\App\Http\Controllers\WaliKelas\NilaiEkstrakurikulerController::class, 'store'])->name('nilai-ekstrakurikuler.store');

        // Rapor -- reuse Kurikulum\RaporController, guard akses ada di controller itu sendiri
        Route::get('rapor', [\App\Http\Controllers\Kurikulum\RaporController::class, 'index'])->name('rapor.index');
        Route::get('rapor/{santri}', [\App\Http\Controllers\Kurikulum\RaporController::class, 'show'])->name('rapor.show');
        Route::get('rapor/{santri}/cetak', [\App\Http\Controllers\Kurikulum\RaporController::class, 'cetak'])->name('rapor.cetak');
        Route::get('rapor/{santri}/cetak-arab', [\App\Http\Controllers\Kurikulum\RaporController::class, 'cetakArab'])->name('rapor.cetak-arab');

        // Leger Nilai
        Route::get('leger-nilai', [\App\Http\Controllers\Kurikulum\LegerController::class, 'index'])->name('leger-nilai.index');
        Route::get('leger-nilai/{kelas}', [\App\Http\Controllers\Kurikulum\LegerController::class, 'show'])->name('leger-nilai.show');
        Route::get('leger-nilai/{kelas}/cetak', [\App\Http\Controllers\Kurikulum\LegerController::class, 'cetak'])->name('leger-nilai.cetak');
        Route::get('leger-nilai/{kelas}/export', [\App\Http\Controllers\Kurikulum\LegerController::class, 'export'])->name('leger-nilai.export');
    });

    // ==========================================
    // KESANTRIAN
    // ==========================================
    Route::prefix('kesantrian')->name('kesantrian.')->middleware('role:kesantrian|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Kesantrian\DashboardController::class, 'index'])->name('dashboard');

        // Presensi Kegiatan Harian
        Route::get('presensi', [\App\Http\Controllers\Kesantrian\PresensiKegiatanController::class, 'index'])->name('presensi.index');
        Route::post('presensi', [\App\Http\Controllers\Kesantrian\PresensiKegiatanController::class, 'store'])->name('presensi.store');
        Route::get('presensi/{tanggal}/{kegiatan}', [\App\Http\Controllers\Kesantrian\PresensiKegiatanController::class, 'show'])->name('presensi.show');

        // Asrama & Kamar
        Route::resource('asrama', \App\Http\Controllers\Kesantrian\AsramaController::class);
        Route::get('kamar', [\App\Http\Controllers\Kesantrian\KamarController::class, 'index'])->name('kamar.index');
        Route::post('kamar', [\App\Http\Controllers\Kesantrian\KamarController::class, 'store'])->name('kamar.store');
        Route::put('kamar/{kamar}', [\App\Http\Controllers\Kesantrian\KamarController::class, 'update'])->name('kamar.update');
        Route::post('kamar/{kamar}/tempatkan', [\App\Http\Controllers\Kesantrian\KamarController::class, 'tempatkan'])->name('kamar.tempatkan');
        Route::patch('kamar/{penempatan}/keluarkan', [\App\Http\Controllers\Kesantrian\KamarController::class, 'keluarkan'])->name('kamar.keluarkan');

        // Pelanggaran
        Route::resource('pelanggaran', \App\Http\Controllers\Kesantrian\PelanggaranController::class);
        Route::patch('pelanggaran/{pelanggaran}/selesai', [\App\Http\Controllers\Kesantrian\PelanggaranController::class, 'selesai'])->name('pelanggaran.selesai');

        // Prestasi
        Route::resource('prestasi', \App\Http\Controllers\Kesantrian\PrestasiController::class);

        // Rekap
        Route::get('rekap/pelanggaran', [\App\Http\Controllers\Kesantrian\PelanggaranController::class, 'index'])->name('rekap.pelanggaran');
        Route::get('rekap/presensi', [\App\Http\Controllers\Kesantrian\PresensiKegiatanController::class, 'rekap'])->name('rekap.presensi');
    });

    // ==========================================
    // ADMIN / STAF
    // ==========================================
    Route::prefix('admin')->name('admin.')->middleware('role:admin|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Data Santri
        // PENTING: route literal (export, import-*, {santri}/profil) HARUS didaftarkan
        // SEBELUM Route::resource() -- resource bikin GET santri/{santri} (wildcard
        // 1-segmen) yang kalau didaftarkan duluan akan "menangkap" request ke
        // santri/export, santri/import-bulk, dst (dianggap {santri}="export" dst),
        // gagal ketemu model, jadi 404. Ini pernah jadi bug nyata -- jangan diubah
        // urutannya lagi tanpa alasan kuat.
        Route::get('santri/export', [\App\Http\Controllers\Admin\SantriController::class, 'export'])->name('santri.export');
        Route::get('santri/import-template', [\App\Http\Controllers\Admin\SantriController::class, 'importTemplate'])->name('santri.import-template');
        Route::get('santri/import-bulk', [\App\Http\Controllers\Admin\SantriController::class, 'importBulk'])->name('santri.import-bulk');
        Route::post('santri/import-bulk/preview', [\App\Http\Controllers\Admin\SantriController::class, 'previewBulk'])->name('santri.import-bulk.preview');
        Route::post('santri/import-bulk/store', [\App\Http\Controllers\Admin\SantriController::class, 'storeBulk'])->name('santri.import-bulk.store');

        // PERBAIKAN: Import Santri Baru (CREATE massal) dibatasi khusus
        // sysadmin -- beda dari Import Massal di atas (UPDATE kelas/asrama
        // santri yang sudah ada) yang tetap boleh diakses role admin biasa.
        // Middleware role:sysadmin di sini MENAMBAH syarat di atas
        // role:admin|sysadmin yang sudah berlaku untuk seluruh grup 'admin'
        // (bukan menggantikannya) -- hasil akhirnya cuma sysadmin yang lolos
        // ke 4 route ini, walau admin biasa tetap lolos middleware grup luar.
        // Tetap didaftarkan SEBELUM Route::resource('santri', ...) dengan
        // alasan sama seperti route literal lain di atas.
        Route::middleware('role:sysadmin')->group(function () {
            Route::get('santri/import-baru', [\App\Http\Controllers\Admin\SantriController::class, 'importBaru'])->name('santri.import-baru');
            Route::get('santri/import-baru-template', [\App\Http\Controllers\Admin\SantriController::class, 'importBaruTemplate'])->name('santri.import-baru-template');
            Route::post('santri/import-baru/preview', [\App\Http\Controllers\Admin\SantriController::class, 'previewBaru'])->name('santri.import-baru.preview');
            Route::post('santri/import-baru/store', [\App\Http\Controllers\Admin\SantriController::class, 'storeBaru'])->name('santri.import-baru.store');
        });

        Route::get('santri/{santri}/profil', [\App\Http\Controllers\Admin\SantriController::class, 'profil'])->name('santri.profil');
        Route::resource('santri', \App\Http\Controllers\Admin\SantriController::class);

        // Tenaga Pendidik
        Route::resource('pendidik', \App\Http\Controllers\Admin\PendidikController::class);

        // Master Data
        Route::resource('tahun-ajaran', \App\Http\Controllers\Admin\TahunAjaranController::class);
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');
        Route::resource('tingkatan', \App\Http\Controllers\Admin\TingkatanController::class)->except(['show']);
        Route::resource('kategori-mata-pelajaran', \App\Http\Controllers\Admin\KategoriMataPelajaranController::class)->except(['show']);
        Route::resource('mata-pelajaran', \App\Http\Controllers\Admin\MataPelajaranController::class);
        Route::get('kkm', [\App\Http\Controllers\Admin\KkmController::class, 'index'])->name('kkm.index');
        Route::post('kkm', [\App\Http\Controllers\Admin\KkmController::class, 'store'])->name('kkm.store');
        Route::resource('ekstrakurikuler', \App\Http\Controllers\Admin\EkstrakurikulerController::class);
        Route::resource('komponen-nilai', \App\Http\Controllers\Admin\KomponenNilaiController::class);

        // PPDB
        Route::get('ppdb', [\App\Http\Controllers\Admin\PpdbController::class, 'index'])->name('ppdb.index');
        Route::get('ppdb/create-periode', [\App\Http\Controllers\Admin\PpdbController::class, 'createPeriode'])->name('ppdb.create-periode');
        Route::post('ppdb/periode', [\App\Http\Controllers\Admin\PpdbController::class, 'storePeriode'])->name('ppdb.store-periode');
        Route::post('ppdb/{periode}/aktifkan', [\App\Http\Controllers\Admin\PpdbController::class, 'aktifkanPeriode'])->name('ppdb.aktifkan');
        Route::get('ppdb/pendaftar/{periode?}', [\App\Http\Controllers\Admin\PpdbController::class, 'showPendaftar'])->name('ppdb.pendaftar');
        Route::get('ppdb/pendaftar/{pendaftar}/detail', [\App\Http\Controllers\Admin\PpdbController::class, 'showDetail'])->name('ppdb.detail');
        Route::post('ppdb/{pendaftar}/verifikasi', [\App\Http\Controllers\Admin\PpdbController::class, 'verifikasi'])->name('ppdb.verifikasi');
        Route::post('ppdb/{pendaftar}/terima', [\App\Http\Controllers\Admin\PpdbController::class, 'terima'])->name('ppdb.terima');
        Route::post('ppdb/{pendaftar}/tolak', [\App\Http\Controllers\Admin\PpdbController::class, 'tolak'])->name('ppdb.tolak');
        Route::post('ppdb/{pendaftar}/konversi', [\App\Http\Controllers\Admin\PpdbController::class, 'konversiKeSantri'])->name('ppdb.konversi');

        // Surat
        Route::get('surat/template-konten', [\App\Http\Controllers\Admin\SuratController::class, 'getKontenTemplate'])->name('surat.template-konten');
        Route::resource('surat', \App\Http\Controllers\Admin\SuratController::class);
        Route::resource('template-surat', \App\Http\Controllers\Admin\TemplateSuratController::class);
        Route::get('surat/{surat}/cetak', [\App\Http\Controllers\Admin\SuratController::class, 'cetak'])->name('surat.cetak');
        Route::post('surat/{surat}/terbitkan', [\App\Http\Controllers\Admin\SuratController::class, 'terbitkan'])->name('surat.terbitkan');
    });

    // ==========================================
    // SYSADMIN
    // ==========================================
    Route::prefix('sysadmin')->name('sysadmin.')->middleware('role:sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Sysadmin\DashboardController::class, 'index'])->name('dashboard');

        // Manajemen User
        Route::resource('users', \App\Http\Controllers\Sysadmin\UserController::class);
        Route::post('users/{user}/toggle-aktif', [\App\Http\Controllers\Sysadmin\UserController::class, 'toggleAktif'])->name('users.toggle');
        Route::post('users/{user}/reset-password', [\App\Http\Controllers\Sysadmin\UserController::class, 'resetPassword'])->name('users.reset-password');

        // Activity Log
        Route::get('activity-log', [\App\Http\Controllers\Sysadmin\ActivityLogController::class, 'index'])->name('activity-log');

        // AI Log
        Route::get('ai-log', [\App\Http\Controllers\Sysadmin\AiLogController::class, 'index'])->name('ai-log');
    });

    // ==========================================
    // SIMAQ - Sistem Penilaian Al-Quran
    // ==========================================
    require __DIR__ . '/simaq.php';
});

// PPDB Form Publik (tanpa auth)
Route::prefix('ppdb')->name('ppdb.public.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Public\PpdbPublicController::class, 'index'])->name('index');
    Route::get('daftar', [\App\Http\Controllers\Public\PpdbPublicController::class, 'create'])->name('create');
    Route::post('daftar', [\App\Http\Controllers\Public\PpdbPublicController::class, 'store'])->name('store');
    Route::get('cek/{nomor_daftar}', [\App\Http\Controllers\Public\PpdbPublicController::class, 'cekStatus'])->name('cek');
});

// Rute Khusus Halaman Login SIMAQ
Route::middleware('guest')->get('/simaq/login', function () {
    session(['url.intended' => route('simaq.dashboard')]);

    return view('simaq.login');
})->name('simaq.login');

use App\Http\Controllers\SimaqController;

Route::middleware(['auth', 'role:guru_tahsin_tahfizh|admin|super_admin'])->prefix('simaq')->name('simaq.')->group(function () {
    Route::delete('/nilai/{id}', [App\Http\Controllers\SimaqController::class, 'destroy'])->name('destroy');
});
