<?php
// ============================================================
// app/Http/Controllers/Admin/TahunAjaranController.php
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('nama')->orderByDesc('semester')->paginate(10);
        return view('tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function create()
    {
        return view('tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'            => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ], [
            'nama.regex'      => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2025/2026)',
        ]);

        $ta = TahunAjaran::create($validated);
        ActivityLogService::logCreate($ta);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        return view('tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $request->validate([
            'nama'            => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'        => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ]);

        $old = $tahunAjaran->toArray();
        $tahunAjaran->update($validated);
        ActivityLogService::logUpdate($tahunAjaran, $old);

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function aktifkan(TahunAjaran $tahunAjaran)
    {
        DB::transaction(function () use ($tahunAjaran) {
            // Nonaktifkan semua dulu
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);
            // Aktifkan yang dipilih
            $tahunAjaran->update(['is_active' => true]);
            ActivityLogService::log('tahun_ajaran.activated', $tahunAjaran);
        });

        return back()->with('success', "Tahun ajaran {$tahunAjaran->nama_lengkap} diaktifkan.");
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang aktif.');
        }

        ActivityLogService::logDelete($tahunAjaran);
        $tahunAjaran->delete();

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}
