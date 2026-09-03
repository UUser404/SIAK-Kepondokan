<?php

namespace App\Http\Controllers;

use App\Models\SimaqPenilaian;
use App\Models\Santri;
use App\Services\SimaqScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();

        // Proteksi keamanan: Jika sistem gagal membaca user, paksa kembali ke halaman login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah habis. Silakan login kembali.');
        }
        
        $guruId = $user->tenagaPendidik->id ?? 0;
        $isAdmin = $user->hasRole(['admin', 'super_admin', 'guru', 'guru_tahsin_tahfizh']);
        
        // DATA STATISTIK ATAS
        if ($isAdmin) {
            $totalPenilaian = SimaqPenilaian::count();
            // Cukup gunakan count() tanpa menyebut nama kolom
            $totalSantri = Santri::whereHas('simaqPenilaians')->count();
            $totalGuru = SimaqPenilaian::distinct('guru_id')->count('guru_id');
        } else {
            $guru = $user->tenagaPendidik;
            $totalPenilaian = $guru ? $guru->simaqPenilaians()->count() : 0;
            // Cara teraman: Hitung dari tabel penilaian, bukan tabel santri
            $totalSantri = $guru ? SimaqPenilaian::where('guru_id', $guru->id)->distinct('santri_id')->count('santri_id') : 0;
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
    public function detailSantri(int $id)
    {
        $santri = Santri::findOrFail($id);
        $penilaians = SimaqPenilaian::where('santri_id', $id)->latest('tanggal')->get();

        return view('simaq.detail', compact('santri', 'penilaians'));
    }

    /**
     * 4. Menampilkan form input nilai baru
     */
    public function createPenilaian(int $id)
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

        $guruId = Auth::user()->tenagaPendidik->id ?? 0;
        
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
    public function destroyPenilaian(int $id)
    {
        $penilaian = SimaqPenilaian::findOrFail($id);
        $santriId = $penilaian->santri_id;
        
        $penilaian->delete();

        return redirect()->route('simaq.detail', $santriId)
            ->with('success', 'Catatan nilai setoran berhasil dihapus.');
    }

    /**
     * Menampilkan halaman pilih santri untuk dicetak rapornya
     */
    public function laporanIndex()
    {
        // Untuk tahap ini, kita tampilkan semua santri
        $santris = \App\Models\Santri::all();
        return view('simaq.laporan.index', compact('santris'));
    }

    /**
     * Proses Kalkulasi & Tampilan Cetak Rapor Individu
     */
    public function cetakRapor(int $id)
    {
        $santri = \App\Models\Santri::with('simaqPenilaians')->findOrFail($id);
        $penilaians = $santri->simaqPenilaians;

        if($penilaians->isEmpty()) {
            return back()->with('error', 'Gagal! Santri ini belum memiliki riwayat nilai setoran untuk dicetak.');
        }

        // 1. Kalkulasi Rata-rata Murni (Tilawah/Kelancaran & Tajwid)
        $avgTilawah = round($penilaians->avg('nilai_kelancaran'), 2);
        $avgTajwid = round($penilaians->avg('nilai_tajwid'), 2);

        // 2. Konversi ke Huruf dan Predikat menggunakan Service Engine
        $kriteriaTilawah = $this->scoringService->getKriteriaNilai($avgTilawah, 'setoran_harian');
        $kriteriaTajwid = $this->scoringService->getKriteriaNilai($avgTajwid, 'setoran_harian');

        // 3. Kalkulasi Jumlah dan Rata-rata Akhir
        $jumlahNilai = $avgTilawah + $avgTajwid;
        $rataRataAkhir = round($jumlahNilai / 2, 2);

        // Data konfigurasi pondok dari file config/siak.php
        $pondok = config('siak.pondok');

        return view('simaq.laporan.cetak', compact(
            'santri', 'avgTilawah', 'avgTajwid', 
            'kriteriaTilawah', 'kriteriaTajwid', 
            'jumlahNilai', 'rataRataAkhir', 'pondok'
        ));
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