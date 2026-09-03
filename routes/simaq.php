<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimaqController;

/**
 * SIMAQ Routes - Sistem Penilaian Al-Quran
 * Prefix: /simaq
 */
Route::middleware(['auth', 'verified', 'role:guru_tahsin_tahfizh|admin|super_admin'])->prefix('simaq')->name('simaq.')->group(function () {
    
    // 0. Dashboard SIMAQ
    Route::get('/', [SimaqController::class, 'dashboard'])->name('dashboard');
    
    // 1. Modul Setoran Harian 
    Route::get('/harian', [SimaqController::class, 'listSantri'])->name('harian.index');
    
    // 2. Modul Ujian Pemantapan (Segera Hadir)
    Route::get('/pemantapan', function() { return "Halaman Ujian Pemantapan (Segera Hadir)"; })->name('pemantapan.index');
    
    // 3. Modul Imtihan Tasmi (Segera Hadir)
    Route::get('/tasmi', function() { return "Halaman Imtihan Tasmi' (Segera Hadir)"; })->name('tasmi.index');
    
    // 4. Modul Jamiyyatul Huffazh (Segera Hadir)
    Route::get('/huffazh', function() { return "Halaman Jamiyyatul Huffazh (Segera Hadir)"; })->name('huffazh.index');

    // Modul Laporan & Cetak Rapor SIMAQ
    Route::get('/laporan', [SimaqController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/cetak/{id}', [SimaqController::class, 'cetakRapor'])->name('laporan.cetak');

    // Modul CRUD Penilaian (Menempel pada Santri)
    Route::get('/santri/{id}', [SimaqController::class, 'detailSantri'])->name('detail');
    Route::get('/santri/{id}/nilai', [SimaqController::class, 'createPenilaian'])->name('create');
    Route::post('/nilai', [SimaqController::class, 'storePenilaian'])->name('store');
    Route::delete('/nilai/{id}', [SimaqController::class, 'destroyPenilaian'])->name('destroy');
});