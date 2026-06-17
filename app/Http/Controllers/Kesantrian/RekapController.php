<?php

namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PresensiKegiatan;
use App\Models\Santri;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function pelanggaran(Request $request)
    {
        $query = Pelanggaran::with('santri')
            ->orderByDesc('tanggal');

        if ($request->filled('santri_id')) {
            $query->where('santri_id', $request->santri_id);
        }

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggaran = $query->paginate(20)->withQueryString();
        $santriList  = Santri::aktif()->orderBy('nama_lengkap')->get();

        return view('kesantrian.rekap.pelanggaran', compact('pelanggaran', 'santriList'));
    }

    public function presensi(Request $request)
    {
        $santriList = Santri::aktif()->orderBy('nama_lengkap')->get();

        $rekap = [];

        if ($request->filled('dari') && $request->filled('sampai')) {
            $rekap = PresensiKegiatan::with('santri')
                ->whereBetween('tanggal', [$request->dari, $request->sampai])
                ->when($request->filled('santri_id'), fn($q) => $q->where('santri_id', $request->santri_id))
                ->orderBy('tanggal')
                ->get()
                ->groupBy('santri_id');
        }

        return view('kesantrian.rekap.presensi', compact('rekap', 'santriList'));
    }
}
