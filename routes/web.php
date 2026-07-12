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
        // Catatan perbaikan: sebelumnya route ini "nilai/{kelas}/{mapel}" padahal controller & filter form
        // membaca kelas_id/mapel_id sebagai query string (GET), bukan route segment -> selalu error
        // "Missing required parameter [mapel]" saat diklik dari halaman index. Diperbaiki jadi path polos.
        Route::get('nilai/detail', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'show'])->name('nilai.show');
        Route::post('nilai/finalize', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'finalize'])->name('nilai.finalize');
        // FR-14: export nilai & presensi ke Excel (kelas_id & mapel_id dikirim sebagai query string)
        Route::get('nilai/export', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'exportNilai'])->name('nilai.export');
        Route::get('presensi/export', [\App\Http\Controllers\Kurikulum\NilaiController::class, 'exportPresensi'])->name('presensi.export');

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
        Route::get('presensi/create/{penugasan}', [\App\Http\Controllers\Guru\PresensiController::class, 'create'])->name('presensi.create');
        Route::post('presensi', [\App\Http\Controllers\Guru\PresensiController::class, 'store'])->name('presensi.store');
        Route::get('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'show'])->name('presensi.show');
        Route::put('presensi/{pertemuan}', [\App\Http\Controllers\Guru\PresensiController::class, 'update'])->name('presensi.update');

        // Nilai (hanya mapel sendiri)
        Route::get('nilai', [\App\Http\Controllers\Guru\NilaiController::class, 'index'])->name('nilai.index');
        Route::get('nilai/{kelas}/{mataPelajaran}', [\App\Http\Controllers\Guru\NilaiController::class, 'show'])->name('nilai.show');
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
        // Perbaikan: KamarController hanya punya method index()/store()/update() -- TIDAK ADA
        // create()/show()/edit()/destroy() standar. "Route::resource('kamar', ...)" sebelumnya
        // tetap mendaftarkan route utuh untuk method yang tidak ada itu (bisa 500 kalau diakses,
        // pola sama seperti bug ppdb yang sudah diperbaiki). Diganti eksplisit sesuai method nyata.
        Route::get('kamar', [\App\Http\Controllers\Kesantrian\KamarController::class, 'index'])->name('kamar.index');
        Route::post('kamar', [\App\Http\Controllers\Kesantrian\KamarController::class, 'store'])->name('kamar.store');
        Route::put('kamar/{kamar}', [\App\Http\Controllers\Kesantrian\KamarController::class, 'update'])->name('kamar.update');
        Route::post('kamar/{kamar}/tempatkan', [\App\Http\Controllers\Kesantrian\KamarController::class, 'tempatkan'])->name('kamar.tempatkan');
        // Perbaikan: route ini sebelumnya belum ada padahal KamarController::keluarkan() sudah ada
        // dan sudah dipanggil dari resources/views/asrama/show.blade.php -> selalu error "Route not found".
        Route::patch('kamar/{penempatan}/keluarkan', [\App\Http\Controllers\Kesantrian\KamarController::class, 'keluarkan'])->name('kamar.keluarkan');

        // Pelanggaran
        Route::resource('pelanggaran', \App\Http\Controllers\Kesantrian\PelanggaranController::class);
        Route::patch('pelanggaran/{pelanggaran}/selesai', [\App\Http\Controllers\Kesantrian\PelanggaranController::class, 'selesai'])->name('pelanggaran.selesai');

        // Prestasi
        Route::resource('prestasi', \App\Http\Controllers\Kesantrian\PrestasiController::class);

        // Rekap
        // Catatan perbaikan: sebelumnya kedua route ini mengarah ke RekapController@pelanggaran
        // dan RekapController@presensi, yang masing-masing me-return view('kesantrian.rekap.pelanggaran')
        // dan view('kesantrian.rekap.presensi') — TAPI kedua file view tsb TIDAK PERNAH ADA di project ini,
        // jadi keduanya pasti error "View not found" saat diakses. Diarahkan ke controller/view yang
        // sudah benar-benar ada dan berfungsi.
        Route::get('rekap/pelanggaran', [\App\Http\Controllers\Kesantrian\PelanggaranController::class, 'index'])->name('rekap.pelanggaran');
        Route::get('rekap/presensi', [\App\Http\Controllers\Kesantrian\PresensiKegiatanController::class, 'rekap'])->name('rekap.presensi');
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
        // Perbaikan: sebelumnya "Route::resource('ppdb', PpdbController::class)" -- padahal controller
        // TIDAK punya method create()/store()/show()/edit()/update()/destroy() standar (yang ada malah
        // createPeriode()/storePeriode()/aktifkanPeriode()/showPendaftar()/showDetail()). View yang
        // dipakai memanggil nama route 'admin.ppdb.create-periode', 'admin.ppdb.aktifkan',
        // 'admin.ppdb.pendaftar', 'admin.ppdb.detail' yang sebelumnya sama sekali tidak terdaftar
        // -> selalu "Route not found". Diganti route eksplisit yang cocok, sekaligus menghindari
        // resource route "hantu" yang bisa 500 (BadMethodCallException) kalau tidak sengaja diakses.
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
        // Perbaikan: route 'surat/template-konten' harus didaftarkan SEBELUM Route::resource('surat', ...),
        // supaya tidak "ketutupan" oleh route resource show ('surat/{surat}') yang polanya sama-sama
        // "surat/{sesuatu}" dan akan lebih dulu dicoba dicocokkan oleh Laravel.
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
