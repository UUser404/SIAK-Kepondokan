<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class KomponenNilaiController extends Controller
{
    public function index()
    {
        $komponen = KomponenNilai::with('mataPelajaran')->orderBy('nama')->paginate(20);
        return view('komponen-nilai.index', compact('komponen'));
    }

    public function create()
    {
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();
        return view('komponen-nilai.create', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'nama'              => ['required', 'string', 'max:100'],
            'bobot'             => ['required', 'numeric', 'min:0', 'max:100'],
            'tipe'              => ['required', 'in:harian,uts,uas,praktik'],
        ]);

        $komponen = KomponenNilai::create($validated);
        ActivityLogService::logCreate($komponen);

        return redirect()->route('admin.komponen-nilai.index')
            ->with('success', 'Komponen nilai berhasil ditambahkan.');
    }

    public function show(KomponenNilai $komponenNilai)
    {
        return view('komponen-nilai.show', compact('komponenNilai'));
    }

    public function edit(KomponenNilai $komponenNilai)
    {
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();
        return view('komponen-nilai.edit', compact('komponenNilai', 'mataPelajaran'));
    }

    public function update(Request $request, KomponenNilai $komponenNilai)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'nama'              => ['required', 'string', 'max:100'],
            'bobot'             => ['required', 'numeric', 'min:0', 'max:100'],
            'tipe'              => ['required', 'in:harian,uts,uas,praktik'],
        ]);

        $komponenNilai->update($validated);
        ActivityLogService::logUpdate($komponenNilai);

        return redirect()->route('admin.komponen-nilai.index')
            ->with('success', 'Komponen nilai berhasil diperbarui.');
    }

    public function destroy(KomponenNilai $komponenNilai)
    {
        ActivityLogService::logDelete($komponenNilai);
        $komponenNilai->delete();

        return redirect()->route('admin.komponen-nilai.index')
            ->with('success', 'Komponen nilai berhasil dihapus.');
    }
}
