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
use App\Services\RaporArabService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RaporController extends Controller
{
    public function __construct(private RaporArabService $raporArabService) {}

    /**
     * Guard akses: Wakil Kurikulum/Sysadmin bebas akses semua kelas/santri.
     * Kalau yang akses seorang guru (Wali Kelas), batasi hanya untuk santri
     * yang kelas aktifnya benar dia wali-i, di tahun ajaran aktif.
     */
    private function authorizeSantri(Santri $santri, ?TahunAjaran $ta): void
    {
        $user = Auth::user();
        if ($user->role !== 'guru') return; // Kurikulum/Sysadmin: tidak dibatasi

        $kelasAktif = $santri->santriKelas->firstWhere('status', 'aktif')?->kelas
            ?? $santri->kelasAktif;

        abort_if(
            !$ta || !$kelasAktif || $kelasAktif->wali_kelas_id !== $user->id || $kelasAktif->tahun_ajaran_id !== $ta->id,
            403,
            'Anda bukan wali kelas untuk santri ini di tahun ajaran aktif.'
        );
    }

    /**
     * Index — pilih kelas untuk lihat rapor
     */
    public function index(Request $request)
    {
        $ta        = TahunAjaran::aktif();
        $user      = Auth::user();

        $kelasQuery = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas'])
            ->withCount('santri as jumlah_santri');

        // Wali Kelas (guru) cuma lihat kelas yang dia wali-i sendiri
        if ($user->role === 'guru') {
            $kelasQuery->where('wali_kelas_id', $user->id);
        }

        $kelasList = $kelasQuery->get();

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
        $this->authorizeSantri($santri, $ta);

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
        $this->authorizeSantri($santri, $ta);

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

        $namaFileTa = str_replace(['/', '\\'], '-', $ta?->nama ?? 'ta');

        return $pdf->download("rapor-{$santri->nis}-{$namaFileTa}.pdf");
    }

    /**
     * Rapor Arab (2 halaman, format KMI) -- BELUM DITEST render Arabnya di
     * DomPDF (barryvdh/laravel-dompdf biasa). Kalau huruf Arab keluar
     * terputus-putus/tidak tersambung, itu keterbatasan DomPDF standar --
     * lihat catatan di DEVELOPER_GUIDE.md soal opsi pindah ke omaralalwi/gpdf.
     */
    public function cetakArab(Santri $santri)
    {
        $ta = TahunAjaran::aktif();
        $this->authorizeSantri($santri, $ta);
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $santri->load(['santriKelas.kelas.tingkatan']);
        $kelasAktif = $santri->santriKelas->where('status', 'aktif')->first()?->kelas;
        abort_if(!$kelasAktif, 422, 'Santri ini belum punya penempatan kelas aktif.');

        $data = $this->raporArabService->rakit($santri, $kelasAktif, $ta);

        $pdf = Pdf::loadView('rapor.cetak-arab-pdf', compact('data'))
            ->setPaper('a4', 'portrait');

        $namaFileTa = str_replace(['/', '\\'], '-', $ta->nama);

        return $pdf->download("rapor-arab-{$santri->nis}-{$namaFileTa}.pdf");
    }
}
