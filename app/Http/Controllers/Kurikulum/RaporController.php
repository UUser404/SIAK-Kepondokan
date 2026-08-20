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
use Mpdf\Mpdf;
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

        // Wali kelas (guru) yang cuma pegang 1 kelas -- skip halaman pilih
        // kelas, langsung redirect ke kelas itu (mirip alasan sidebar
        // "Dashboard Wali Kelas" disembunyikan kalau cuma 1 kelas). Cek
        // !$request->filled('kelas_id') supaya tidak infinite redirect saat
        // request ini sendiri sudah punya kelas_id (hasil redirect barusan).
        // Kurikulum/Sysadmin TIDAK diberi shortcut ini -- mereka harus selalu
        // lihat daftar kelas, walau kebetulan cuma ada 1 kelas di sekolah.
        if ($user->role === 'guru' && $kelasList->count() === 1 && !$request->filled('kelas_id')) {
            return redirect()->route('wali-kelas.rapor.index', ['kelas_id' => $kelasList->first()->id]);
        }

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

        // KKM per tingkatan (bukan lagi kolom mata_pelajaran.kkm global) --
        // dihitung sekali di sini untuk semua baris nilai, mengikuti pola yang
        // sama persis dengan RaporArabService::rakit(). Ditempel sebagai
        // atribut sementara (tidak disimpan ke DB) di tiap objek NilaiAkhir
        // supaya blade tinggal baca $na->kkm_tingkatan.
        $tingkatanId = $kelasAktif?->tingkatan_id;
        foreach ($nilaiAkhir as $na) {
            $na->kkm_tingkatan = $na->mataPelajaran->kkmUntukTingkatan($tingkatanId);
        }

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
     * Cetak rapor PDF (Rapor Biasa/Latin)
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

        // Sama seperti di show() -- lihat catatan di sana.
        $tingkatanId = $kelasAktif?->tingkatan_id;
        foreach ($nilaiAkhir as $na) {
            $na->kkm_tingkatan = $na->mataPelajaran->kkmUntukTingkatan($tingkatanId);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'dejavusans',
        ]);

        $html = view('rapor.cetak-pdf', compact(
            'santri',
            'nilaiAkhir',
            'kelasAktif',
            'ta'
        ))->render();

        $mpdf->WriteHTML($html);

        // Nama file: KELAS_NAMA_SISWA.pdf
        $namaKelas = $kelasAktif?->nama ?? 'tanpa-kelas';
        $namaSantri = str_replace(['/', '\\', ' '], '_', $santri->nama_lengkap);
        $namaFile = "{$namaKelas}_{$namaSantri}.pdf";

        return $mpdf->Output($namaFile, 'D');
    }

    /**
     * Cetak Rapor Arab (format "كشف الدرجة" 2 halaman)
     * Menggunakan mPDF dengan autoArabic = true untuk support Arabic shaping.
     * CATATAN: header/footer TIDAK pakai mekanisme native mPDF lagi —
     * blade sudah menghardcode ulang blok data murid di tiap halaman,
     * jadi margin di sini disamakan dengan @page di blade (15mm semua sisi).
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

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'margin_top' => 34,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_header' => 8,
            'margin_footer' => 8,
        ]);

        // Aktifkan Arabic shaping
        $mpdf->autoArabic = true;
        $mpdf->SetDirectionality('rtl');

        $html = view('rapor.cetak-arab-pdf', compact('data'))->render();
        $mpdf->WriteHTML($html);

        $namaKelas = $kelasAktif->nama ?? 'tanpa-kelas';
        $namaSantri = str_replace(['/', '\\', ' '], '_', $santri->nama_lengkap);
        $namaFile = "{$namaKelas}_RAPOR_SYARI_{$namaSantri}.pdf";

        return $mpdf->Output($namaFile, 'D');
    }
}
