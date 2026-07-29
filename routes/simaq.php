<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SimaqController;

/**
 * SIMAQ Routes - Sistem Penilaian Al-Quran
 * 
 * Prefix: /simaq
 * Middleware: auth, verified, EnsureGuruSimaq
 */
Route::middleware(['auth', 'verified', 'role:guru_simaq|admin|super_admin'])->prefix('simaq')->name('simaq.')->group(function () {
    
    // Dashboard SIMAQ
    Route::get('/', [SimaqController::class, 'dashboard'])->name('dashboard');
    
    // List santri dengan penilaian
    Route::get('santri', [SimaqController::class, 'listSantri'])->name('santri.list');
    
    // Detail santri & history penilaian
    Route::get('santri/{santri}', [SimaqController::class, 'detailSantri'])->name('santri.detail');
    
    // CRUD Penilaian
    Route::post('penilaian', [SimaqController::class, 'storePenilaian'])->name('penilaian.store');
    Route::patch('penilaian/{penilaian}', [SimaqController::class, 'updatePenilaian'])->name('penilaian.update');
    Route::delete('penilaian/{penilaian}', [SimaqController::class, 'destroyPenilaian'])->name('penilaian.destroy');
});
