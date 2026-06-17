<?php

namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\JenisKegiatan;
use App\Models\PresensiKegiatan;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresensiKegiatanController extends Controller
{
    /**
     * Index — pilih tanggal & jenis kegiatan
     */
    public function index(Request $request)
    {
        $tanggal       = $request->get('tanggal', today()->format('Y-m-d'));
        $kegiatanId    = $request->get('kegiatan_id');
        $kegiatanList  = JenisKegiatan::where('is_active', true)->orderBy('waktu_default')->get();

        $rekapHarian = null;
        if ($kegiatanId) {
            $rekapHarian = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatanId)
                ->whereDate('tanggal', $tanggal)
                ->with('santri')
                ->get()
                ->groupBy('status');
        }

        // Ringkasan semua kegiatan hari ini
        $ringkasanHariIni = $kegiatanList->map(function ($kegiatan) use ($tanggal) {
            $total = Santri::aktif()->count();
            $sudahInput = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
                ->whereDate('tanggal', $tanggal)
                ->count();
            return [
                'kegiatan'    => $kegiatan,
                'sudah_input' => $sudahInput,
                'total'       => $total,
                'persen'      => $total > 0 ? round(($sudahInput / $total) * 100) : 0,
            ];
        });

        return view('presensi-kegiatan.index', compact(
            'tanggal', 'kegiatanId', 'kegiatanList',
            'rekapHarian', 'ringkasanHariIni'
        ));
    }

    /**
     * Show form + data presensi satu kegiatan satu tanggal
     */
    public function show(string $tanggal, JenisKegiatan $kegiatan)
    {
        $santriList = Santri::aktif()
            ->with(['kamarAktif.asrama'])
            ->orderBy('nama_lengkap')
            ->get();

        // Ambil presensi yang sudah ada
        $sudahInput = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('santri_id');

        $rekap = [
            'hadir' => $sudahInput->where('status', 'hadir')->count(),
            'sakit' => $sudahInput->where('status', 'sakit')->count(),
            'izin'  => $sudahInput->where('status', 'izin')->count(),
            'alpa'  => $sudahInput->where('status', 'alpa')->count(),
        ];

        return view('presensi-kegiatan.show', compact(
            'tanggal', 'kegiatan', 'santriList', 'sudahInput', 'rekap'
        ));
    }

    /**
     * Simpan / update presensi kegiatan (bulk)
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_kegiatan_id' => ['required', 'exists:jenis_kegiatan,id'],
            'tanggal'           => ['required', 'date', 'before_or_equal:today'],
            'presensi'          => ['required', 'array', 'min:1'],
            'presensi.*.santri_id'  => ['required', 'exists:santri,id'],
            'presensi.*.status'     => ['required', 'in:hadir,sakit,izin,alpa'],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:200'],
        ]);

        $count = DB::transaction(function () use ($request) {
            $count = 0;
            foreach ($request->presensi as $p) {
                PresensiKegiatan::updateOrCreate(
                    [
                        'santri_id'         => $p['santri_id'],
                        'jenis_kegiatan_id' => $request->jenis_kegiatan_id,
                        'tanggal'           => $request->tanggal,
                    ],
                    [
                        'status'       => $p['status'],
                        'keterangan'   => $p['keterangan'] ?? null,
                        'dicatat_oleh' => Auth::id(),
                    ]
                );
                $count++;
            }

            ActivityLogService::log('presensi_kegiatan.stored', null, [], [
                'kegiatan_id' => $request->jenis_kegiatan_id,
                'tanggal'     => $request->tanggal,
                'jumlah'      => $count,
            ]);

            return $count;
        });

        return redirect()
            ->route('kesantrian.presensi.show', [
                'tanggal'  => $request->tanggal,
                'kegiatan' => $request->jenis_kegiatan_id,
            ])
            ->with('success', "Presensi {$count} santri berhasil disimpan.");
    }

    /**
     * Rekap presensi kegiatan — per santri per bulan
     */
    public function rekap(Request $request)
    {
        $bulan      = $request->get('bulan', now()->month);
        $tahun      = $request->get('tahun', now()->year);
        $kegiatanId = $request->get('kegiatan_id');

        $kegiatanList = JenisKegiatan::where('is_active', true)->get();
        $kegiatan     = $kegiatanId ? JenisKegiatan::find($kegiatanId) : $kegiatanList->first();

        if (! $kegiatan) {
            return view('presensi-kegiatan.rekap', compact('kegiatanList', 'bulan', 'tahun'));
        }

        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();

        $presensiMap = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('santri_id');

        // Hari-hari yang ada presensinya
        $tanggalList = PresensiKegiatan::where('jenis_kegiatan_id', $kegiatan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->distinct('tanggal')
            ->orderBy('tanggal')
            ->pluck('tanggal');

        $rows = $santriList->map(function ($santri) use ($presensiMap, $tanggalList) {
            $data = $presensiMap[$santri->id] ?? collect();
            $byTanggal = $data->keyBy(fn($p) => $p->tanggal->format('Y-m-d'));

            return [
                'santri'    => $santri,
                'detail'    => $tanggalList->map(fn($t) => $byTanggal[$t->format('Y-m-d')] ?? null),
                'hadir'     => $data->where('status', 'hadir')->count(),
                'sakit'     => $data->where('status', 'sakit')->count(),
                'izin'      => $data->where('status', 'izin')->count(),
                'alpa'      => $data->where('status', 'alpa')->count(),
                'persen'    => $tanggalList->count() > 0
                    ? round(($data->where('status', 'hadir')->count() / $tanggalList->count()) * 100)
                    : 0,
            ];
        });

        return view('presensi-kegiatan.rekap', compact(
            'kegiatanList', 'kegiatan', 'bulan', 'tahun',
            'santriList', 'tanggalList', 'rows'
        ));
    }
}
