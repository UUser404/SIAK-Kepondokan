<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TenagaPendidik;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalPelajaran::with(['kelas', 'mataPelajaran', 'guru']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $jadwal    = $query->orderBy('hari')->orderBy('jam_mulai')->paginate(20)->withQueryString();
        $kelasList = Kelas::orderBy('nama')->get();

        return view('kurikulum.jadwal.index', compact('jadwal', 'kelasList'));
    }

    public function create()
    {
        $kelasList     = Kelas::orderBy('nama')->get();
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();
        $guruList      = TenagaPendidik::where('status', 'aktif')->orderBy('nama')->get();

        return view('kurikulum.jadwal.create', compact('kelasList', 'mataPelajaran', 'guruList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id'           => ['required', 'exists:tenaga_pendidik,id'],
            'hari'              => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'jam_selesai'       => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan'           => ['nullable', 'string', 'max:50'],
        ]);

        $jadwal = JadwalPelajaran::create($validated);
        ActivityLogService::logCreate($jadwal);

        return redirect()->route('kurikulum.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function show(JadwalPelajaran $jadwal)
    {
        $jadwal->load('kelas', 'mataPelajaran', 'guru');
        return view('kurikulum.jadwal.show', compact('jadwal'));
    }

    public function edit(JadwalPelajaran $jadwal)
    {
        $kelasList     = Kelas::orderBy('nama')->get();
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();
        $guruList      = TenagaPendidik::where('status', 'aktif')->orderBy('nama')->get();

        return view('kurikulum.jadwal.edit', compact('jadwal', 'kelasList', 'mataPelajaran', 'guruList'));
    }

    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $validated = $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id'           => ['required', 'exists:tenaga_pendidik,id'],
            'hari'              => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'jam_selesai'       => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan'           => ['nullable', 'string', 'max:50'],
        ]);

        $jadwal->update($validated);
        ActivityLogService::logUpdate($jadwal);

        return redirect()->route('kurikulum.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwal)
    {
        ActivityLogService::logDelete($jadwal);
        $jadwal->delete();

        return redirect()->route('kurikulum.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
