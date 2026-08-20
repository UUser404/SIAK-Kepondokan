<?php
// ============================================================
// app/Http/Controllers/Guru/JurnalController.php
// ============================================================
namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PenugasanMengajar;
use App\Models\Pertemuan;
use App\Models\PresensiKbm;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurnalController extends Controller
{
    /**
     * Guard akses -- SENGAJA duplikat logic yang sama persis dengan
     * PresensiController::guruBolehAkses() (bukan di-share lewat trait/helper)
     * karena PresensiController::guruBolehAkses() itu private, dan project
     * ini belum punya pola shared-helper untuk guard semacam ini di modul
     * lain manapun (RaporController & LegerController juga masing-masing
     * punya method authorize-nya sendiri, bukan di-share). Kalau nanti mau
     * dirapikan jadi 1 trait/helper, JANGAN cuma rapikan yang ini -- rapikan
     * sekalian 3 tempat lain yang polanya sama (Rapor, Leger, Presensi).
     */
    private function guruBolehAkses(int $kelasId, int $mapelId): bool
    {
        if (Auth::user()->isManajemen()) return true;

        $ta = TahunAjaran::aktif();

        return PenugasanMengajar::where('guru_id', Auth::id())
            ->where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mapelId)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->exists();
    }

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

    /**
     * Detail 1 entri jurnal -- fokus ke topik/materi/catatan mengajar, BUKAN
     * tabel kehadiran lengkap seperti presensi.show. Kehadiran cuma
     * ditampilkan ringkas (jumlah H/S/I/A), sementara detail nama santri
     * cuma ditampilkan untuk yang TIDAK hadir (sakit/izin/alpa) -- santri
     * yang hadir tidak perlu disebut satu-satu, itu "kondisi normal".
     */
    public function show(Pertemuan $pertemuan)
    {
        abort_if(!$this->guruBolehAkses($pertemuan->kelas_id, $pertemuan->mata_pelajaran_id), 403);

        $pertemuan->load(['mataPelajaran', 'kelas', 'presensiKbm.santri', 'guru']);

        $rekap = [
            'hadir' => $pertemuan->presensiKbm->where('status', 'hadir')->count(),
            'sakit' => $pertemuan->presensiKbm->where('status', 'sakit')->count(),
            'izin'  => $pertemuan->presensiKbm->where('status', 'izin')->count(),
            'alpa'  => $pertemuan->presensiKbm->where('status', 'alpa')->count(),
        ];

        // Cuma santri yang TIDAK hadir -- ini yang perlu disebut namanya.
        // Diurutkan bertingkat: status dulu (alpa > izin > sakit, yang lebih
        // "serius" duluan), baru nama -- pakai sintaks multi-kriteria
        // sortBy([callback, callback]) Collection, BUKAN chain sortBy()
        // berkali-kali (itu saling menimpa, bukan gabung jadi 1 urutan).
        $urutanStatus = ['alpa' => 1, 'izin' => 2, 'sakit' => 3];
        $tidakHadir = $pertemuan->presensiKbm
            ->whereIn('status', ['sakit', 'izin', 'alpa'])
            ->sortBy([
                fn($a, $b) => ($urutanStatus[$a->status] ?? 9) <=> ($urutanStatus[$b->status] ?? 9),
                fn($a, $b) => $a->santri->nama_lengkap <=> $b->santri->nama_lengkap,
            ])
            ->values();

        return view('presensi-kbm.jurnal-show', compact('pertemuan', 'rekap', 'tidakHadir'));
    }
}
