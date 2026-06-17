<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $mataPelajaran = MataPelajaran::orderBy('nama')->paginate(20);
        return view('mata-pelajaran.index', compact('mataPelajaran'));
    }

    public function create()
    {
        return view('mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'   => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode'],
            'nama'   => ['required', 'string', 'max:100'],
            'tingkat'=> ['required', 'string', 'max:20'],
            'kkm'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $mapel = MataPelajaran::create($validated);
        ActivityLogService::logCreate($mapel);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(MataPelajaran $mataPelajaran)
    {
        return view('mata-pelajaran.show', compact('mataPelajaran'));
    }

    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('mata-pelajaran.edit', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'kode'   => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode,' . $mataPelajaran->id],
            'nama'   => ['required', 'string', 'max:100'],
            'tingkat'=> ['required', 'string', 'max:20'],
            'kkm'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $mataPelajaran->update($validated);
        ActivityLogService::logUpdate($mataPelajaran);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        ActivityLogService::logDelete($mataPelajaran);
        $mataPelajaran->delete();

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
