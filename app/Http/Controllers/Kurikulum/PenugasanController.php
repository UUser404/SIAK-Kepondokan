<?php
// ============================================================
// app/Http/Controllers/Kurikulum/PenugasanController.php
// ============================================================
namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\PenugasanMengajar;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class PenugasanController extends Controller
{
    /**
     * Langkah 1: daftar guru — pilih siapa yang mau diatur penugasannya
     */
    public function index(Request $request)
    {
        $ta = TahunAjaran::aktif();

        $query = User::where('role', 'guru')
            ->withCount(['penugasanMengajar as jumlah_penugasan' => function ($q) use ($ta) {
                $ta && $q->where('tahun_ajaran_id', $ta->id);
            }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $guruList = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('penugasan.index', compact('guruList', 'ta'));
    }

    /**
     * Langkah 2: detail penugasan 1 guru — tambah mapel, lalu pilih kelas untuk mapel itu
     */
    public function show(User $guru)
    {
        abort_if($guru->role !== 'guru', 404);

        $ta = TahunAjaran::aktif();

        $penugasanList = PenugasanMengajar::where('guru_id', $guru->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['mataPelajaran', 'kelas'])
            ->get()
            ->groupBy('mata_pelajaran_id');

        $mapelList = MataPelajaran::orderBy('nama')->get();
        $kelasList = Kelas::when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->orderBy('nama')->get();

        return view('penugasan.show', compact('guru', 'penugasanList', 'mapelList', 'kelasList', 'ta'));
    }

    /**
     * Simpan 1 mapel + banyak kelas sekaligus untuk guru ini.
     * Diulang dari form yang sama kalau guru mengampu mapel lain.
     */
    public function store(Request $request, User $guru)
    {
        abort_if($guru->role !== 'guru', 404);

        $ta = TahunAjaran::aktif();
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $validated = $request->validate([
            'mata_pelajaran_id'   => ['required', 'exists:mata_pelajaran,id'],
            'kelas_id'            => ['required', 'array', 'min:1'],
            'kelas_id.*'          => ['exists:kelas,id'],
        ], [
            'kelas_id.required' => 'Pilih minimal satu kelas.',
        ]);

        foreach ($validated['kelas_id'] as $kelasId) {
            $penugasan = PenugasanMengajar::firstOrCreate([
                'guru_id'           => $guru->id,
                'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
                'kelas_id'          => $kelasId,
                'tahun_ajaran_id'   => $ta->id,
            ]);

            if ($penugasan->wasRecentlyCreated) {
                ActivityLogService::logCreate($penugasan);
            }
        }

        return redirect()->route('kurikulum.penugasan.show', $guru)
            ->with('success', 'Penugasan mengajar berhasil ditambahkan.');
    }

    /**
     * Hapus satu baris penugasan (satu kombinasi kelas untuk satu mapel)
     */
    public function destroy(PenugasanMengajar $penugasan)
    {
        $guru = $penugasan->guru;
        ActivityLogService::logDelete($penugasan);
        $penugasan->delete();

        return redirect()->route('kurikulum.penugasan.show', $guru)
            ->with('success', 'Penugasan berhasil dihapus.');
    }
}
