<?php
// ============================================================
// app/Http/Controllers/Mudir/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Mudir;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TenagaPendidik;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pelanggaran;
use App\Models\PenempatanKamar;
use App\Models\Kamar;
use App\Models\Prestasi;
use App\Models\NilaiAkhir;
use App\Models\PresensiKbm;
use App\Models\PpdbPendaftar;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    public function index()
    {
        $ta = TahunAjaran::aktif();

        $totalSantri    = Santri::aktif()->count();
        $totalPendidik  = TenagaPendidik::count();
        $totalKelas     = $ta ? Kelas::where('tahun_ajaran_id', $ta->id)->count() : 0;
        $totalMapel     = MataPelajaran::where('is_active', true)->count();

        // Rata-rata kehadiran KBM bulan ini
        $bulanIni       = now()->startOfMonth();
        $totalPresensi  = PresensiKbm::whereHas('pertemuan', fn($q) => $q->where('tanggal', '>=', $bulanIni))->count();
        $hadirPresensi  = PresensiKbm::whereHas('pertemuan', fn($q) => $q->where('tanggal', '>=', $bulanIni))
            ->where('status', 'hadir')->count();
        $rataTingkatKehadiran = $totalPresensi > 0
            ? round(($hadirPresensi / $totalPresensi) * 100, 1)
            : 0;

        // Rata-rata nilai akhir
        $rataRataNilai  = $ta
            ? round(NilaiAkhir::where('tahun_ajaran_id', $ta->id)->avg('nilai_akhir') ?? 0, 1)
            : 0;

        $pelanggaranAktif = Pelanggaran::where('status', 'aktif')
            ->whereMonth('tanggal', now()->month)->count();

        // Asrama
        $totalHunian         = PenempatanKamar::where('is_aktif', true)->count();
        $totalKapasitasKamar = Kamar::where('is_active', true)->sum('kapasitas');
        $totalPrestasi       = Prestasi::whereYear('tanggal', now()->year)->count();
        $totalPpdb           = PpdbPendaftar::where('status', 'menunggu')->count();

        return view('dashboards.mudir', compact(
            'totalSantri',
            'totalPendidik',
            'totalKelas',
            'totalMapel',
            'rataTingkatKehadiran',
            'rataRataNilai',
            'pelanggaranAktif',
            'totalHunian',
            'totalKapasitasKamar',
            'totalPrestasi',
            'totalPpdb'
        ));
    }
}
