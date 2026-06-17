<?php
// ============================================================
// app/Http/Controllers/Kesantrian/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\Pelanggaran;
use App\Models\PresensiKegiatan;
use App\Models\Kamar;
use App\Models\JenisKegiatan;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSantri = Santri::aktif()->count();

        // Persentase presensi kegiatan hari ini (ambil kegiatan pertama/subuh)
        $kegiatanHariIni = JenisKegiatan::where('is_active', true)->first();
        $presensiHariIni = 0;
        if ($kegiatanHariIni && $totalSantri > 0) {
            $hadir = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatanHariIni->id)
                ->whereDate('tanggal', today())
                ->where('status', 'hadir')
                ->count();
            $presensiHariIni = round(($hadir / $totalSantri) * 100);
        }

        $pelanggaranAktif = Pelanggaran::where('status', 'aktif')->count();

        $kamarTersedia = Kamar::where('is_active', true)->get()
            ->filter(fn($k) => $k->sisa_kapasitas > 0)
            ->count();

        $pelanggaranTerbaru = Pelanggaran::with(['santri', 'kategori'])
            ->where('status', 'aktif')
            ->orderByDesc('tanggal')
            ->limit(5)
            ->get();

        return view('dashboards.kesantrian', compact(
            'totalSantri',
            'presensiHariIni',
            'pelanggaranAktif',
            'kamarTersedia',
            'pelanggaranTerbaru'
        ));
    }
}
