<?php
// ============================================================
// app/Http/Controllers/Kurikulum/NilaiController.php
// ============================================================
namespace App\Http\Controllers\Kurikulum;

use App\Exports\NilaiExport;
use App\Exports\PresensiExport;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use App\Services\PresensiKbmService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NilaiController extends Controller
{
    public function __construct(
        private PenilaianService $penilaianService,
        private PresensiKbmService $presensiKbmService
    ) {}

    /**
     * Overview semua kelas & status nilai
     */
    public function index(Request $request)
    {
        $ta = TahunAjaran::aktif();

        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas', 'santri'])
            ->withCount('santri as jumlah_santri')
            ->get();

        $mapelList = MataPelajaran::where('is_active', true)->orderBy('nama')->get();

        // Progress finalisasi per kelas
        $progressMap = [];
        if ($ta) {
            foreach ($kelasList as $kelas) {
                $totalSantri = $kelas->jumlah_santri;
                $totalMapel  = $mapelList->count();
                $sudahFinal  = NilaiAkhir::where('kelas_id', $kelas->id)
                    ->where('tahun_ajaran_id', $ta->id)
                    ->distinct('santri_id')
                    ->count('santri_id');

                $progressMap[$kelas->id] = [
                    'sudah'  => $sudahFinal,
                    'total'  => $totalSantri,
                    'persen' => $totalSantri > 0 ? round(($sudahFinal / $totalSantri) * 100) : 0,
                ];
            }
        }

        return view('nilai.kurikulum-index', compact('kelasList', 'mapelList', 'progressMap', 'ta'));
    }

    /**
     * Detail nilai satu kelas + mapel
     */
    public function show(Request $request)
    {
        $ta            = TahunAjaran::aktif();
        $kelasId       = $request->kelas_id;
        $mapelId       = $request->mapel_id;

        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)->with('tingkatan')->get();
        $mapelList = MataPelajaran::where('is_active', true)->orderBy('nama')->get();

        $kelas        = $kelasId ? Kelas::find($kelasId) : $kelasList->first();
        $mataPelajaran = $mapelId ? MataPelajaran::find($mapelId) : $mapelList->first();

        $rekap = null;
        $statistik = null;
        if ($kelas && $mataPelajaran && $ta) {
            $rekap     = $this->penilaianService->getRekapNilaiKelas($kelas, $mataPelajaran, $ta);
            $statistik = $this->penilaianService->getStatistikKelas($kelas, $mataPelajaran, $ta);
        }

        return view('nilai.kurikulum-show', compact(
            'kelasList',
            'mapelList',
            'kelas',
            'mataPelajaran',
            'rekap',
            'statistik',
            'ta'
        ));
    }

    /**
     * Finalisasi nilai akhir satu kelas & mapel
     */
    public function finalize(Request $request)
    {
        $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
        ]);

        $ta            = TahunAjaran::aktif();
        $kelas         = Kelas::findOrFail($request->kelas_id);
        $mataPelajaran = MataPelajaran::findOrFail($request->mata_pelajaran_id);

        $results = $this->penilaianService->hitungNilaiAkhirBulk($kelas, $mataPelajaran, $ta);

        return back()->with(
            'success',
            "Nilai akhir {$kelas->nama} — {$mataPelajaran->nama} berhasil dikalkulasi ({$results} santri)."
        );
    }

    /**
     * FR-14: Export rekap nilai satu kelas & mapel ke Excel
     */
    public function exportNilai(Request $request)
    {
        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
        ]);

        $ta            = TahunAjaran::aktif();
        $kelas         = Kelas::findOrFail($request->kelas_id);
        $mataPelajaran = MataPelajaran::findOrFail($request->mapel_id);

        abort_if(! $ta, 422, 'Tidak ada tahun ajaran aktif.');

        return Excel::download(
            new NilaiExport($kelas, $mataPelajaran, $ta, $this->penilaianService),
            "nilai-{$kelas->nama}-{$mataPelajaran->nama}-" . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * FR-14: Export rekap presensi KBM satu kelas & mapel ke Excel
     */
    public function exportPresensi(Request $request)
    {
        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mata_pelajaran,id'],
        ]);

        $ta            = TahunAjaran::aktif();
        $kelas         = Kelas::findOrFail($request->kelas_id);
        $mataPelajaran = MataPelajaran::findOrFail($request->mapel_id);

        abort_if(! $ta, 422, 'Tidak ada tahun ajaran aktif.');

        return Excel::download(
            new PresensiExport($kelas, $mataPelajaran, $ta, $this->presensiKbmService),
            "presensi-{$kelas->nama}-{$mataPelajaran->nama}-" . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
