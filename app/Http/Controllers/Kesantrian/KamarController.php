<?php
// ============================================================
// app/Http/Controllers/Kesantrian/KamarController.php
// ============================================================
namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\Kamar;
use App\Models\PenempatanKamar;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KamarController extends Controller
{
    public function index(Request $request)
    {
        $query = Kamar::with(['asrama', 'penempatanAktif.santri'])
            ->where('is_active', true);

        if ($request->filled('asrama_id')) {
            $query->where('asrama_id', $request->asrama_id);
        }
        if ($request->filled('jenis')) {
            $query->whereHas('asrama', fn($q) => $q->where('jenis', $request->jenis));
        }

        $kamarList  = $query->orderBy('asrama_id')->orderBy('nomor_kamar')->paginate(20)->withQueryString();
        $asramaList = Asrama::where('is_active', true)->get();

        return view('asrama.kamar-index', compact('kamarList', 'asramaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asrama_id'    => ['required', 'exists:asrama,id'],
            'nomor_kamar'  => [
                'required',
                'string',
                'max:10',
                Rule::unique('kamar')->where(fn($q) =>
                $q->where('asrama_id', $request->asrama_id))
            ],
            'kapasitas'    => ['required', 'integer', 'min:1', 'max:20'],
            'lantai'       => ['nullable', 'string', 'max:10'],
        ]);

        $kamar = Kamar::create(array_merge($request->only(
            'asrama_id',
            'nomor_kamar',
            'kapasitas',
            'lantai'
        ), ['is_active' => true]));

        ActivityLogService::logCreate($kamar);

        return back()->with('success', "Kamar {$kamar->nomor_kamar} berhasil ditambahkan.");
    }

    public function update(Request $request, Kamar $kamar)
    {
        $request->validate([
            'kapasitas' => ['required', 'integer', 'min:1', 'max:20'],
            'lantai'    => ['nullable', 'string', 'max:10'],
        ]);

        $kamar->update($request->only('kapasitas', 'lantai'));

        return back()->with('success', 'Data kamar diperbarui.');
    }

    /**
     * Tempatkan santri ke kamar
     */
    public function tempatkan(Request $request, Kamar $kamar)
    {
        $request->validate([
            'santri_id'      => ['required', 'exists:santri,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'tanggal_masuk'  => ['required', 'date'],
        ]);

        // Cek kapasitas
        $penghuni = $kamar->penempatanAktif()->count();
        if ($penghuni >= $kamar->kapasitas) {
            return back()->with('error', 'Kamar sudah penuh.');
        }

        // Cek santri sudah ada di kamar lain
        $sudahDitempatkan = PenempatanKamar::where('santri_id', $request->santri_id)
            ->where('is_aktif', true)
            ->exists();
        if ($sudahDitempatkan) {
            return back()->with('error', 'Santri sudah ditempatkan di kamar lain.');
        }

        DB::transaction(function () use ($request, $kamar) {
            PenempatanKamar::create([
                'santri_id'       => $request->santri_id,
                'kamar_id'        => $kamar->id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'tanggal_masuk'   => $request->tanggal_masuk,
                'is_aktif'        => true,
            ]);
        });

        return back()->with('success', 'Santri berhasil ditempatkan di kamar.');
    }

    /**
     * Pindahkan / keluarkan santri dari kamar
     */
    public function keluarkan(Request $request, PenempatanKamar $penempatan)
    {
        $request->validate([
            'tanggal_keluar' => ['required', 'date'],
        ]);

        $penempatan->update([
            'tanggal_keluar' => $request->tanggal_keluar,
            'is_aktif'       => false,
        ]);

        return back()->with('success', 'Santri berhasil dikeluarkan dari kamar.');
    }
}
