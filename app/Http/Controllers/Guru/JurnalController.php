<?php
// ============================================================
// app/Http/Controllers/Guru/JurnalController.php
// ============================================================
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Pertemuan;
use App\Models\PresensiKbm;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurnalController extends Controller
{
    /**
     * Jurnal mengajar — rekap semua pertemuan guru ini per mapel/kelas
     */
    public function index(Request $request)
    {
        $user   = Auth::user();
        $ta     = TahunAjaran::aktif();
        $bulan  = $request->get('bulan', now()->month);
        $tahun  = $request->get('tahun', now()->year);

        // Semua pertemuan guru ini bulan ini
        $pertemuanList = Pertemuan::where('guru_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with(['mataPelajaran', 'kelas', 'presensiKbm'])
            ->orderByDesc('tanggal')
            ->get();

        // Rekap per kelas-mapel
        $rekapPerKelas = $pertemuanList->groupBy(fn($p) => $p->kelas_id . '_' . $p->mata_pelajaran_id)
            ->map(function ($pertemuan) {
                $totalSantri   = $pertemuan->first()->presensiKbm->count();
                $totalHadir    = $pertemuan->sum(fn($p) => $p->presensiKbm->where('status', 'hadir')->count());
                $totalPresensi = $pertemuan->sum(fn($p) => $p->presensiKbm->count());

                return [
                    'kelas'         => $pertemuan->first()->kelas,
                    'mata_pelajaran' => $pertemuan->first()->mataPelajaran,
                    'jumlah_pertemuan' => $pertemuan->count(),
                    'rata_kehadiran' => $totalPresensi > 0
                        ? round(($totalHadir / $totalPresensi) * 100, 1) : 0,
                ];
            })->values();

        // Statistik bulan ini
        $totalPertemuan = $pertemuanList->count();
        $totalSantriHadir = $pertemuanList->sum(fn($p) => $p->presensiKbm->where('status', 'hadir')->count());
        $totalSantriAlpa  = $pertemuanList->sum(fn($p) => $p->presensiKbm->where('status', 'alpa')->count());

        return view('presensi-kbm.jurnal', compact(
            'pertemuanList',
            'rekapPerKelas',
            'totalPertemuan',
            'totalSantriHadir',
            'totalSantriAlpa',
            'bulan',
            'tahun'
        ));
    }
}
