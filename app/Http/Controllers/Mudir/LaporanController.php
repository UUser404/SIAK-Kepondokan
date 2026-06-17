<?php
// ============================================================
// app/Http/Controllers/Mudir/LaporanController.php
// ============================================================
namespace App\Http\Controllers\Mudir;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiAkhir;
use App\Models\Pelanggaran;
use App\Models\PresensiKbm;
use App\Models\PresensiKegiatan;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\TenagaPendidik;
use App\Models\Prestasi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function akademik(Request $request)
    {
        $ta        = TahunAjaran::aktif();
        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas'])->withCount('santri as jumlah_santri')->get();
        $mapelList = MataPelajaran::where('is_active', true)->get();

        $nilaiPerKelas = [];
        foreach ($kelasList as $kelas) {
            $n = NilaiAkhir::where('kelas_id', $kelas->id)->where('tahun_ajaran_id', $ta?->id)->get();
            $nilaiPerKelas[$kelas->id] = [
                'rata_rata'     => round($n->avg('nilai_akhir') ?? 0, 1),
                'persen_tuntas' => $n->count() > 0
                    ? round(($n->where('tuntas', true)->count() / $n->count()) * 100, 1) : 0,
                'distribusi'    => ['A' => $n->where('predikat', 'A')->count(), 'B' => $n->where('predikat', 'B')->count(), 'C' => $n->where('predikat', 'C')->count(), 'D' => $n->where('predikat', 'D')->count()],
            ];
        }

        $nilaiPerMapel = [];
        foreach ($mapelList as $m) {
            $n = NilaiAkhir::where('mata_pelajaran_id', $m->id)->where('tahun_ajaran_id', $ta?->id)->get();
            $nilaiPerMapel[$m->id] = [
                'nama' => $m->nama,
                'kkm' => $m->kkm,
                'rata_rata'     => round($n->avg('nilai_akhir') ?? 0, 1),
                'persen_tuntas' => $n->count() > 0 ? round(($n->where('tuntas', true)->count() / $n->count()) * 100, 1) : 0,
            ];
        }

        $allNilai = NilaiAkhir::where('tahun_ajaran_id', $ta?->id)->get();
        $summary  = [
            'total_santri'         => Santri::aktif()->count(),
            'total_pendidik'       => TenagaPendidik::count(),
            'total_kelas'          => $kelasList->count(),
            'rata_nilai_global'    => round($allNilai->avg('nilai_akhir') ?? 0, 1),
            'persen_tuntas_global' => $allNilai->count() > 0
                ? round(($allNilai->where('tuntas', true)->count() / $allNilai->count()) * 100, 1) : 0,
        ];

        return view('laporan.akademik', compact('ta', 'kelasList', 'mapelList', 'nilaiPerKelas', 'nilaiPerMapel', 'summary'));
    }

    public function kesantrian(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $pelanggaranData = [
            'ringan' => Pelanggaran::whereHas('kategori', fn($q) => $q->where('tingkat', 'ringan'))->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count(),
            'sedang' => Pelanggaran::whereHas('kategori', fn($q) => $q->where('tingkat', 'sedang'))->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count(),
            'berat'  => Pelanggaran::whereHas('kategori', fn($q) => $q->where('tingkat', 'berat'))->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count(),
        ];

        $topPelanggaran = Santri::aktif()
            ->withCount([
                'pelanggaran as poin_total' => fn($q) =>
                $q->where('status', 'aktif')
                    ->join('kategori_pelanggaran', 'pelanggaran.kategori_pelanggaran_id', '=', 'kategori_pelanggaran.id')
                    ->selectRaw('SUM(kategori_pelanggaran.poin)')
            ])->orderByDesc('poin_total')->limit(5)->get();

        $prestasiData = [
            'total'         => Prestasi::whereYear('tanggal', $tahun)->count(),
            'nasional_plus' => Prestasi::whereIn('tingkat', ['nasional', 'internasional'])->whereYear('tanggal', $tahun)->count(),
        ];

        $hunianAsrama = \App\Models\Asrama::with(['kamar' => fn($q) =>
        $q->withCount(['penempatanAktif as penghuni'])])->get()
            ->map(fn($a) => [
                'nama'     => $a->nama,
                'jenis' => $a->jenis,
                'penghuni' => $a->kamar->sum('penghuni'),
                'kapasitas' => $a->kamar->sum('kapasitas'),
            ]);

        return view('laporan.kesantrian', compact('bulan', 'tahun', 'pelanggaranData', 'topPelanggaran', 'prestasiData', 'hunianAsrama'));
    }

    public function presensi(Request $request)
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);
        $ta    = TahunAjaran::aktif();

        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)->withCount('santri as jumlah_santri')->get();

        $presensiKbmPerKelas = [];
        foreach ($kelasList as $kelas) {
            $ids    = \App\Models\Pertemuan::where('kelas_id', $kelas->id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->pluck('id');
            $total  = PresensiKbm::whereIn('pertemuan_id', $ids)->count();
            $hadir  = PresensiKbm::whereIn('pertemuan_id', $ids)->where('status', 'hadir')->count();
            $presensiKbmPerKelas[$kelas->id] = [
                'kelas'  => $kelas->nama,
                'total' => $total,
                'hadir' => $hadir,
                'alpa'   => PresensiKbm::whereIn('pertemuan_id', $ids)->where('status', 'alpa')->count(),
                'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0,
            ];
        }

        $kegiatanList     = \App\Models\JenisKegiatan::where('is_active', true)->get();
        $presensiKegiatan = $kegiatanList->map(function ($k) use ($bulan, $tahun) {
            $total = PresensiKegiatan::where('jenis_kegiatan_id', $k->id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count();
            $hadir = PresensiKegiatan::where('jenis_kegiatan_id', $k->id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('status', 'hadir')->count();
            return ['nama' => $k->nama, 'hari' => PresensiKegiatan::where('jenis_kegiatan_id', $k->id)->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->distinct('tanggal')->count('tanggal'), 'persen' => $total > 0 ? round(($hadir / $total) * 100, 1) : 0];
        });

        $santriAlpa = PresensiKbm::select('santri_id', \DB::raw('COUNT(*) as total_alpa'))
            ->where('status', 'alpa')
            ->whereHas('pertemuan', fn($q) => $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun))
            ->groupBy('santri_id')->orderByDesc('total_alpa')->with('santri')->limit(10)->get();

        return view('laporan.presensi', compact('bulan', 'tahun', 'kelasList', 'presensiKbmPerKelas', 'presensiKegiatan', 'santriAlpa'));
    }
}
