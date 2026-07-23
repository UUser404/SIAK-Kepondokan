<?php

namespace App\Observers;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SimaqPenilaianObserver
 * 
 * Observer untuk menangani event pada model SimaqPenilaian.
 * Mengotomasi update agregat data santri (total_nilai, juz_tercapai, dll).
 */
class SimaqPenilaianObserver
{
    /**
     * Hook: Ketika penilaian dibuat
     * Jika program = hafalan, update santri.simaq_juz_tercapai
     */
    public function created(SimaqPenilaian $penilaian): void
    {
        try {
            DB::transaction(function () use ($penilaian) {
                $this->recalculateSantriAggregate($penilaian->santri);

                // Jika program hafalan, update juz tercapai
                if ($penilaian->program === 'hafalan' && $penilaian->juz) {
                    $maxJuz = $penilaian->santri
                        ->simaqPenilaians()
                        ->where('program', 'hafalan')
                        ->max('juz');

                    $penilaian->santri->update(['simaq_juz_tercapai' => $maxJuz ?? 0]);
                }
            });
        } catch (\Exception $e) {
            Log::error('SimaqPenilaianObserver: Error on created', [
                'penilaian_id' => $penilaian->id,
                'error'        => $e->getMessage(),
            ]);
            // Don't re-throw - observer shouldn't block main operation
        }
    }

    /**
     * Hook: Ketika penilaian diupdate
     * Recalculate agregat data santri
     */
    public function updated(SimaqPenilaian $penilaian): void
    {
        try {
            DB::transaction(function () use ($penilaian) {
                $this->recalculateSantriAggregate($penilaian->santri);

                // Jika program hafalan dan juz berubah
                if ($penilaian->program === 'hafalan') {
                    $maxJuz = $penilaian->santri
                        ->simaqPenilaians()
                        ->where('program', 'hafalan')
                        ->max('juz');

                    $penilaian->santri->update(['simaq_juz_tercapai' => $maxJuz ?? 0]);
                }
            });
        } catch (\Exception $e) {
            Log::error('SimaqPenilaianObserver: Error on updated', [
                'penilaian_id' => $penilaian->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Hook: Ketika penilaian dihapus (soft delete)
     * Recalculate agregat data santri
     */
    public function deleted(SimaqPenilaian $penilaian): void
    {
        try {
            DB::transaction(function () use ($penilaian) {
                $this->recalculateSantriAggregate($penilaian->santri);

                // Jika program hafalan, recalc juz
                if ($penilaian->program === 'hafalan') {
                    $maxJuz = $penilaian->santri
                        ->simaqPenilaians()
                        ->where('program', 'hafalan')
                        ->max('juz');

                    $penilaian->santri->update(['simaq_juz_tercapai' => $maxJuz ?? 0]);
                }
            });
        } catch (\Exception $e) {
            Log::error('SimaqPenilaianObserver: Error on deleted', [
                'penilaian_id' => $penilaian->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Hook: Ketika penilaian di-restore (dari soft delete)
     * Recalculate agregat data santri
     */
    public function restored(SimaqPenilaian $penilaian): void
    {
        try {
            DB::transaction(function () use ($penilaian) {
                $this->recalculateSantriAggregate($penilaian->santri);

                if ($penilaian->program === 'hafalan') {
                    $maxJuz = $penilaian->santri
                        ->simaqPenilaians()
                        ->where('program', 'hafalan')
                        ->max('juz');

                    $penilaian->santri->update(['simaq_juz_tercapai' => $maxJuz ?? 0]);
                }
            });
        } catch (\Exception $e) {
            Log::error('SimaqPenilaianObserver: Error on restored', [
                'penilaian_id' => $penilaian->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * Recalculate semua agregat data santri
     * - total_nilai (rata-rata semua penilaian)
     * - total_bintang (rata-rata bintang)
     * - total_setoran (count penilaian harian)
     * - badge (dari total_setoran & bintang)
     */
    private function recalculateSantriAggregate(Santri $santri): void
    {
        $penilaians = $santri->simaqPenilaians()->get();

        if ($penilaians->isEmpty()) {
            // Jika tidak ada penilaian, reset semua nilai
            $santri->update([
                'simaq_total_nilai'    => null,
                'simaq_total_bintang'  => null,
                'simaq_total_setoran'  => 0,
                'simaq_badge'          => null,
            ]);
            return;
        }

        // Hitung agregat
        $totalNilai    = round($penilaians->avg('nilai_akhir'), 2);
        $totalBintang  = round($penilaians->avg('bintang'), 2);
        $totalSetoran  = $penilaians->where('jenis', 'setoran_harian')->count();

        // Hitung badge
        $badge = null;
        if ($totalSetoran >= 100 && $totalBintang >= 4) {
            $badge = 'Hafiz Muda';
        } elseif ($totalSetoran >= 30) {
            $badge = 'Penghafal Awal';
        }

        // Update santri
        $santri->update([
            'simaq_total_nilai'   => $totalNilai,
            'simaq_total_bintang' => $totalBintang,
            'simaq_total_setoran' => $totalSetoran,
            'simaq_badge'         => $badge,
        ]);

        Log::info('SimaqPenilaianObserver: Santri aggregate recalculated', [
            'santri_id'         => $santri->id,
            'total_nilai'       => $totalNilai,
            'total_bintang'     => $totalBintang,
            'total_setoran'     => $totalSetoran,
            'badge'             => $badge,
        ]);
    }
}
