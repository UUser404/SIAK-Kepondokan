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

        return view('wali-kelas.predikat-sikap', compact('kelas', 'santriList', 'predikatMap', 'ta'));
    }

    /**
     * Hitung ulang Kedisiplinan SEMUA santri di kelas ini dari data presensi
     * gabungan (semua mapel). Boleh dijalankan kapan saja -- hasilnya tetap
     * bisa diedit manual sesudahnya (tidak dikunci).
     */
    public function hitungKedisiplinan(Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorize_($kelas, $ta);

        $santriList = $kelas->santri()->get();

        foreach ($santriList as $santri) {
            $kehadiran    = $this->penilaianService->getPersentaseKehadiranTotal($santri, $kelas, $ta);
            $kedisiplinan = $this->penilaianService->getPredikatKehadiran($kehadiran['persen']);

            $existing = PredikatSikap::where('santri_id', $santri->id)
                ->where('tahun_ajaran_id', $ta->id)
                ->first();

            if ($existing) {
                // Baris sudah ada -- cuma Kedisiplinan yang di-update. Kategori lain
                // (yang mungkin sudah diisi manual wali kelas sebelumnya) tidak disentuh.
                $existing->update(['kedisiplinan' => $kedisiplinan]);
            } else {
                // Baris baru -- kategori lain diisi 'C' sebagai default netral sementara;
                // wali kelas perlu meninjau & menyesuaikannya manual lewat form.
                PredikatSikap::create([
                    'santri_id'       => $santri->id,
                    'kelas_id'        => $kelas->id,
                    'tahun_ajaran_id' => $ta->id,
                    'kedisiplinan'    => $kedisiplinan,
                    'kebersihan'      => 'C',
                    'kerapihan'       => 'C',
                    'akhlak'          => 'C',
                ]);
            }
        }

        return back()->with('success', 'Kedisiplinan berhasil dihitung ulang dari data presensi. Kategori lain untuk santri yang belum pernah dinilai diisi default "C" -- silakan periksa & sesuaikan manual.');
    }

    public function store(Request $request, Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorize_($kelas, $ta);

        $request->validate([
            'predikat'                    => ['required', 'array'],
            'predikat.*.kedisiplinan'     => ['required', 'in:A,B,C,D,E'],
            'predikat.*.kebersihan'       => ['required', 'in:A,B,C,D,E'],
            'predikat.*.kerapihan'        => ['required', 'in:A,B,C,D,E'],
            'predikat.*.akhlak'           => ['required', 'in:A,B,C,D,E'],
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
                    'kerapihan'           => $data['kerapihan'],
                    'akhlak'              => $data['akhlak'],
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
