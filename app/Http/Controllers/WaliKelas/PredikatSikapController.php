<?php
// ============================================================
// app/Http/Controllers/WaliKelas/PredikatSikapController.php
// ============================================================
namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\PredikatSikap;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PredikatSikapController extends Controller
{
    public function __construct(private PenilaianService $penilaianService) {}

    /**
     * Pastikan kelas ini benar tanggung jawab wali kelas yang login,
     * di tahun ajaran AKTIF (lingkup wali kelas sengaja dibatasi per tahun ajaran).
     */
    private function authorize_(Kelas $kelas, ?TahunAjaran $ta): void
    {
        abort_if(
            !$ta || $kelas->wali_kelas_id !== Auth::id() || $kelas->tahun_ajaran_id !== $ta->id,
            403,
            'Anda bukan wali kelas untuk kelas ini di tahun ajaran aktif.'
        );
    }

    public function index(Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorize_($kelas, $ta);

        $santriList = $kelas->santri()->orderBy('nama_lengkap')->get();

        $predikatMap = PredikatSikap::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->get()
            ->keyBy('santri_id');

        // Ketidakhadiran (sakit/izin/alpa) default-nya dihitung otomatis dari
        // presensi gabungan semua mapel -- dipakai sebagai nilai awal di form
        // kalau wali kelas belum pernah override manual (*_override masih null).
        $kehadiranAuto = [];
        foreach ($santriList as $santri) {
            $kehadiranAuto[$santri->id] = $this->penilaianService
                ->getPersentaseKehadiranTotal($santri, $kelas, $ta);
        }

        return view('wali-kelas.predikat-sikap', compact('kelas', 'santriList', 'predikatMap', 'kehadiranAuto', 'ta'));
    }

    public function store(Request $request, Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorize_($kelas, $ta);

        // Semua 4 kategori Kepribadian (Akhlaq/Kerajinan/Kebersihan/Kedisiplinan)
        // sepenuhnya diisi manual oleh wali kelas -- sesuai template rapor asli,
        // yang cuma punya 3 tingkat nilai (A/B/C -> ممتاز/جيد/مقبول), bukan A-E.
        // Sebelumnya ada mekanisme "Kedisiplinan auto-hitung dari presensi" --
        // dihapus karena template rapor asli tidak punya rumus semacam itu,
        // semua 4 kategori murni input manual wali kelas.
        $request->validate([
            'predikat'                    => ['required', 'array'],
            'predikat.*.kedisiplinan'     => ['required', 'in:A,B,C'],
            'predikat.*.kebersihan'       => ['required', 'in:A,B,C'],
            'predikat.*.kerajinan'        => ['required', 'in:A,B,C'],
            'predikat.*.akhlak'           => ['required', 'in:A,B,C'],
            'predikat.*.sakit'            => ['nullable', 'integer', 'min:0'],
            'predikat.*.izin'             => ['nullable', 'integer', 'min:0'],
            'predikat.*.alpa'             => ['nullable', 'integer', 'min:0'],
            'predikat.*.catatan'          => ['nullable', 'string', 'max:500'],
        ]);

        // Penting: request->predikat berupa array ber-key santri_id. Tanpa cross-check
        // ini, wali kelas kelas A bisa saja mengirim santri_id milik kelas B lewat
        // request yang dimanipulasi manual (bukan lewat form), dan tetap lolos karena
        // authorize_() di atas cuma mengecek kepemilikan $kelas, bukan keanggotaan
        // tiap santri_id di dalamnya.
        $validSantriIds = $kelas->santri()->pluck('santri.id')->all();

        foreach ($request->predikat as $santriId => $data) {
            if (!in_array((int) $santriId, $validSantriIds, true)) {
                continue;
            }

            PredikatSikap::updateOrCreate(
                ['santri_id' => $santriId, 'tahun_ajaran_id' => $ta->id],
                [
                    'kelas_id'            => $kelas->id,
                    'kedisiplinan'        => $data['kedisiplinan'],
                    'kebersihan'          => $data['kebersihan'],
                    'kerajinan'           => $data['kerajinan'],
                    'akhlak'              => $data['akhlak'],
                    // Kosong/null berarti "pakai hasil auto-hitung dari presensi",
                    // diisi berarti override manual wali kelas.
                    'sakit_override'      => $data['sakit'] !== '' ? ($data['sakit'] ?? null) : null,
                    'izin_override'       => $data['izin'] !== '' ? ($data['izin'] ?? null) : null,
                    'alpa_override'       => $data['alpa'] !== '' ? ($data['alpa'] ?? null) : null,
                    'catatan_wali_kelas'  => $data['catatan'] ?? null,
                    'diinput_oleh'        => Auth::id(),
                ]
            );
        }

        ActivityLogService::log('predikat_sikap.bulk_updated', $kelas);

        return redirect()->route('wali-kelas.predikat-sikap.index', $kelas)
            ->with('success', 'Predikat sikap berhasil disimpan.');
    }
}
