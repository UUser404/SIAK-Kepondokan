<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\NilaiAkhir;
use App\Models\PenugasanMengajar;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function __construct(private PenilaianService $penilaianService) {}

    /**
     * Index — daftar kelas & mapel yang diampu guru ini
     */
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        $jadwalList = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['mataPelajaran', 'kelas.tingkatan'])
            ->get();

        // Cek progress input nilai per kelas-mapel
        $jadwalList = $jadwalList->map(function ($jadwal) use ($ta) {
            $totalSantri = SantriKelas::where('kelas_id', $jadwal->kelas_id)
                ->where('status', 'aktif')
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->count();

            $sudahInput = Nilai::where('kelas_id', $jadwal->kelas_id)
                ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->distinct('santri_id')
                ->count('santri_id');

            $jadwal->total_santri = $totalSantri;
            $jadwal->sudah_input  = $sudahInput;
            $jadwal->persen       = $totalSantri > 0
                ? round(($sudahInput / $totalSantri) * 100) : 0;

            return $jadwal;
        });

        return view('nilai.guru-index', compact('jadwalList', 'ta'));
    }

    /**
     * Show — form input nilai untuk satu kelas & mapel
     */
    public function show(Kelas $kelas, MataPelajaran $mataPelajaran)
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        // Pastikan guru ini mengajar kelas-mapel ini (berdasarkan Penugasan Mengajar dari Kurikulum)
        $penugasan = PenugasanMengajar::where('guru_id', $user->id)
            ->where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->firstOrFail();

        $komponen = KomponenNilai::where('is_active', true)->orderBy('urutan')->get();

        $santriList = SantriKelas::where('kelas_id', $kelas->id)
            ->where('status', 'aktif')
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('santri')
            ->get()
            ->sortBy('santri.nama_lengkap');

        // Ambil nilai yang sudah ada
        $nilaiMap = Nilai::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->get()
            ->groupBy('santri_id')
            ->map(fn($rows) => $rows->keyBy('komponen_nilai_id'));

        // Nilai akhir yang sudah dikalkulasi
        $nilaiAkhirMap = NilaiAkhir::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->get()
            ->keyBy('santri_id');

        return view('nilai.show', compact(
            'kelas', 'mataPelajaran', 'komponen',
            'santriList', 'nilaiMap', 'nilaiAkhirMap', 'ta'
        ));
    }

    /**
     * Store — simpan nilai satu santri satu komponen
     */
    public function store(Request $request)
    {
        $request->validate([
            'santri_id'          => ['required', 'exists:santri,id'],
            'kelas_id'           => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id'  => ['required', 'exists:mata_pelajaran,id'],
            'komponen_nilai_id'  => ['required', 'exists:komponen_nilai,id'],
            'tahun_ajaran_id'    => ['required', 'exists:tahun_ajaran,id'],
            'nilai'              => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $nilai = Nilai::updateOrCreate(
            [
                'santri_id'         => $request->santri_id,
                'kelas_id'          => $request->kelas_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'komponen_nilai_id' => $request->komponen_nilai_id,
                'tahun_ajaran_id'   => $request->tahun_ajaran_id,
            ],
            [
                'nilai'        => $request->nilai,
                'catatan'      => $request->catatan,
                'diinput_oleh' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'nilai'   => $nilai,
        ]);
    }

    /**
     * Bulk store — simpan semua nilai satu kelas sekaligus
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'tahun_ajaran_id'   => ['required', 'exists:tahun_ajaran,id'],
            'nilai'             => ['required', 'array'],
            'nilai.*.*'         => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        // Pastikan guru mengajar kelas-mapel ini (berdasarkan Penugasan Mengajar dari Kurikulum)
        $penugasan = PenugasanMengajar::where('guru_id', Auth::id())
            ->where('kelas_id', $request->kelas_id)
            ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
            ->firstOrFail();

        $saved = DB::transaction(function () use ($request) {
            $count = 0;
            foreach ($request->nilai as $santriId => $komponenNilai) {
                foreach ($komponenNilai as $komponenId => $nilaiValue) {
                    if ($nilaiValue === null || $nilaiValue === '') continue;

                    Nilai::updateOrCreate(
                        [
                            'santri_id'         => $santriId,
                            'kelas_id'          => $request->kelas_id,
                            'mata_pelajaran_id' => $request->mata_pelajaran_id,
                            'komponen_nilai_id' => $komponenId,
                            'tahun_ajaran_id'   => $request->tahun_ajaran_id,
                        ],
                        [
                            'nilai'        => $nilaiValue,
                            'diinput_oleh' => Auth::id(),
                        ]
                    );
                    $count++;
                }
            }
            return $count;
        });

        return redirect()
            ->route('guru.nilai.show', [
                'kelas'          => $request->kelas_id,
                'mataPelajaran'  => $request->mata_pelajaran_id,
            ])
            ->with('success', "{$saved} nilai berhasil disimpan.");
    }

    /**
     * Update satu nilai
     */
    public function update(Request $request, Nilai $nilai)
    {
        $request->validate([
            'nilai'   => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:200'],
        ]);

        $nilai->update([
            'nilai'        => $request->nilai,
            'catatan'      => $request->catatan,
            'diinput_oleh' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'nilai' => $nilai]);
    }
}
