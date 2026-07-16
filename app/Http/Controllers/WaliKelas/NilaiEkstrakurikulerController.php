<?php
// ============================================================
// app/Http/Controllers/WaliKelas/NilaiEkstrakurikulerController.php
// ============================================================
namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use App\Models\Kelas;
use App\Models\NilaiEkstrakurikuler;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiEkstrakurikulerController extends Controller
{
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

        $santriList     = $kelas->santri()->orderBy('nama_lengkap')->get();
        $ekskulList     = Ekstrakurikuler::where('is_active', true)->orderBy('nama')->get();

        // nilaiMap[santri_id][ekskul_id] = NilaiEkstrakurikuler
        $nilaiMap = NilaiEkstrakurikuler::where('kelas_id', $kelas->id)
            ->where('tahun_ajaran_id', $ta->id)
            ->get()
            ->groupBy('santri_id')
            ->map(fn($rows) => $rows->keyBy('ekstrakurikuler_id'));

        return view('wali-kelas.nilai-ekstrakurikuler', compact('kelas', 'santriList', 'ekskulList', 'nilaiMap', 'ta'));
    }

    public function store(Request $request, Kelas $kelas)
    {
        $ta = TahunAjaran::aktif();
        $this->authorize_($kelas, $ta);

        $request->validate([
            'nilai'   => ['required', 'array'],
            'nilai.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $count = 0;
        foreach ($request->nilai as $santriId => $perEkskul) {
            foreach ($perEkskul as $ekskulId => $nilaiValue) {
                if ($nilaiValue === null || $nilaiValue === '') continue;

                NilaiEkstrakurikuler::updateOrCreate(
                    [
                        'santri_id'          => $santriId,
                        'ekstrakurikuler_id' => $ekskulId,
                        'tahun_ajaran_id'    => $ta->id,
                    ],
                    [
                        'kelas_id'     => $kelas->id,
                        'nilai'        => $nilaiValue,
                        'diinput_oleh' => Auth::id(),
                    ]
                );
                $count++;
            }
        }

        ActivityLogService::log('nilai_ekstrakurikuler.bulk_updated', $kelas);

        return redirect()->route('wali-kelas.nilai-ekstrakurikuler.index', $kelas)
            ->with('success', "{$count} nilai ekstrakurikuler berhasil disimpan.");
    }
}
