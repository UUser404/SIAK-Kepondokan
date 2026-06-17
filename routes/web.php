<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;

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
        Route::resource('kelas', \App\Http\Controllers\Kurikulum\KelasController::class);
        Route::resource('jadwal', \App\Http\Controllers\Kurikulum\JadwalController::class);

        // Penilaian
        Route::get('nilai', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/{kelas}/{mapel}', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'show'])->name('nilai.show');
        Route::post('nilai/finalize', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'finalize'])->name('nilai.finalize');

        // Rapor
        Route::get('rapor', [\App\Http\Controllers\Kurikulum\RaporController::class, 'index'])->name('rapor.index');
        Route::get('rapor/{santri}', [\App\Http\Controllers\Kurikulum\RaporController::class, 'show'])->name('rapor.show');
        Route::get('rapor/{santri}/cetak', [\App\Http\Controllers\Kurikulum\RaporController::class, 'cetak'])->name('rapor.cetak');

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
        Route::get('presensi/create/{jadwal}', [\App\Http\Controllers\Guru\PresensiController::class, 'create'])->name('presensi.create');
        Route::post('presensi', [\App\Http\Controllers\Guru\PresensiController::class, 'store'])->name('presensi.store');
        Route::get('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'show'])->name('presensi.show');
        Route::put('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'update'])->name('presensi.update');

        // Nilai (hanya mapel sendiri)
        Route::get('nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/{kelas}/{mapel}', [\App\Http\Controllers\Guru\NilaiController::class, 'show'])->name('nilai.show');
        Route::post('nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'store'])->name('nilai.store');
        Route::put('nilai/{nilai}', [\App\Http\Controllers\Guru\NilaiController::class, 'update'])->name('nilai.update');
        Route::post('nilai/bulk', [\App\Http\Controllers\Guru\NilaiController::class, 'bulkStore'])->name('nilai.bulk');

        // Jurnal Mengajar
        Route::get('jurnal', [\App\Http\Controllers\Guru\JurnalController::class, 'index'])->name('jurnal.index');

        // AI Advisor
        Route::get('ai-advisor', [\App\Http\Controllers\AiAdvisorController::class, 'index'])->name('ai.index');
        Route::post('ai-advisor/chat', [\App\Http\Controllers\AiAdvisorController::class, 'chat'])->name('ai.chat');
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
        Route::resource('kamar', \App\Http\Controllers\Kesantrian\KamarController::class);
        Route::post('kamar/{kamar}/tempatkan', [\App\Http\Controllers\Kesantrian\KamarController::class, 'tempatkan'])->name('kamar.tempatkan');

        // Pelanggaran
        Route::resource('pelanggaran', \App\Http\Controllers\Kesantrian\PelanggaranController::class);
        Route::patch('pelanggaran/{pelanggaran}/selesai', [\App\Http\Controllers\Kesantrian\PelanggaranController::class, 'selesai'])->name('pelanggaran.selesai');

        // Prestasi
        Route::resource('prestasi', \App\Http\Controllers\Kesantrian\PrestasiController::class);

        // Rekap
        Route::get('rekap/pelanggaran', [\App\Http\Controllers\Kesantrian\RekapController::class, 'pelanggaran'])->name('rekap.pelanggaran');
        Route::get('rekap/presensi', [\App\Http\Controllers\Kesantrian\RekapController::class, 'presensi'])->name('rekap.presensi');
    });

    // ==========================================
    // ADMIN / STAF
    // ==========================================
    Route::prefix('admin')->name('admin.')->middleware('role:admin|sysadmin')->group(function () {
        Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Data Santri
        Route::resource('santri', \App\Http\Controllers\Admin\SantriController::class);
        Route::get('santri/export', [\App\Http\Controllers\Admin\SantriController::class, 'export'])->name('santri.export');
        Route::get('santri/{santri}/profil', [\App\Http\Controllers\Admin\SantriController::class, 'profil'])->name('santri.profil');

        // Tenaga Pendidik
        Route::resource('pendidik', \App\Http\Controllers\Admin\PendidikController::class);

        // Master Data
        Route::resource('tahun-ajaran', \App\Http\Controllers\Admin\TahunAjaranController::class);
        Route::post('tahun-ajaran/{tahunAjaran}/aktifkan', [\App\Http\Controllers\Admin\TahunAjaranController::class, 'aktifkan'])->name('tahun-ajaran.aktifkan');
        Route::resource('mata-pelajaran', \App\Http\Controllers\Admin\MataPelajaranController::class);
        Route::resource('komponen-nilai', \App\Http\Controllers\Admin\KomponenNilaiController::class);

        // PPDB
        Route::resource('ppdb', \App\Http\Controllers\Admin\PpdbController::class);
        Route::post('ppdb/{pendaftar}/verifikasi', [\App\Http\Controllers\Admin\PpdbController::class, 'verifikasi'])->name('ppdb.verifikasi');
        Route::post('ppdb/{pendaftar}/terima', [\App\Http\Controllers\Admin\PpdbController::class, 'terima'])->name('ppdb.terima');
        Route::post('ppdb/{pendaftar}/tolak', [\App\Http\Controllers\Admin\PpdbController::class, 'tolak'])->name('ppdb.tolak');
        Route::post('ppdb/{pendaftar}/konversi', [\App\Http\Controllers\Admin\PpdbController::class, 'konversiKeSantri'])->name('ppdb.konversi');

        // Surat
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
    // PPDB PUBLIK (tanpa auth, untuk calon santri)
    // ==========================================
});

// PPDB Form Publik (tanpa auth)
Route::prefix('ppdb')->name('ppdb.public.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Public\PpdbPublicController::class, 'index'])->name('index');
    Route::get('daftar', [\App\Http\Controllers\Public\PpdbPublicController::class, 'create'])->name('create');
    Route::post('daftar', [\App\Http\Controllers\Public\PpdbPublicController::class, 'store'])->name('store');
    Route::get('cek/{nomor_daftar}', [\App\Http\Controllers\Public\PpdbPublicController::class, 'cekStatus'])->name('cek');
});
