<?php
// ============================================================
// app/Http/Controllers/Kesantrian/PelanggaranController.php
// ============================================================
namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use App\Models\Santri;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pelanggaran::with(['santri', 'kategori', 'pencatat'])
            ->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $query->whereHas(
                'santri',
                fn($q) =>
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('nis', 'like', "%{$request->search}%")
            );
        }
        if ($request->filled('tingkat')) {
            $query->whereHas('kategori', fn($q) => $q->where('tingkat', $request->tingkat));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'aktif');
        }
        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        $pelanggaran  = $query->paginate(20)->withQueryString();
        $kategoriList = KategoriPelanggaran::orderBy('tingkat')->orderBy('nama')->get();

        // Statistik
        $stats = [
            'total_aktif' => Pelanggaran::where('status', 'aktif')->count(),
            'berat'       => Pelanggaran::where('status', 'aktif')
                ->whereHas('kategori', fn($q) => $q->where('tingkat', 'berat'))->count(),
            'hari_ini'    => Pelanggaran::whereDate('tanggal', today())->count(),
        ];

        return view('pelanggaran.index', compact('pelanggaran', 'kategoriList', 'stats'));
    }

    public function create()
    {
        $santriList   = Santri::aktif()->orderBy('nama_lengkap')->get();
        $kategoriList = KategoriPelanggaran::orderBy('tingkat')->orderBy('nama')->get()
            ->groupBy('tingkat');

        return view('pelanggaran.create', compact('santriList', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'santri_id'              => ['required', 'exists:santri,id'],
            'kategori_pelanggaran_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'tanggal'                => ['required', 'date', 'before_or_equal:today'],
            'deskripsi'              => ['required', 'string', 'max:500'],
            'sanksi'                 => ['nullable', 'string', 'max:200'],
        ]);

        $pelanggaran = Pelanggaran::create([
            ...$request->only(
                'santri_id',
                'kategori_pelanggaran_id',
                'tanggal',
                'deskripsi',
                'sanksi'
            ),
            'status'       => 'aktif',
            'dicatat_oleh' => Auth::id(),
        ]);

        ActivityLogService::logCreate($pelanggaran);

        // Cek batas poin
        $totalPoin = $pelanggaran->santri->total_poin_pelanggaran;
        $this->cekBatasPoin($pelanggaran->santri, $totalPoin);

        return redirect()->route('kesantrian.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil dicatat.');
    }

    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load(['santri', 'kategori', 'pencatat']);
        return view('pelanggaran.show', compact('pelanggaran'));
    }

    public function edit(Pelanggaran $pelanggaran)
    {
        $santriList   = Santri::aktif()->orderBy('nama_lengkap')->get();
        $kategoriList = KategoriPelanggaran::orderBy('tingkat')->orderBy('nama')->get()
            ->groupBy('tingkat');

        return view('pelanggaran.edit', compact('pelanggaran', 'santriList', 'kategoriList'));
    }

    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $request->validate([
            'kategori_pelanggaran_id' => ['required', 'exists:kategori_pelanggaran,id'],
            'tanggal'                => ['required', 'date'],
            'deskripsi'              => ['required', 'string', 'max:500'],
            'sanksi'                 => ['nullable', 'string', 'max:200'],
        ]);

        $old = $pelanggaran->toArray();
        $pelanggaran->update($request->only(
            'kategori_pelanggaran_id',
            'tanggal',
            'deskripsi',
            'sanksi'
        ));
        ActivityLogService::logUpdate($pelanggaran, $old);

        return redirect()->route('kesantrian.pelanggaran.index')
            ->with('success', 'Data pelanggaran diperbarui.');
    }

    public function destroy(Pelanggaran $pelanggaran)
    {
        ActivityLogService::logDelete($pelanggaran);
        $pelanggaran->delete();

        return back()->with('success', 'Data pelanggaran dihapus.');
    }

    public function selesai(Pelanggaran $pelanggaran)
    {
        $pelanggaran->update(['status' => 'selesai']);
        ActivityLogService::log('pelanggaran.selesai', $pelanggaran);

        return back()->with('success', 'Pelanggaran ditandai selesai.');
    }

    private function cekBatasPoin(Santri $santri, int $totalPoin): void
    {
        $config = config('siak.pelanggaran');

        if ($totalPoin >= $config['batas_poin_dikeluarkan']) {
            \Log::warning("Santri {$santri->nama_lengkap} mencapai batas poin dikeluarkan: {$totalPoin}");
        } elseif ($totalPoin >= $config['batas_poin_skors']) {
            \Log::warning("Santri {$santri->nama_lengkap} mencapai batas poin skors: {$totalPoin}");
        } elseif ($totalPoin >= $config['batas_poin_panggilan_wali']) {
            \Log::info("Santri {$santri->nama_lengkap} perlu pemanggilan wali: {$totalPoin}");
        }
    }
}
