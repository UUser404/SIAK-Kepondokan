<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('tahunAjaran')
            ->orderBy('nama')
            ->paginate(20);

        return view('kurikulum.kelas.index', compact('kelas'));
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('nama')->get();
        return view('kurikulum.kelas.create', compact('tahunAjaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama'            => ['required', 'string', 'max:50'],
            'tingkat'         => ['required', 'string', 'max:20'],
            'wali_kelas_id'   => ['nullable', 'exists:tenaga_pendidik,id'],
            'kapasitas'       => ['nullable', 'integer', 'min:1'],
        ]);

        $kelas = Kelas::create($validated);
        ActivityLogService::logCreate($kelas);

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(Kelas $kelas)
    {
        $kelas->load('tahunAjaran', 'waliKelas', 'santriKelas.santri');
        return view('kurikulum.kelas.show', compact('kelas'));
    }

    public function edit(Kelas $kelas)
    {
        $tahunAjaran = TahunAjaran::orderByDesc('nama')->get();
        return view('kurikulum.kelas.edit', compact('kelas', 'tahunAjaran'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama'            => ['required', 'string', 'max:50'],
            'tingkat'         => ['required', 'string', 'max:20'],
            'wali_kelas_id'   => ['nullable', 'exists:tenaga_pendidik,id'],
            'kapasitas'       => ['nullable', 'integer', 'min:1'],
        ]);

        $kelas->update($validated);
        ActivityLogService::logUpdate($kelas);

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        ActivityLogService::logDelete($kelas);
        $kelas->delete();

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
