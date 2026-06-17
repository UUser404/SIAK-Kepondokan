<?php
// ============================================================
// app/Http/Controllers/Kurikulum/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Santri;
use App\Models\NilaiAkhir;
use App\Models\Pertemuan;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        $ta = TahunAjaran::aktif();

        $totalKelas    = $ta ? Kelas::where('tahun_ajaran_id', $ta->id)->count() : 0;
        $totalSantri   = Santri::aktif()->count();

        // Kelas yang belum ada nilai finalisasi
        $nilaiFinalisasi = $ta
            ? NilaiAkhir::where('tahun_ajaran_id', $ta->id)->distinct('kelas_id')->count('kelas_id')
            : 0;
        $kelasBelumNilai = $totalKelas - $nilaiFinalisasi;

        // Pertemuan hari ini
        $pertemuanHariIni = Pertemuan::whereDate('tanggal', today())->count();

        // Rekap nilai per kelas (5 kelas terakhir diupdate)
        $rekapKelas = $ta ? Kelas::where('tahun_ajaran_id', $ta->id)
            ->withCount(['santri as jumlah_santri'])
            ->with('tingkatan', 'waliKelas')
            ->get() : collect();

        return view('dashboards.kurikulum', compact(
            'totalKelas',
            'totalSantri',
            'kelasBelumNilai',
            'pertemuanHariIni',
            'rekapKelas'
        ));
    }
}
