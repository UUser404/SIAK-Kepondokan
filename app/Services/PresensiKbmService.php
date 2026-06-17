<?php
// ============================================================
// app/Services/PresensiKbmService.php
// ============================================================
namespace App\Services;

use App\Models\Santri;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Pertemuan;
use App\Models\PresensiKbm;
use Illuminate\Support\Collection;

class PresensiKbmService
{
    /**
     * Rekap kehadiran santri per mapel dalam satu kelas & TA
     * Return: [santri_id => ['hadir'=>n, 'sakit'=>n, 'izin'=>n, 'alpa'=>n, 'total'=>n, 'persen'=>n]]
     */
    public function getRekapKehadiranKelas(
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): Collection {
        $pertemuanIds = Pertemuan::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->whereBetween('tanggal', [
                $tahunAjaran->tanggal_mulai,
                $tahunAjaran->tanggal_selesai,
            ])
            ->pluck('id');

        if ($pertemuanIds->isEmpty()) {
            return collect();
        }

        $presensiAll = PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
            ->select('santri_id', 'status', \DB::raw('COUNT(*) as jumlah'))
            ->groupBy('santri_id', 'status')
            ->get();

        return $presensiAll->groupBy('santri_id')->map(function ($rows) use ($pertemuanIds) {
            $data = $rows->keyBy('status');
            $total = $pertemuanIds->count();
            $hadir = $data->get('hadir')?->jumlah ?? 0;

            return [
                'hadir'  => $hadir,
                'sakit'  => $data->get('sakit')?->jumlah ?? 0,
                'izin'   => $data->get('izin')?->jumlah ?? 0,
                'alpa'   => $data->get('alpa')?->jumlah ?? 0,
                'total'  => $total,
                'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                'tuntas' => $total > 0 && (($hadir / $total) * 100) >= config('siak.presensi.min_kehadiran', 75),
            ];
        });
    }

    /**
     * Santri dengan kehadiran di bawah threshold
     */
    public function getSantriRentanAbsen(
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran,
        int $threshold = 75
    ): Collection {
        $rekap = $this->getRekapKehadiranKelas($kelas, $mataPelajaran, $tahunAjaran);

        return $rekap->filter(fn($r) => $r['persen'] < $threshold)
            ->sortBy('persen');
    }

    /**
     * Total pertemuan yang sudah dilaksanakan satu guru di satu kelas-mapel
     */
    public function getTotalPertemuan(
        int $guruId,
        Kelas $kelas,
        MataPelajaran $mataPelajaran,
        TahunAjaran $tahunAjaran
    ): int {
        return Pertemuan::where('guru_id', $guruId)
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->whereBetween('tanggal', [
                $tahunAjaran->tanggal_mulai,
                $tahunAjaran->tanggal_selesai,
            ])
            ->count();
    }

    /**
     * Rekap kehadiran santri semua mapel (untuk profil santri)
     */
    public function getRekapKehadiranSantri(
        Santri $santri,
        TahunAjaran $tahunAjaran
    ): Collection {
        $presensiAll = PresensiKbm::where('santri_id', $santri->id)
            ->whereHas('pertemuan', fn($q) => $q->whereBetween('tanggal', [
                $tahunAjaran->tanggal_mulai,
                $tahunAjaran->tanggal_selesai,
            ]))
            ->with('pertemuan.mataPelajaran')
            ->get();

        return $presensiAll->groupBy(fn($p) => $p->pertemuan->mata_pelajaran_id)
            ->map(function ($rows) {
                $total = $rows->count();
                $hadir = $rows->where('status', 'hadir')->count();

                return [
                    'mata_pelajaran' => $rows->first()->pertemuan->mataPelajaran,
                    'total'  => $total,
                    'hadir'  => $hadir,
                    'sakit'  => $rows->where('status', 'sakit')->count(),
                    'izin'   => $rows->where('status', 'izin')->count(),
                    'alpa'   => $rows->where('status', 'alpa')->count(),
                    'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
                ];
            })->values();
    }
}
