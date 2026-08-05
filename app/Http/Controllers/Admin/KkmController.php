<?php
// ============================================================
// app/Http/Controllers/Admin/KkmController.php
// Editor matrix KKM per mata pelajaran x tingkatan -- 1 halaman,
// semua kombinasi diisi & disimpan sekaligus (sesuai sheet "KKM"
// di template rapor asli, tiap tingkatan KMI punya kolom sendiri).
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KkmTingkatan;
use App\Models\MataPelajaran;
use App\Models\Tingkatan;
use Illuminate\Http\Request;

class KkmController extends Controller
{
    public function index()
    {
        $mapelList     = MataPelajaran::aktif()->orderBy('kategori')->orderBy('nama')->get();
        $tingkatanList = Tingkatan::urut()->get();

        // Kunci: "{mapel_id}-{tingkatan_id}" -> kkm
        $existing = KkmTingkatan::all()->keyBy(fn($row) => $row->mata_pelajaran_id . '-' . $row->tingkatan_id);

        return view('kkm.index', compact('mapelList', 'tingkatanList', 'existing'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kkm'                => ['required', 'array'],
            'kkm.*.*'            => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $mapelIds     = MataPelajaran::aktif()->pluck('id')->all();
        $tingkatanIds = Tingkatan::pluck('id')->all();
        $count = 0;

        foreach ($request->kkm as $mapelId => $perTingkatan) {
            if (!in_array((int) $mapelId, $mapelIds, true)) {
                continue;
            }

            foreach ($perTingkatan as $tingkatanId => $nilai) {
                if ($nilai === null || $nilai === '') {
                    continue;
                }
                if (!in_array((int) $tingkatanId, $tingkatanIds, true)) {
                    continue;
                }

                KkmTingkatan::updateOrCreate(
                    ['mata_pelajaran_id' => $mapelId, 'tingkatan_id' => $tingkatanId],
                    ['kkm' => $nilai]
                );
                $count++;
            }
        }

        return back()->with('success', "KKM berhasil disimpan ({$count} kombinasi mapel-tingkatan).");
    }
}
