<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KomponenNilaiController extends Controller
{
    public function index(Request $request)
    {
        $query = KomponenNilai::with('mataPelajaran');

        if ($request->filled('search')) {
            $query->where(
                fn($q) =>
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('kode', 'like', "%{$request->search}%")
            );
        }

        $komponen    = $query->orderBy('urutan')->paginate(20)->withQueryString();
        $totalBobot  = KomponenNilai::where('is_active', true)->sum('bobot');

        return view('komponen-nilai.index', compact('komponen', 'totalBobot'));
    }

    public function create()
    {
        $mataPelajaran = MataPelajaran::where('is_active', true)->orderBy('nama')->get();
        return view('komponen-nilai.create', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'nama'              => ['required', 'string', 'max:100'],
            'kode'              => ['required', 'string', 'max:20', 'unique:komponen_nilai,kode'],
            'bobot'             => ['required', 'numeric', 'min:0', 'max:100'],
            'maks_input'        => ['required', 'integer', 'min:1', 'max:20'],
            'tipe'              => ['required', 'in:harian,uts,uas,praktik'],
            'urutan'            => ['nullable', 'integer', 'min:0'],
            'deskripsi'         => ['nullable', 'string', 'max:500'],
        ]);

        $komponen = KomponenNilai::create(array_merge($validated, [
            'kode'      => strtoupper($validated['kode']),
            'urutan'    => $validated['urutan'] ?? (KomponenNilai::max('urutan') + 1),
            'is_active' => true,
        ]));

        ActivityLogService::logCreate($komponen);

        return redirect()->route('admin.komponen-nilai.index')
            ->with('success', 'Komponen nilai berhasil ditambahkan.');
    }

    public function show(KomponenNilai $komponenNilai)
    {
        $komponenNilai->load('mataPelajaran');
        return view('komponen-nilai.show', compact('komponenNilai'));
    }

    public function edit(KomponenNilai $komponenNilai)
    {
        $mataPelajaran = MataPelajaran::where('is_active', true)->orderBy('nama')->get();
        return view('komponen-nilai.edit', compact('komponenNilai', 'mataPelajaran'));
    }

    public function update(Request $request, KomponenNilai $komponenNilai)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'nama'              => ['required', 'string', 'max:100'],
            'kode'              => [
                'required',
                'string',
                'max:20',
                Rule::unique('komponen_nilai', 'kode')->ignore($komponenNilai->id)
            ],
            'bobot'             => ['required', 'numeric', 'min:0', 'max:100'],
            'maks_input'        => ['required', 'integer', 'min:1', 'max:20'],
            'tipe'              => ['required', 'in:harian,uts,uas,praktik'],
            'urutan'            => ['nullable', 'integer', 'min:0'],
            'deskripsi'         => ['nullable', 'string', 'max:500'],
        ]);

        $old = $komponenNilai->toArray();
        $komponenNilai->update(array_merge($validated, [
            'kode' => strtoupper($validated['kode']),
        ]));

        ActivityLogService::logUpdate($komponenNilai, $old);

        return redirect()->route('admin.komponen-nilai.index')
            ->with('success', 'Komponen nilai berhasil diperbarui.');
    }

    public function destroy(KomponenNilai $komponenNilai)
    {
        $komponenNilai->update(['is_active' => false]);
        ActivityLogService::logDelete($komponenNilai);

        return back()->with('success', 'Komponen nilai dinonaktifkan.');
    }
}
