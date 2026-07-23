<?php

namespace App\Http\Controllers;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use App\Models\Kelas;
use App\Services\SimaqScoringService;
use App\Actions\SubmitSimaqPenilaianAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SimaqController extends Controller
{
    protected SimaqScoringService $scoringService;
    protected SubmitSimaqPenilaianAction $submitAction;

    public function __construct(
        SimaqScoringService $scoringService,
        SubmitSimaqPenilaianAction $submitAction
    ) {
        $this->scoringService = $scoringService;
        $this->submitAction = $submitAction;
    }

    /**
     * Dashboard SIMAQ - overview & statistik
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        if ($user->hasRole(['admin', 'super_admin'])) {
            // Admin: lihat stats global
            $totalPenilaian = SimaqPenilaian::count();
            $totalSantri = Santri::whereHas('simaqPenilaians')->distinct('santri_id')->count();
            $totalGuru = auth()->user()->tenagaPendidik->simaqPenilaians()->distinct('guru_id')->count() ?? 0;
        } else {
            // Guru SIMAQ: lihat stats pribadi
            $guru = $user->tenagaPendidik;
            $totalPenilaian = $guru->simaqPenilaians()->count();
            $totalSantri = $guru->simaqPenilaians()->distinct('santri_id')->count();
            $totalGuru = 1;
        }

        $recentPenilaians = SimaqPenilaian::query()
            ->when(!$user->hasRole(['admin', 'super_admin']), fn($q) => $q->where('guru_id', $user->tenagaPendidik->id ?? 0))
            ->with(['santri', 'guru', 'kelas'])
            ->latest('tanggal')
            ->limit(10)
            ->get();

        return view('simaq.dashboard', compact('totalPenilaian', 'totalSantri', 'totalGuru', 'recentPenilaians'));
    }

    /**
     * List santri untuk kelas guru yang login
     */
    public function listSantri(Request $request)
    {
        $user = auth()->user();
        $guru = $user->tenagaPendidik;

        $query = Santri::query();

        // Filter by kelas guru yang login (jika bukan admin)
        if (!$user->hasRole(['admin', 'super_admin']) && $guru) {
            $kelasIds = Kelas::where('wali_kelas_id', $user->id)->pluck('id');
            $query->whereHas('santriKelas', fn($q) => $q->whereIn('kelas_id', $kelasIds));
        }

        // Filter by program SIMAQ
        if ($request->program) {
            $query->where('simaq_program', $request->program);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nis', 'like', "%{$request->search}%")
                  ->orWhere('nama_lengkap', 'like', "%{$request->search}%");
            });
        }

        $santriList = $query->with('simaqPenilaians')->paginate(15);

        return view('simaq.santri.list', compact('santriList'));
    }

    /**
     * Detail santri - history penilaian SIMAQ
     */
    public function detailSantri(Santri $santri)
    {
        $this->authorize('view', $santri);

        $penilaians = $santri->simaqPenilaians()
            ->with(['guru', 'kelas'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        $statistik = $santri->simaq_statistik ?? [];

        return view('simaq.santri.detail', compact('santri', 'penilaians', 'statistik'));
    }

    /**
     * Store penilaian baru
     */
    public function storePenilaian(Request $request)
    {
        $validated = $request->validate([
            'santri_id'             => 'required|exists:santri,id',
            'guru_id'               => 'required|exists:tenaga_pendidik,id',
            'kelas_id'              => 'required|exists:kelas,id',
            'program'               => 'required|in:hafalan,tilawah,tahsin',
            'jenis'                 => 'required|in:setoran_harian,tasmi,pemantapan',
            'tanggal'               => 'required|date',
            'surah_ayat'            => 'nullable|string',
            'halaman'               => 'nullable|integer|min:1',
            'juz'                   => 'nullable|integer|min:1|max:30',
            'kesalahan_kelancaran'  => 'nullable|integer|min:0',
            'kesalahan_tajwid'      => 'nullable|integer|min:0',
            'kesalahan_makhraj'     => 'nullable|integer|min:0',
            'nilai_pemantapan'      => 'nullable|numeric|min:0|max:100',
            'nilai_tasmi'           => 'nullable|numeric|min:0|max:100',
            'catatan'               => 'nullable|string|max:1000',
        ]);

        try {
            $penilaian = $this->submitAction->execute($validated);
            
            return redirect()->route('simaq.santri.detail', $penilaian->santri)
                ->with('success', 'Penilaian berhasil disimpan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Update penilaian
     */
    public function updatePenilaian(Request $request, SimaqPenilaian $penilaian)
    {
        $this->authorize('update', $penilaian);

        $validated = $request->validate([
            'kesalahan_kelancaran'  => 'nullable|integer|min:0',
            'kesalahan_tajwid'      => 'nullable|integer|min:0',
            'kesalahan_makhraj'     => 'nullable|integer|min:0',
            'nilai_pemantapan'      => 'nullable|numeric|min:0|max:100',
            'nilai_tasmi'           => 'nullable|numeric|min:0|max:100',
            'catatan'               => 'nullable|string|max:1000',
        ]);

        try {
            $data = [
                'jenis'                    => $penilaian->jenis,
                'kesalahan_kelancaran'     => $validated['kesalahan_kelancaran'] ?? $penilaian->kesalahan_kelancaran,
                'kesalahan_tajwid'         => $validated['kesalahan_tajwid'] ?? $penilaian->kesalahan_tajwid,
                'kesalahan_makhraj'        => $validated['kesalahan_makhraj'] ?? $penilaian->kesalahan_makhraj,
            ];

            if (in_array($penilaian->jenis, ['tasmi', 'pemantapan'])) {
                $data['nilai_pemantapan'] = $validated['nilai_pemantapan'] ?? $penilaian->nilai_kelancaran;
                $data['nilai_tasmi'] = $validated['nilai_tasmi'] ?? $penilaian->nilai_tajwid;
            }

            $nilaiKalkulasi = $this->scoringService->calculatePenilaian($data);

            $penilaian->update(array_merge($validated, $nilaiKalkulasi));

            return back()->with('success', 'Penilaian berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengupdate penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Delete (soft delete) penilaian
     */
    public function destroyPenilaian(SimaqPenilaian $penilaian)
    {
        $this->authorize('delete', $penilaian);

        try {
            $penilaian->delete();
            
            return back()->with('success', 'Penilaian berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus penilaian: ' . $e->getMessage());
        }
    }
}
