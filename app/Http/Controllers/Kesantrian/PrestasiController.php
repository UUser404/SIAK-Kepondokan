<?php
// ============================================================
// app/Http/Controllers/Kesantrian/PrestasiController.php
// ============================================================
namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Models\Santri;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PrestasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Prestasi::with(['santri'])
            ->orderByDesc('tanggal');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_prestasi', 'like', "%{$request->search}%")
                    ->orWhereHas(
                        'santri',
                        fn($s) =>
                        $s->where('nama_lengkap', 'like', "%{$request->search}%")
                    );
            });
        }
        if ($request->filled('jenis')) {
            $query->where('jenis',   $request->jenis);
        }
        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        $prestasi = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => Prestasi::count(),
            'tahun_ini'   => Prestasi::whereYear('tanggal', now()->year)->count(),
            'nasional'    => Prestasi::whereIn('tingkat', ['nasional', 'internasional'])
                ->whereYear('tanggal', now()->year)->count(),
        ];

        return view('prestasi.index', compact('prestasi', 'stats'));
    }

    public function create()
    {
        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();
        return view('prestasi.create', compact('santriList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'santri_id'    => ['required', 'exists:santri,id'],
            'nama_prestasi' => ['required', 'string', 'max:200'],
            'jenis'        => ['required', Rule::in(['akademik', 'non_akademik', 'hafalan', 'lainnya'])],
            'tingkat'      => ['required', Rule::in(['pondok', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional'])],
            'peringkat'    => ['nullable', Rule::in(['juara_1', 'juara_2', 'juara_3', 'harapan', 'peserta', 'lainnya'])],
            'tanggal'      => ['required', 'date'],
            'keterangan'   => ['nullable', 'string', 'max:500'],
        ]);

        $prestasi = Prestasi::create([
            ...$request->only('santri_id', 'nama_prestasi', 'jenis', 'tingkat', 'peringkat', 'tanggal', 'keterangan'),
            'dicatat_oleh' => Auth::id(),
        ]);

        ActivityLogService::logCreate($prestasi);

        return redirect()->route('kesantrian.prestasi.index')
            ->with('success', 'Prestasi berhasil dicatat.');
    }

    public function edit(Prestasi $prestasi)
    {
        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();
        return view('prestasi.edit', compact('prestasi', 'santriList'));
    }

    public function update(Request $request, Prestasi $prestasi)
    {
        $request->validate([
            'nama_prestasi' => ['required', 'string', 'max:200'],
            'jenis'        => ['required', Rule::in(['akademik', 'non_akademik', 'hafalan', 'lainnya'])],
            'tingkat'      => ['required', Rule::in(['pondok', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional'])],
            'peringkat'    => ['nullable', Rule::in(['juara_1', 'juara_2', 'juara_3', 'harapan', 'peserta', 'lainnya'])],
            'tanggal'      => ['required', 'date'],
            'keterangan'   => ['nullable', 'string', 'max:500'],
        ]);

        $old = $prestasi->toArray();
        $prestasi->update($request->only('nama_prestasi', 'jenis', 'tingkat', 'peringkat', 'tanggal', 'keterangan'));
        ActivityLogService::logUpdate($prestasi, $old);

        return redirect()->route('kesantrian.prestasi.index')
            ->with('success', 'Data prestasi diperbarui.');
    }

    public function destroy(Prestasi $prestasi)
    {
        ActivityLogService::logDelete($prestasi);
        $prestasi->delete();

        return back()->with('success', 'Data prestasi dihapus.');
    }
}
