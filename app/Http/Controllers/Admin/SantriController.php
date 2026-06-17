<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SantriController extends Controller
{
    public function index(Request $request)
    {
        $query = Santri::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $santri = $query->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        return view('santri.index', compact('santri'));
    }

    public function create()
    {
        return view('santri.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'           => ['required', 'string', 'unique:santri,nis'],
            'nisn'          => ['nullable', 'string', 'unique:santri,nisn'],
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'tempat_lahir'  => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat'        => ['nullable', 'string'],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['nullable', 'string', 'max:20'],
            'angkatan'      => ['required', 'integer'],
            'status'        => ['required', 'in:aktif,nonaktif,alumni'],
        ]);

        $santri = Santri::create($validated);
        ActivityLogService::logCreate($santri);

        return redirect()->route('admin.santri.show', $santri)
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function show(Santri $santri)
    {
        return view('santri.show', compact('santri'));
    }

    public function profil(Santri $santri)
    {
        return view('santri.profil', compact('santri'));
    }

    public function edit(Santri $santri)
    {
        return view('santri.edit', compact('santri'));
    }

    public function update(Request $request, Santri $santri)
    {
        $validated = $request->validate([
            'nis'           => ['required', 'string', 'unique:santri,nis,' . $santri->id],
            'nisn'          => ['nullable', 'string', 'unique:santri,nisn,' . $santri->id],
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'tempat_lahir'  => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat'        => ['nullable', 'string'],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['nullable', 'string', 'max:20'],
            'angkatan'      => ['required', 'integer'],
            'status'        => ['required', 'in:aktif,nonaktif,alumni'],
        ]);

        $santri->update($validated);
        ActivityLogService::logUpdate($santri);

        return redirect()->route('admin.santri.show', $santri)
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        ActivityLogService::logDelete($santri);
        $santri->delete();

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil dihapus.');
    }

    public function export()
    {
        $santri = Santri::orderBy('nama_lengkap')->get();
        return view('santri.export', compact('santri'));
    }
}
