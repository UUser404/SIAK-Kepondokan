<?php
// ============================================================
// app/Http/Controllers/Kurikulum/RaporController.php
// ============================================================
namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\NilaiAkhir;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;
use App\Models\PresensiKbm;
use App\Models\Pertemuan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RaporController extends Controller
{
    /**
     * Index — pilih kelas untuk lihat rapor
     */
    public function index(Request $request)
    {
        $ta        = TahunAjaran::aktif();
        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas'])
            ->withCount('santri as jumlah_santri')
            ->get();

        $kelasId  = $request->kelas_id;
        $kelas    = $kelasId ? Kelas::with(['tingkatan', 'waliKelas'])->find($kelasId) : null;
        $santriList = null;

        if ($kelas && $ta) {
            $santriList = $kelas->santri()
                ->with(['nilaiAkhir' => fn($q) => $q->where('tahun_ajaran_id', $ta->id)])
                ->orderBy('nama_lengkap')
                ->get()
                ->map(function ($santri) use ($ta) {
                    $santri->total_mapel  = $santri->nilaiAkhir->count();
                    $santri->rata_nilai   = round($santri->nilaiAkhir->avg('nilai_akhir') ?? 0, 1);
                    $santri->mapel_tuntas = $santri->nilaiAkhir->where('tuntas', true)->count();
                    return $santri;
                });
        }

        return view('rapor.index', compact('kelasList', 'kelas', 'santriList', 'ta'));
    }

    /**
     * Show rapor satu santri
     */
    public function show(Santri $santri)
    {
        $ta = TahunAjaran::aktif();

        $nilaiAkhir = NilaiAkhir::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $ta?->id)
            ->with('mataPelajaran')
            ->get()
            ->sortBy('mataPelajaran.nama');

        // Hitung kehadiran KBM per mapel
        $kehadiranMapel = [];
        foreach ($nilaiAkhir as $na) {
            $pertemuanIds = Pertemuan::where('kelas_id', $na->kelas_id)
                ->where('mata_pelajaran_id', $na->mata_pelajaran_id)
                ->pluck('id');

            $total = $pertemuanIds->count();
            $hadir = PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
                ->where('santri_id', $santri->id)
                ->where('status', 'hadir')
                ->count();

            $kehadiranMapel[$na->mata_pelajaran_id] = [
                'total'  => $total,
                'hadir'  => $hadir,
                'sakit'  => PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
                    ->where('santri_id', $santri->id)
                    ->where('status', 'sakit')->count(),
                'izin'   => PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
                    ->where('santri_id', $santri->id)
                    ->where('status', 'izin')->count(),
                'alpa'   => PresensiKbm::whereIn('pertemuan_id', $pertemuanIds)
                    ->where('santri_id', $santri->id)
                    ->where('status', 'alpa')->count(),
            ];
        }

        $santri->load(['santriKelas.kelas.tingkatan', 'penempatanKamar.kamar.asrama']);
        $kelasAktif = $santri->santriKelas->where('status', 'aktif')->first()?->kelas;

        $rataRata   = round($nilaiAkhir->avg('nilai_akhir') ?? 0, 1);
        $totalTuntas = $nilaiAkhir->where('tuntas', true)->count();
        $totalMapel  = $nilaiAkhir->count();

        return view('rapor.show', compact(
            'santri',
            'nilaiAkhir',
            'kehadiranMapel',
            'kelasAktif',
            'rataRata',
            'totalTuntas',
            'totalMapel',
            'ta'
        ));
    }

    /**
     * Cetak rapor PDF
     */
    public function cetak(Santri $santri)
    {
        $ta = TahunAjaran::aktif();

        $nilaiAkhir = NilaiAkhir::where('santri_id', $santri->id)
            ->where('tahun_ajaran_id', $ta?->id)
            ->with('mataPelajaran')
            ->get()
            ->sortBy('mataPelajaran.nama');

        $santri->load(['santriKelas.kelas.tingkatan']);
        $kelasAktif = $santri->santriKelas->where('status', 'aktif')->first()?->kelas;

        $pdf = Pdf::loadView('rapor.cetak-pdf', compact(
            'santri',
            'nilaiAkhir',
            'kelasAktif',
            'ta'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("rapor-{$santri->nis}-{$ta?->nama}.pdf");
    }
}
