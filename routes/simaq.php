<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimaqController;

// Wajib ditambahkan di file ini agar rute mengenali lokasi kontroler
use App\Http\Controllers\Simaq\PemantapanController;
use App\Http\Controllers\Simaq\TasmiController;
use App\Http\Controllers\Simaq\HuffazhController;

/**
 * SIMAQ Routes - Sistem Penilaian Al-Quran
 * Prefix: /simaq
 */
Route::middleware(['auth', 'verified', 'role:guru_tahsin_tahfizh|admin|super_admin'])->prefix('simaq')->name('simaq.')->group(function () {
    
    // 0. Dashboard SIMAQ
    Route::get('/', [SimaqController::class, 'dashboard'])->name('dashboard');
    
    // 1. Modul Setoran Harian 
    Route::get('/harian', [SimaqController::class, 'listSantri'])->name('harian.index');
    
    // 2. Modul Ujian Pemantapan (10 Pertanyaan Sambung Ayat)
    Route::prefix('pemantapan')->name('pemantapan.')->group(function () {
        Route::get('/', [PemantapanController::class, 'index'])->name('index');
        Route::get('/nilai/{santri_id}', [PemantapanController::class, 'create'])->name('create');
        Route::post('/nilai', [PemantapanController::class, 'store'])->name('store');
    });
    
    // 3. Modul Imtihan Tasmi' (Setoran 1 Juz Full)
    Route::prefix('tasmi')->name('tasmi.')->group(function () {
        Route::get('/', [TasmiController::class, 'index'])->name('index');
        Route::get('/nilai/{santri_id}', [TasmiController::class, 'create'])->name('create');
        Route::post('/nilai', [TasmiController::class, 'store'])->name('store');
    });
    
    // 4. Modul Jam'iyyatul Huffazh (Program Khusus & Absensi)
    Route::prefix('huffazh')->name('huffazh.')->group(function () {
        Route::get('/', [HuffazhController::class, 'index'])->name('index');
        
        // Presensi 2x Sepekan
        Route::get('/presensi', [HuffazhController::class, 'presensiIndex'])->name('presensi.index');
        Route::post('/presensi', [HuffazhController::class, 'presensiStore'])->name('presensi.store');
        
        // Imtihan Tasmi' Khusus Member Huffazh
        Route::get('/tasmi/{santri_id}', [HuffazhController::class, 'tasmiCreate'])->name('tasmi.create');
        Route::post('/tasmi', [HuffazhController::class, 'tasmiStore'])->name('tasmi.store');
    });

    // Modul Laporan & Cetak Rapor SIMAQ
    Route::get('/laporan', [SimaqController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/cetak/{id}', [SimaqController::class, 'cetakRapor'])->name('laporan.cetak');

    // Modul CRUD Penilaian (Menempel pada Santri)
    Route::get('/santri/{id}', [SimaqController::class, 'detailSantri'])->name('detail');
    Route::get('/santri/{id}/nilai', [SimaqController::class, 'createPenilaian'])->name('create');
    Route::post('/nilai', [SimaqController::class, 'storePenilaian'])->name('store');
    Route::delete('/nilai/{id}', [SimaqController::class, 'destroyPenilaian'])->name('destroy');
});