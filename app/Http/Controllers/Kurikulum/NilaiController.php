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
use App\Models\PenugasanMengajar;
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

        // Progress finalisasi per kelas.
        // PENTING: "total mapel" per kelas HARUS dari Penugasan Mengajar (mapel yang
        // benar-benar ditugaskan/diampu guru di kelas itu), BUKAN dari seluruh mapel
        // aktif sistem -- sebelumnya pakai $mapelList->count() global, jadi kelas
        // SMP dan SMA (yang mapel-nya beda) dianggap butuh jumlah mapel yang sama.
        // Progress juga dihitung per kombinasi santri x mapel (bukan per santri
        // doang) -- sebelumnya 1 mapel selesai untuk semua santri sudah dianggap
        // 100% "Lengkap" walau mapel lain belum tersentuh sama sekali.
        $progressMap = [];
        if ($ta) {
            $penugasanPerKelas = PenugasanMengajar::where('tahun_ajaran_id', $ta->id)
                ->select('kelas_id', 'mata_pelajaran_id')
                ->distinct()
                ->get()
                ->groupBy('kelas_id');

            foreach ($kelasList as $kelas) {
                $totalSantri = $kelas->jumlah_santri;
                $mapelIds    = $penugasanPerKelas->get($kelas->id, collect())
                    ->pluck('mata_pelajaran_id')->unique()->values();
                $totalMapel  = $mapelIds->count();

                $sudahFinal = $totalMapel > 0
                    ? NilaiAkhir::where('kelas_id', $kelas->id)
                    ->where('tahun_ajaran_id', $ta->id)
                    ->whereIn('mata_pelajaran_id', $mapelIds)
                    ->count()
                    : 0;

                $totalDiperlukan = $totalSantri * $totalMapel;

                $progressMap[$kelas->id] = [
                    'sudah'       => $sudahFinal,
                    'total'       => $totalDiperlukan,
                    'total_mapel' => $totalMapel,
                    'persen'      => $totalDiperlukan > 0 ? round(($sudahFinal / $totalDiperlukan) * 100) : 0,
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
        $kelas     = $kelasId ? Kelas::find($kelasId) : $kelasList->first();

        // Dropdown mapel HARUS di-scope ke yang benar ditugaskan (Penugasan Mengajar)
        // untuk kelas ini di TA aktif -- sebelumnya pakai MataPelajaran::where('is_active')
        // global (bug yang sama seperti di index(), lihat catatan di sana). Kalau kelas
        // ini belum ada penugasan sama sekali (mis. kelas baru, cuma ada data santri),
        // dropdown-nya kosong -- bukan nampilin semua mapel sistem.
        $mapelIds = collect();
        if ($kelas && $ta) {
            $mapelIds = PenugasanMengajar::where('kelas_id', $kelas->id)
                ->where('tahun_ajaran_id', $ta->id)
                ->distinct()
                ->pluck('mata_pelajaran_id');
        }
        $mapelList = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama')->get();

        // Fallback ke mapel pertama yang valid kalau mapel_id kosong ATAU mapel_id
        // itu bukan mapel yang ditugaskan ke kelas ini (mis. user baru ganti kelas
        // lewat dropdown, tapi mapel_id lama di form masih nyangkut dari kelas
        // sebelumnya dan kebetulan tidak valid untuk kelas yang baru dipilih).
        $mataPelajaran = ($mapelId && $mapelIds->contains($mapelId))
            ? MataPelajaran::find($mapelId)
            : $mapelList->first();

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

        // FIX: $results itu array (satu entri per santri), sebelumnya diselipkan
        // langsung ke string ("{$results} santri") -> PHP nge-print literal "Array"
        // (Array to string conversion), bukan angka. Sekarang pakai count().
        return back()->with(
            'success',
            "Nilai akhir {$kelas->nama} — {$mataPelajaran->nama} berhasil dikalkulasi (" . count($results) . " santri)."
        );
    }

    /**
     * Finalisasi SEMUA mata pelajaran aktif sekaligus untuk satu kelas -- supaya
     * Wakil Kurikulum tidak perlu klik satu-satu per mapel kalau memang mau
     * finalisasi kelas itu secara penuh.
     */
    public function finalizeKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        $ta    = TahunAjaran::aktif();
        $kelas = Kelas::findOrFail($request->kelas_id);
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        // Cuma finalisasi mapel yang benar ditugaskan ke kelas ini (via Penugasan
        // Mengajar), bukan semua mapel aktif sistem -- konsisten sama cara hitung
        // progress di index().
        $mapelIds = PenugasanMengajar::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->distinct()
            ->pluck('mata_pelajaran_id');
        $mapelList = MataPelajaran::whereIn('id', $mapelIds)->get();

        $totalSantri = 0;

        foreach ($mapelList as $mapel) {
            $results = $this->penilaianService->hitungNilaiAkhirBulk($kelas, $mapel, $ta);
            $totalSantri += count($results);
        }

        return back()->with(
            'success',
            "Nilai akhir {$kelas->nama} berhasil difinalisasi untuk {$mapelList->count()} mata pelajaran yang ditugaskan ({$totalSantri} baris nilai diproses)."
        );
    }

    /**
     * Finalisasi SEMUA kelas + SEMUA mata pelajaran aktif di tahun ajaran berjalan
     * sekaligus -- dipakai kalau Wakil Kurikulum memang mau "tutup buku" nilai
     * satu semester penuh dalam satu klik, bukannya kelas-per-kelas atau
     * mapel-per-mapel.
     */
    public function finalizeAll(Request $request)
    {
        $ta = TahunAjaran::aktif();
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $kelasList = Kelas::where('tahun_ajaran_id', $ta->id)->get();

        // Sama seperti finalizeKelas() -- per kelas, cuma mapel yang benar
        // ditugaskan (Penugasan Mengajar), bukan semua mapel aktif sistem.
        $penugasanPerKelas = PenugasanMengajar::where('tahun_ajaran_id', $ta->id)
            ->select('kelas_id', 'mata_pelajaran_id')
            ->distinct()
            ->get()
            ->groupBy('kelas_id');

        $totalSantri = 0;
        $totalKombinasi = 0;

        foreach ($kelasList as $kelas) {
            $mapelIds = $penugasanPerKelas->get($kelas->id, collect())
                ->pluck('mata_pelajaran_id')->unique();
            $mapelList = MataPelajaran::whereIn('id', $mapelIds)->get();

            foreach ($mapelList as $mapel) {
                $results = $this->penilaianService->hitungNilaiAkhirBulk($kelas, $mapel, $ta);
                $totalSantri += count($results);
                $totalKombinasi++;
            }
        }

        return back()->with(
            'success',
            "Finalisasi selesai untuk {$kelasList->count()} kelas ({$totalKombinasi} kombinasi kelas-mapel yang ditugaskan, {$totalSantri} baris nilai diproses)."
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
