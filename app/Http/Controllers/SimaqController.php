<?php

namespace App\Http\Controllers;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use App\Services\SimaqScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimaqController extends Controller
{
    protected SimaqScoringService $scoringService;

    public function __construct(SimaqScoringService $scoringService) 
    {
        $this->scoringService = $scoringService;
    }

    /**
     * Dashboard SIMAQ
     */
    /**
     * Dashboard SIMAQ - Overview, Grafik & Leaderboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $guruId = $user->tenagaPendidik->id ?? 0;
        $isAdmin = $user->hasRole(['admin', 'super_admin']);
        
        // 1. DATA STATISTIK ATAS
        if ($isAdmin) {
            $totalPenilaian = SimaqPenilaian::count();
            $totalSantri = Santri::whereHas('simaqPenilaians')->distinct('santri_id')->count();
            $totalGuru = SimaqPenilaian::distinct('guru_id')->count();
        } else {
            $guru = $user->tenagaPendidik;
            $totalPenilaian = $guru ? $guru->simaqPenilaians()->count() : 0;
            $totalSantri = $guru ? $guru->simaqPenilaians()->distinct('santri_id')->count() : 0;
            $totalGuru = 1;
        }

        // 2. DATA GRAFIK: Tren Setoran 7 Hari Terakhir
        $chartDates = collect(range(6, 0))->map(function($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        $chartData = $chartDates->map(function($date) use ($isAdmin, $guruId) {
            $query = SimaqPenilaian::whereDate('tanggal', $date);
            if (!$isAdmin) {
                // Jika guru, hanya lihat grafik setorannya sendiri
                $query->where('guru_id', $guruId); 
            }
            return $query->count();
        });

        // Format tanggal untuk label di bawah grafik (Contoh: "24 Jul")
        $chartLabels = $chartDates->map(function($date) {
            return \Carbon\Carbon::parse($date)->translatedFormat('d M'); 
        });

        // 3. DATA LEADERBOARD: Top 5 Santri
        // Menggunakan data otomatis dari SimaqPenilaianObserver
        $leaderboard = Santri::where('simaq_total_setoran', '>', 0)
            ->when(!$isAdmin, function($query) use ($guruId) {
                // Guru hanya melihat santri yang pernah ia nilai
                return $query->whereHas('simaqPenilaians', function($q) use ($guruId) {
                    $q->where('guru_id', $guruId);
                });
            })
            ->orderByDesc('simaq_juz_tercapai')   // Prioritas 1: Juz terbanyak
            ->orderByDesc('simaq_total_bintang')  // Prioritas 2: Bintang tertinggi
            ->orderByDesc('simaq_total_setoran')  // Prioritas 3: Paling rajin setor
            ->take(5)
            ->get();

        return view('simaq.dashboard', compact(
            'totalPenilaian', 'totalSantri', 'totalGuru', 
            'chartLabels', 'chartData', 'leaderboard'
        ));
    }

    /**
     * Detail santri & History Nilai
     */
    public function detailSantri($id)
    {
        $santri = Santri::findOrFail($id);
        $penilaians = SimaqPenilaian::where('santri_id', $id)->latest('tanggal')->get();

        return view('simaq.detail', compact('santri', 'penilaians'));
    }

    /**
     * Tampilkan Form Input Nilai
     */
    public function createPenilaian($id)
    {
        $santri = Santri::findOrFail($id);
        return view('simaq.create', compact('santri'));
    }

    /**
     * Simpan Penilaian (Jembatan antara UI Sederhana & Database Kompleks)
     */
    /**
     * Simpan Penilaian & Kalkulasi Otomatis (Setoran Harian)
     */
    public function storePenilaian(Request $request)
    {
        // 1. Validasi input: Memastikan data berupa angka minimal 0
        $request->validate([
            'santri_id'            => 'required|exists:santri,id',
            'tanggal'              => 'required|date',
            'program'              => 'required|in:hafalan,tilawah,tahsin',
            'surah_ayat'           => 'required|string',
            'kesalahan_kelancaran' => 'required|integer|min:0',
            'kesalahan_tajwid'     => 'required|integer|min:0',
            'kesalahan_makhraj'    => 'required|integer|min:0',
            'catatan'              => 'nullable|string',
        ]);

        $guruId = auth()->user()->tenagaPendidik->id ?? 0;

        // 2. Minta SimaqScoringService untuk menghitung nilai akhir, huruf, bintang, dll
        $hasilKalkulasi = $this->scoringService->calculatePenilaian([
            'jenis'                => 'setoran_harian', 
            'kesalahan_kelancaran' => $request->kesalahan_kelancaran,
            'kesalahan_tajwid'     => $request->kesalahan_tajwid,
            'kesalahan_makhraj'    => $request->kesalahan_makhraj,
        ]);

        // 3. Gabungkan data dari Form (Kesalahan) dengan Hasil Kalkulasi Mesin, lalu Simpan!
        SimaqPenilaian::create(array_merge([
            'santri_id'            => $request->santri_id,
            'guru_id'              => $guruId,
            'kelas_id'             => 1, // Opsional jika butuh ID Kelas (default 1 untuk tes)
            'program'              => $request->program,
            'jenis'                => 'setoran_harian', // Fix type
            'tanggal'              => $request->tanggal,
            'surah_ayat'           => $request->surah_ayat,
            
            // Catat log kesalahannya (untuk histori)
            'kesalahan_kelancaran' => $request->kesalahan_kelancaran,
            'kesalahan_tajwid'     => $request->kesalahan_tajwid,
            'kesalahan_makhraj'    => $request->kesalahan_makhraj,
            
            'catatan'              => $request->catatan,
        ], $hasilKalkulasi)); // Timpa sisa kolom dengan (nilai_akhir, huruf, predikat, bintang)

        return redirect()->route('simaq.detail', $request->santri_id)
            ->with('success', 'Alhamdulillah! Nilai setoran berhasil dikalkulasi dan disimpan.');
    }

    /**
     * Hapus Penilaian
     */
    public function destroyPenilaian($id)
    {
        $penilaian = SimaqPenilaian::findOrFail($id);
        $santriId = $penilaian->santri_id;
        $penilaian->delete();

        return redirect()->route('simaq.detail', $santriId)
            ->with('success', 'Catatan nilai berhasil dihapus.');
    }
}