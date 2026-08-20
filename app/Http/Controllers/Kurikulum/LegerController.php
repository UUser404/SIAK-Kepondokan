<?php
// ============================================================
// app/Http/Controllers/Kurikulum/LegerController.php
// Leger Nilai (Daftar Kumpulan Nilai Semester) -- 1 halaman per kelas,
// semua santri x semua mapel yang ditugaskan ke kelas itu. Dipakai
// bersama oleh Kurikulum & WaliKelas (pola sama persis seperti
// RaporController -- lihat komentar di routes/web.php & rapor/index.blade.php).
// ============================================================
namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Exports\LegerNilaiExport;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Mpdf;

class LegerController extends Controller
{
    public function __construct(private PenilaianService $penilaianService) {}

    /**
     * Guard akses: Kurikulum/Sysadmin bebas akses semua kelas. Wali Kelas
     * (guru) cuma boleh akses kelas yang dia wali-i sendiri, di tahun ajaran
     * aktif -- pola sama persis dengan RaporController::authorizeSantri(),
     * cuma unit yang dicek kelas langsung, bukan lewat santri.
     */
    private function authorizeKelas(Kelas $kelas, ?TahunAjaran $ta): void
    {
        $user = Auth::user();
        if ($user->role !== 'guru') return; // Kurikulum/Sysadmin: tidak dibatasi

        abort_if(
            !$ta || $kelas->wali_kelas_id !== $user->id || $kelas->tahun_ajaran_id !== $ta->id,
            403,
            'Anda bukan wali kelas untuk kelas ini di tahun ajaran aktif.'
        );
    }

    /**
     * Index -- pilih kelas untuk lihat leger
     */
    public function index(Request $request)
    {
        $ta   = TahunAjaran::aktif();
        $user = Auth::user();

        $kelasQuery = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas'])
            ->withCount('santri as jumlah_santri');

        // Wali Kelas (guru) cuma lihat kelas yang dia wali-i sendiri
        if ($user->role === 'guru') {
            $kelasQuery->where('wali_kelas_id', $user->id);
        }

        $kelasList = $kelasQuery->orderBy('nama')->get();

        // Sama seperti RaporController::index() -- wali kelas yang cuma
        // pegang 1 kelas langsung diarahkan ke show() kelas itu, skip
        // halaman pilih kelas. Kurikulum/Sysadmin tidak diberi shortcut ini.
        if ($user->role === 'guru' && $kelasList->count() === 1) {
            return redirect()->route('wali-kelas.leger-nilai.show', $kelasList->first());
        }

        return view('leger-nilai.index', compact('kelasList', 'ta'));
    }

    /**
     * Show -- tampilan web (matrix nilai 1 kelas penuh)
     */
    public function show(Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorizeKelas($kelas, $ta);
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $kelas->load(['tingkatan', 'waliKelas']);
        $data = $this->penilaianService->getLegerKelas($kelas, $ta);

        return view('leger-nilai.show', compact('kelas', 'data', 'ta'));
    }

    /**
     * Cetak PDF -- landscape, lebar (banyak kolom mapel)
     */
    public function cetak(Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorizeKelas($kelas, $ta);
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $kelas->load(['tingkatan', 'waliKelas']);
        $data = $this->penilaianService->getLegerKelas($kelas, $ta);

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            // A3 Landscape -- leger bisa punya banyak kolom mapel + kolom
            // kehadiran & kepribadian, A4 biasa kemungkinan besar kepotong.
            // Kalau kelasnya sedikit mapel & muat di A4-L, ganti manual di
            // sini, tapi A3-L lebih aman sebagai default.
            'format'        => 'A3-L',
            'orientation'   => 'L',
            'default_font'  => 'dejavusans',
            'margin_left'   => 8,
            'margin_right'  => 8,
            'margin_top'    => 10,
            'margin_bottom' => 10,
        ]);

        // Aktifkan Arabic shaping supaya kolom Nama Arab tersambung hurufnya
        // (tanpa ini, huruf Arab render terpisah-pisah per karakter, tidak
        // nyambung). HARUS di-set SEBELUM WriteHTML(), bukan sesudah --
        // shaping diterapkan saat HTML diproses. SENGAJA tidak set
        // SetDirectionality('rtl') seperti di RaporController::cetakArab()
        // -- leger ini tabelnya predominan LTR (angka, nama latin), cuma 1
        // kolom yang Arab. Kalau seluruh halaman dipaksa RTL, urutan kolom
        // tabel malah kebalik semua.
        $mpdf->autoArabic = true;

        $html = view('leger-nilai.cetak-pdf', compact('kelas', 'data', 'ta'))->render();
        $mpdf->WriteHTML($html);

        $namaKelas = str_replace(['/', '\\', ' '], '_', $kelas->nama);
        $namaFile  = "LEGER_{$namaKelas}.pdf";

        return $mpdf->Output($namaFile, 'D');
    }

    /**
     * Export Excel -- lewat Laravel Excel (paket sudah ada di project,
     * lihat App\Exports\NilaiExport.php / PresensiExport.php untuk pola
     * yang sudah dipakai di modul lain).
     */
    public function export(Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorizeKelas($kelas, $ta);
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $kelas->load(['tingkatan', 'waliKelas']);
        $data = $this->penilaianService->getLegerKelas($kelas, $ta);

        $namaKelas = str_replace(['/', '\\', ' '], '_', $kelas->nama);
        $namaFile  = "LEGER_{$namaKelas}.xlsx";

        return Excel::download(new LegerNilaiExport($kelas, $data, $ta), $namaFile);
    }
}
