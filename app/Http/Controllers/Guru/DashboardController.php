<?php
// ============================================================
// app/Http/Controllers/Guru/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PenugasanMengajar;
use App\Models\Pertemuan;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        // Kelas & mapel yang ditugaskan Kurikulum ke guru ini
        $penugasanList = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['mataPelajaran', 'kelas'])
            ->get();

        // Tandai kelas-mapel yang sudah/belum diinput presensinya HARI INI
        $penugasanList->each(function ($penugasan) {
            $penugasan->sudahPresensi = Pertemuan::where('guru_id', $penugasan->guru_id)
                ->where('kelas_id', $penugasan->kelas_id)
                ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
                ->whereDate('tanggal', today())
                ->exists();
        });

        $totalMapel = $penugasanList->pluck('mata_pelajaran_id')->unique()->count();
        $totalKelas = $penugasanList->pluck('kelas_id')->unique()->count();

        $pertemuanBulanIni = Pertemuan::where('guru_id', $user->id)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        // Nilai yang belum diinput (santri di kelas tanpa nilai UTS/UAS)
        $nilaiPending = 0; // Kalkulasi ringan untuk dashboard

        return view('dashboards.guru', compact(
            'penugasanList',
            'totalMapel',
            'totalKelas',
            'pertemuanBulanIni',
            'nilaiPending'
        ));
    }
}
