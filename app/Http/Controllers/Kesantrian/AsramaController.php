<?php
// ============================================================
// app/Http/Controllers/Kesantrian/AsramaController.php
// ============================================================
namespace App\Http\Controllers\Kesantrian;

use App\Http\Controllers\Controller;
use App\Models\Asrama;
use App\Models\Kamar;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsramaController extends Controller
{
    public function index()
    {
        $asramaList = Asrama::with(['kamar' => fn($q) => $q->withCount([
            'penempatanAktif as penghuni_count'
        ])])
            ->withCount('kamar')
            ->get();

        $stats = [
            'total_kamar'    => Kamar::where('is_active', true)->count(),
            'total_penghuni' => \App\Models\PenempatanKamar::where('is_aktif', true)->count(),
            'total_kapasitas' => Kamar::where('is_active', true)->sum('kapasitas'),
        ];
        $stats['tersedia'] = $stats['total_kapasitas'] - $stats['total_penghuni'];

        return view('asrama.index', compact('asramaList', 'stats'));
    }

    public function create()
    {
        return view('asrama.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => ['required', 'string', 'max:100'],
            'jenis'       => ['required', Rule::in(['putra', 'putri'])],
            'pengurus'    => ['nullable', 'string', 'max:100'],
            'keterangan'  => ['nullable', 'string', 'max:300'],
        ]);

        $asrama = Asrama::create(array_merge($validated, ['is_active' => true]));
        ActivityLogService::logCreate($asrama);

        return redirect()->route('kesantrian.asrama.index')
            ->with('success', 'Asrama berhasil ditambahkan.');
    }

    public function show(Asrama $asrama)
    {
        $asrama->load(['kamar' => fn($q) => $q->withCount(['penempatanAktif as penghuni'])
            ->orderBy('nomor_kamar')]);

        return view('asrama.show', compact('asrama'));
    }

    public function edit(Asrama $asrama)
    {
        return view('asrama.edit', compact('asrama'));
    }

    public function update(Request $request, Asrama $asrama)
    {
        $validated = $request->validate([
            'nama'       => ['required', 'string', 'max:100'],
            'jenis'      => ['required', Rule::in(['putra', 'putri'])],
            'pengurus'   => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:300'],
        ]);

        $old = $asrama->toArray();
        $asrama->update($validated);
        ActivityLogService::logUpdate($asrama, $old);

        return redirect()->route('kesantrian.asrama.show', $asrama)
            ->with('success', 'Data asrama berhasil diperbarui.');
    }

    public function destroy(Asrama $asrama)
    {
        $asrama->update(['is_active' => false]);
        ActivityLogService::logDelete($asrama);

        return redirect()->route('kesantrian.asrama.index')
            ->with('success', 'Asrama dinonaktifkan.');
    }
}
