<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::query();

        if ($request->filled('search')) {
            $query->where(
                fn($q) =>
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('kode', 'like', "%{$request->search}%")
            );
        }

        $mataPelajaran = $query->orderBy('nama')->paginate(20)->withQueryString();
        return view('mata-pelajaran.index', compact('mataPelajaran'));
    }

    public function create()
    {
        // Dropdown kategori sekarang dari master data (bisa dikelola lewat
        // Admin > Kategori Mapel), bukan lagi array hardcoded di blade.
        $kategoriMataPelajaran = KategoriMataPelajaran::urut()->get();

        return view('mata-pelajaran.create', compact('kategoriMataPelajaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'      => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode'],
            'nama'      => ['required', 'string', 'max:100'],
            // Sengaja masih 'exists' ke kolom nama (bukan foreign key sungguhan) --
            // lihat catatan di migration create_kategori_mata_pelajaran_table
            // soal kenapa mata_pelajaran.kategori tetap string biasa.
            'kategori'  => ['nullable', 'string', 'max:100', Rule::exists('kategori_mata_pelajaran', 'nama')],
            'tingkat'   => ['required', 'string', 'max:20'],
            'kkm'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'kategori.exists' => 'Kategori tidak valid. Pilih dari daftar yang tersedia.',
        ]);

        $mapel = MataPelajaran::create(array_merge($validated, [
            'kode'      => strtoupper($validated['kode']),
            'is_active' => true,
        ]));

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
        $kategoriMataPelajaran = KategoriMataPelajaran::urut()->get();

        return view('mata-pelajaran.edit', compact('mataPelajaran', 'kategoriMataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $validated = $request->validate([
            'kode'      => [
                'required',
                'string',
                'max:20',
                Rule::unique('mata_pelajaran', 'kode')->ignore($mataPelajaran->id)
            ],
            'nama'      => ['required', 'string', 'max:100'],
            'kategori'  => ['nullable', 'string', 'max:100', Rule::exists('kategori_mata_pelajaran', 'nama')],
            'tingkat'   => ['required', 'string', 'max:20'],
            'kkm'       => ['nullable', 'integer', 'min:0', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ], [
            'kategori.exists' => 'Kategori tidak valid. Pilih dari daftar yang tersedia.',
        ]);

        $old = $mataPelajaran->toArray();
        $mataPelajaran->update(array_merge($validated, [
            'kode' => strtoupper($validated['kode']),
        ]));

        ActivityLogService::logUpdate($mataPelajaran, $old);

        return redirect()->route('admin.mata-pelajaran.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update(['is_active' => false]);
        ActivityLogService::logDelete($mataPelajaran);

        return back()->with('success', 'Mata pelajaran dinonaktifkan.');
    }
}
