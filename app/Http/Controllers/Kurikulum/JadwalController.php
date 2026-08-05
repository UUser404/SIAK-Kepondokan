<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran; // Menyertakan Tahun Ajaran Aktif
use App\Models\TenagaPendidik; // Tetap menggunakan Model Lamamu
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil tahun ajaran aktif agar jadwal otomatis terfilter per periode
        $ta = TahunAjaran::aktif();

        // Query: Hanya menampilkan jadwal yang kelasnya berada di tahun ajaran aktif
        $query = JadwalPelajaran::whereHas('kelas', fn($q) => $q->where('tahun_ajaran_id', $ta?->id))
            ->with(['kelas.tingkatan', 'mataPelajaran', 'guru']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $jadwal    = $query->orderBy('hari')->orderBy('jam_mulai')->paginate(20)->withQueryString();

        // Hanya mengambil kelas yang ada di tahun ajaran aktif saat ini
        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)->orderBy('nama')->get();

        return view('jadwal.index', compact('jadwal', 'kelasList', 'ta'));
    }

    public function create()
    {
        $ta        = TahunAjaran::aktif();
        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)->orderBy('nama')->get();

        // Menyeimbangkan variabel compact dari kode lama ($mataPelajaran)
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();

        // Tetap menggunakan TenagaPendidik aktif sesuai database lamamu
        // Catatan: kolom 'nama' tidak ada di tabel tenaga_pendidik, nama guru ada di relasi user()
        $guruList  = TenagaPendidik::with('user')->where('status', 'aktif')->get()
            ->sortBy(fn($g) => $g->user?->name);

        return view('jadwal.create', compact('kelasList', 'mataPelajaran', 'guruList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id'           => ['required', 'exists:users,id'], // FK jadwal_pelajaran.guru_id -> users.id (bukan tenaga_pendidik.id)
            'hari'              => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])], // Menjaga format Huruf Kapital lamamu
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'jam_selesai'       => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan'           => ['nullable', 'string', 'max:50'],
        ]);

        // FIX: whereBetween di jam_mulai/jam_selesai cuma nangkep 2 dari 3 kasus overlap
        // -- kalau jadwal baru itu sepenuhnya "nested" di DALAM jadwal lama yang lebih
        // panjang (mis. lama 07:00-10:00, baru 08:00-09:00), jam_mulai & jam_selesai
        // punya jadwal lama itu keduanya di LUAR rentang baru, jadi lolos tanpa
        // terdeteksi bentrok. Overlap interval yang benar: existing.mulai < baru.selesai
        // DAN existing.selesai > baru.mulai.
        $bentrok = JadwalPelajaran::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'Guru yang bersangkutan sudah memiliki jadwal mengajar lain di waktu tersebut.');
        }

        $jadwal = JadwalPelajaran::create($validated);
        ActivityLogService::logCreate($jadwal);

        return redirect()->route('kurikulum.jadwal.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function show(JadwalPelajaran $jadwal)
    {
        $jadwal->load('kelas', 'mataPelajaran', 'guru');
        return view('jadwal.show', compact('jadwal'));
    }

    public function edit(JadwalPelajaran $jadwal)
    {
        $ta        = TahunAjaran::aktif();
        $kelasList = Kelas::where('tahun_ajaran_id', $ta?->id)->orderBy('nama')->get();
        $mataPelajaran = MataPelajaran::orderBy('nama')->get();
        $guruList  = TenagaPendidik::with('user')->where('status', 'aktif')->get()
            ->sortBy(fn($g) => $g->user?->name);

        return view('jadwal.edit', compact('jadwal', 'kelasList', 'mataPelajaran', 'guruList'));
    }

    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $validated = $request->validate([
            'kelas_id'          => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajaran,id'],
            'guru_id'           => ['required', 'exists:users,id'], // FK jadwal_pelajaran.guru_id -> users.id (bukan tenaga_pendidik.id)
            'hari'              => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'])],
            'jam_mulai'         => ['required', 'date_format:H:i'],
            'jam_selesai'       => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan'           => ['nullable', 'string', 'max:50'],
        ]);

        // FIX: sama seperti store() -- overlap interval yang benar, bukan whereBetween
        // titik awal/akhir doang (lihat catatan lengkap di method store()).
        $bentrok = JadwalPelajaran::where('guru_id', $validated['guru_id'])
            ->where('id', '!=', $jadwal->id)
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $validated['jam_selesai'])
            ->where('jam_selesai', '>', $validated['jam_mulai'])
            ->exists();

        if ($bentrok) {
            return back()->withInput()->with('error', 'Guru yang bersangkutan sudah memiliki jadwal mengajar lain di waktu tersebut.');
        }

        $old = $jadwal->toArray();
        $jadwal->update($validated);
        ActivityLogService::logUpdate($jadwal, $old); // Menyelaraskan pencatatan log dengan struktur baru

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
