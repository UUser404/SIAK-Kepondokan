<?php

namespace App\Http\Controllers;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use App\Services\SimaqScoringService;
use Illuminate\Http\Request;

class SimaqController extends Controller
{
    protected SimaqScoringService $scoringService;

    public function __construct(SimaqScoringService $scoringService) 
    {
        $this->scoringService = $scoringService;
    }

    /**
     * 1. Dashboard SIMAQ - Overview, Grafik & Leaderboard
     */
    public function dashboard()
    {
        $user = auth()->user();
        $guruId = $user->tenagaPendidik->id ?? 0;
        $isAdmin = $user->hasRole(['admin', 'super_admin']);
        
        // DATA STATISTIK ATAS
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

        // DATA GRAFIK: Tren Setoran 7 Hari Terakhir
        $chartDates = collect(range(6, 0))->map(function($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        $chartData = $chartDates->map(function($date) use ($isAdmin, $guruId) {
            $query = SimaqPenilaian::whereDate('tanggal', $date);
            if (!$isAdmin) {
                $query->where('guru_id', $guruId); 
            }
            return $query->count();
        });

        $chartLabels = $chartDates->map(function($date) {
            return \Carbon\Carbon::parse($date)->translatedFormat('d M'); 
        });

        // DATA LEADERBOARD: Top 5 Santri
        $leaderboard = Santri::where('simaq_total_setoran', '>', 0)
            ->when(!$isAdmin, function($query) use ($guruId) {
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
     * 2. Menampilkan daftar santri untuk Setoran Harian
     */
    public function listSantri()
    {
        // Mengambil semua data santri
        $santris = \App\Models\Santri::all();
        
        return view('simaq.index', compact('santris'));
    }

    /**
     * 3. Menampilkan detail riwayat setoran santri
     */
    public function detailSantri($id)
    {
        $santri = Santri::findOrFail($id);
        $penilaians = SimaqPenilaian::where('santri_id', $id)->latest('tanggal')->get();

        return view('simaq.detail', compact('santri', 'penilaians'));
    }

    /**
     * 4. Menampilkan form input nilai baru
     */
    public function createPenilaian($id)
    {
        $santri = Santri::findOrFail($id);
        
        return view('simaq.create', compact('santri'));
    }

    /**
     * 5. Simpan Penilaian & Kalkulasi Otomatis (Setoran Harian)
     */
    public function storePenilaian(Request $request)
    {
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

        // Kalkulasi menggunakan Service
        $hasilKalkulasi = $this->scoringService->calculatePenilaian([
            'jenis'                => 'setoran_harian', 
            'kesalahan_kelancaran' => $request->kesalahan_kelancaran,
            'kesalahan_tajwid'     => $request->kesalahan_tajwid,
            'kesalahan_makhraj'    => $request->kesalahan_makhraj,
        ]);

        // Simpan ke database
        SimaqPenilaian::create(array_merge([
            'santri_id'            => $request->santri_id,
            'guru_id'              => $guruId,
            'kelas_id'             => 1, // Default sementara
            'program'              => $request->program,
            'jenis'                => 'setoran_harian', 
            'tanggal'              => $request->tanggal,
            'surah_ayat'           => $request->surah_ayat,
            'kesalahan_kelancaran' => $request->kesalahan_kelancaran,
            'kesalahan_tajwid'     => $request->kesalahan_tajwid,
            'kesalahan_makhraj'    => $request->kesalahan_makhraj,
            'catatan'              => $request->catatan,
        ], $hasilKalkulasi)); 

        return redirect()->route('simaq.detail', $request->santri_id)
            ->with('success', 'Alhamdulillah! Nilai setoran berhasil dikalkulasi dan disimpan.');
    }

    /**
     * 6. Menghapus data nilai (Soft Delete)
     */
    public function destroyPenilaian($id)
    {
        $penilaian = SimaqPenilaian::findOrFail($id);
        $santriId = $penilaian->santri_id;
        
        $penilaian->delete();

        return redirect()->route('simaq.detail', $santriId)
            ->with('success', 'Catatan nilai setoran berhasil dihapus.');
    }

    /**
     * 7. SINKRONISASI NILAI SIMAQ KE RAPOR UTAMA SIAK (Hybrid)
     */
    public function syncToRapor(Request $request)
    {
        $mapelSimaq = \App\Models\MataPelajaran::where('nama', 'like', '%Tahfizh%')
                        ->orWhere('nama', 'like', '%Tahsin%')
                        ->orWhere('nama', 'like', '%SIMAQ%')
                        ->first();

        if (!$mapelSimaq) {
            return back()->with('error', 'Gagal! Mata Pelajaran Tahsin/Tahfizh tidak ditemukan di sistem SIAK Utama.');
        }

        $santris = Santri::whereNotNull('simaq_total_nilai')->get();
        $count = 0;

        foreach($santris as $santri) {
            if (class_exists('\App\Models\Nilai')) {
                \App\Models\Nilai::updateOrCreate(
                    [
                        'santri_id' => $santri->id,
                        'mata_pelajaran_id' => $mapelSimaq->id,
                    ],
                    [
                        'nilai_akhir' => $santri->simaq_total_nilai, 
                        'catatan' => 'Sinkronisasi otomatis dari modul SIMAQ'
                    ]
                );
                $count++;
            }
        }

        return back()->with('success', "Alhamdulillah! Sebanyak $count nilai akhir SIMAQ berhasil ditarik ke Rapor SIAK Utama.");
    }
}