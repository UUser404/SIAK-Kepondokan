<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tingkatan;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TingkatanController extends Controller
{
    public function index()
    {
        $tingkatan = Tingkatan::withCount('kelas')->urut()->get();

        return view('tingkatan.index', compact('tingkatan'));
    }

    public function create()
    {
        return view('tingkatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:50', 'unique:tingkatan,nama'],
            'urutan'  => ['required', 'integer', 'min:1', 'unique:tingkatan,urutan'],
        ], [
            'urutan.unique' => 'Urutan ini sudah dipakai tingkatan lain. Gunakan angka urutan yang berbeda.',
        ]);

        $tingkatan = Tingkatan::create($validated);
        ActivityLogService::logCreate($tingkatan);

        return redirect()->route('admin.tingkatan.index')
            ->with('success', 'Tingkatan berhasil ditambahkan.');
    }

    public function edit(Tingkatan $tingkatan)
    {
        return view('tingkatan.edit', compact('tingkatan'));
    }

    public function update(Request $request, Tingkatan $tingkatan)
    {
        $validated = $request->validate([
            'nama'   => ['required', 'string', 'max:50', Rule::unique('tingkatan', 'nama')->ignore($tingkatan->id)],
            'urutan' => ['required', 'integer', 'min:1', Rule::unique('tingkatan', 'urutan')->ignore($tingkatan->id)],
        ], [
            'urutan.unique' => 'Urutan ini sudah dipakai tingkatan lain. Gunakan angka urutan yang berbeda.',
        ]);

        $old = $tingkatan->toArray();
        $tingkatan->update($validated);
        ActivityLogService::logUpdate($tingkatan, $old);

        return redirect()->route('admin.tingkatan.index')
            ->with('success', 'Tingkatan berhasil diperbarui.');
    }

    public function destroy(Tingkatan $tingkatan)
    {
        // Penting: kelas.tingkatan_id pakai cascadeOnDelete() -- kalau tingkatan ini
        // dihapus langsung lewat DB, SEMUA kelas (dan data turunannya: santri_kelas, dst)
        // di bawah tingkatan ini ikut kehapus. Jadi di level aplikasi kita cegah hapus
        // kalau masih ada kelas yang menempel, supaya tidak ada yang tidak sengaja
        // menghapus data akademik.
        if ($tingkatan->kelas()->exists()) {
            return back()->with(
                'error',
                "Tingkatan \"{$tingkatan->nama}\" masih punya kelas yang terdaftar. Pindahkan atau hapus kelas-kelas itu dulu sebelum menghapus tingkatan ini."
            );
        }

        ActivityLogService::logDelete($tingkatan);
        $tingkatan->delete();

        return redirect()->route('admin.tingkatan.index')
            ->with('success', 'Tingkatan berhasil dihapus.');
    }
}
