<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KategoriMataPelajaranController extends Controller
{
    public function index()
    {
        $kategoriMataPelajaran = KategoriMataPelajaran::withCount('mataPelajaran')->urut()->get();

        return view('kategori-mata-pelajaran.index', compact('kategoriMataPelajaran'));
    }

    public function create()
    {
        return view('kategori-mata-pelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'   => ['required', 'string', 'max:100', 'unique:kategori_mata_pelajaran,nama'],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ]);

        $kategori = KategoriMataPelajaran::create($validated);
        ActivityLogService::logCreate($kategori);

        return redirect()->route('admin.kategori-mata-pelajaran.index')
            ->with('success', 'Kategori mata pelajaran berhasil ditambahkan.');
    }

    public function edit(KategoriMataPelajaran $kategoriMataPelajaran)
    {
        return view('kategori-mata-pelajaran.edit', compact('kategoriMataPelajaran'));
    }

    public function update(Request $request, KategoriMataPelajaran $kategoriMataPelajaran)
    {
        $validated = $request->validate([
            'nama'   => ['required', 'string', 'max:100', Rule::unique('kategori_mata_pelajaran', 'nama')->ignore($kategoriMataPelajaran->id)],
            'urutan' => ['nullable', 'integer', 'min:1'],
        ]);

        $old = $kategoriMataPelajaran->toArray();
        $namaLama = $kategoriMataPelajaran->nama;

        DB::transaction(function () use ($kategoriMataPelajaran, $validated, $namaLama) {
            $kategoriMataPelajaran->update($validated);

            // mata_pelajaran.kategori adalah string biasa (bukan foreign key),
            // jadi kalau nama kategori berubah, semua mapel yang sebelumnya
            // pakai nama lama HARUS ikut di-update ke nama baru -- kalau tidak,
            // mapel-mapel itu "kehilangan" kategorinya (string-nya jadi tidak
            // cocok dengan pilihan manapun di dropdown).
            if ($namaLama !== $kategoriMataPelajaran->nama) {
                MataPelajaran::where('kategori', $namaLama)
                    ->update(['kategori' => $kategoriMataPelajaran->nama]);
            }
        });

        ActivityLogService::logUpdate($kategoriMataPelajaran, $old);

        return redirect()->route('admin.kategori-mata-pelajaran.index')
            ->with('success', 'Kategori mata pelajaran berhasil diperbarui.');
    }

    public function destroy(KategoriMataPelajaran $kategoriMataPelajaran)
    {
        // Sama seperti guard di TingkatanController::destroy -- cegah hapus
        // kategori yang masih dipakai mapel, supaya tidak ada mapel yang
        // tiba-tiba kategorinya jadi string "yatim" (tidak cocok pilihan manapun).
        if ($kategoriMataPelajaran->mataPelajaran()->exists()) {
            return back()->with(
                'error',
                "Kategori \"{$kategoriMataPelajaran->nama}\" masih dipakai oleh mata pelajaran lain. Pindahkan dulu mapel-mapel itu ke kategori lain sebelum menghapus kategori ini."
            );
        }

        ActivityLogService::logDelete($kategoriMataPelajaran);
        $kategoriMataPelajaran->delete();

        return redirect()->route('admin.kategori-mata-pelajaran.index')
            ->with('success', 'Kategori mata pelajaran berhasil dihapus.');
    }
}
