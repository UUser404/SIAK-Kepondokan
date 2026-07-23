<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PenugasanMengajar;
use App\Models\Pertemuan;
use App\Models\PresensiKbm;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    /**
     * Daftar kelas & mapel yang diampu guru ini (dari Penugasan Mengajar Kurikulum)
     * + riwayat pertemuan yang sudah diinput.
     */
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        // Kelas & mapel yang ditugaskan Kurikulum ke guru ini
        $penugasanList = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with(['mataPelajaran', 'kelas.tingkatan'])
            ->get();

        // Tandai kapan terakhir kali masing-masing kelas-mapel diinput presensinya
        $penugasanList->each(function ($penugasan) use ($user) {
            $penugasan->pertemuan_terakhir = Pertemuan::where('guru_id', $user->id)
                ->where('kelas_id', $penugasan->kelas_id)
                ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
                ->orderByDesc('tanggal')
                ->first();
        });

        // Riwayat pertemuan (10 terakhir)
        $riwayat = Pertemuan::where('guru_id', $user->id)
            ->with(['mataPelajaran', 'kelas', 'presensiKbm'])
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        return view('presensi-kbm.index', compact('penugasanList', 'riwayat'));
    }

    /**
     * Form input presensi untuk satu penugasan (kelas + mapel).
     * Tanggal diisi manual oleh guru (tidak boleh lebih dari hari ini).
     */
    public function create(PenugasanMengajar $penugasan)
    {
        abort_if($penugasan->guru_id !== Auth::id(), 403);

        $ta = TahunAjaran::aktif();

        // Daftar santri di kelas ini
        $santriList = SantriKelas::where('kelas_id', $penugasan->kelas_id)
            ->where('status', 'aktif')
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('santri')
            ->get()
            ->sortBy('santri.nama_lengkap');

        // Hitung pertemuan ke-n untuk kombinasi guru+kelas+mapel ini
        $pertemuanKe = Pertemuan::where('guru_id', $penugasan->guru_id)
            ->where('kelas_id', $penugasan->kelas_id)
            ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
            ->count() + 1;

        $penugasan->load(['mataPelajaran', 'kelas']);

        return view('presensi-kbm.create', compact('penugasan', 'santriList', 'pertemuanKe'));
    }

    /**
     * Simpan pertemuan + presensi semua santri
     */
    public function store(Request $request)
    {
        $request->validate([
            'penugasan_id'          => ['required', 'exists:penugasan_mengajar,id'],
            'tanggal'               => ['required', 'date', 'before_or_equal:today'],
            'jam_mulai'             => ['required', 'date_format:H:i'],
            'jam_selesai'           => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik'                 => ['nullable', 'string', 'max:200'],
            'materi'                => ['nullable', 'string', 'max:1000'],
            'catatan_guru'          => ['nullable', 'string', 'max:500'],
            'presensi'              => ['required', 'array', 'min:1'],
            'presensi.*.santri_id'  => ['required', 'exists:santri,id'],
            'presensi.*.status'     => ['required', 'in:hadir,sakit,izin,alpa'],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:200'],
        ], [
            'presensi.required'       => 'Data presensi santri wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $penugasan = PenugasanMengajar::findOrFail($request->penugasan_id);
        abort_if($penugasan->guru_id !== Auth::id(), 403);

        // Cek duplikat: kelas+mapel+guru yang sama, tanggal yang sama
        $exists = Pertemuan::where('guru_id', $penugasan->guru_id)
            ->where('kelas_id', $penugasan->kelas_id)
            ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();
        abort_if($exists, 422, 'Presensi untuk kelas & mapel ini pada tanggal tersebut sudah diinput.');

        $pertemuan = DB::transaction(function () use ($request, $penugasan) {
            $pertemuanKe = Pertemuan::where('guru_id', $penugasan->guru_id)
                ->where('kelas_id', $penugasan->kelas_id)
                ->where('mata_pelajaran_id', $penugasan->mata_pelajaran_id)
                ->count() + 1;

            $pertemuan = Pertemuan::create([
                'jadwal_pelajaran_id' => null, // fitur jadwal tidak dipakai lagi sebagai acuan
                'guru_id'             => $penugasan->guru_id,
                'kelas_id'            => $penugasan->kelas_id,
                'mata_pelajaran_id'   => $penugasan->mata_pelajaran_id,
                'tanggal'             => $request->tanggal,
                'jam_mulai'           => $request->jam_mulai,
                'jam_selesai'         => $request->jam_selesai,
                'pertemuan_ke'        => $pertemuanKe,
                'topik'               => $request->topik,
                'materi'              => $request->materi,
                'catatan_guru'        => $request->catatan_guru,
                'status'              => 'terlaksana',
            ]);

            // Bulk insert presensi
            $presensiData = collect($request->presensi)->map(fn($p) => [
                'pertemuan_id' => $pertemuan->id,
                'santri_id'    => $p['santri_id'],
                'status'       => $p['status'],
                'keterangan'   => $p['keterangan'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ])->toArray();

            PresensiKbm::insert($presensiData);
            ActivityLogService::logCreate($pertemuan);

            return $pertemuan;
        });

        return redirect()->route('guru.presensi.show', $pertemuan)
            ->with('success', 'Presensi berhasil disimpan.');
    }

    /**
     * Detail satu pertemuan
     */
    public function show(Pertemuan $pertemuan)
    {
        abort_if($pertemuan->guru_id !== Auth::id() && !Auth::user()->isManajemen(), 403);

        $pertemuan->load([
            'mataPelajaran',
            'kelas',
            'presensiKbm.santri',
        ]);

        $rekap = [
            'hadir' => $pertemuan->presensiKbm->where('status', 'hadir')->count(),
            'sakit' => $pertemuan->presensiKbm->where('status', 'sakit')->count(),
            'izin'  => $pertemuan->presensiKbm->where('status', 'izin')->count(),
            'alpa'  => $pertemuan->presensiKbm->where('status', 'alpa')->count(),
        ];

        return view('presensi-kbm.show', compact('pertemuan', 'rekap'));
    }

    /**
     * Form edit presensi
     */
    public function edit(Pertemuan $pertemuan)
    {
        abort_if($pertemuan->guru_id !== Auth::id(), 403);

        $ta = TahunAjaran::aktif();

        $pertemuan->load([
            'mataPelajaran',
            'kelas',
            'presensiKbm.santri',
        ]);

        // Daftar santri di kelas ini
        $santriList = SantriKelas::where('kelas_id', $pertemuan->kelas_id)
            ->where('status', 'aktif')
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('santri')
            ->get()
            ->sortBy('santri.nama_lengkap');

        return view('presensi-kbm.edit', compact('pertemuan', 'santriList'));
    }

    /**
     * Update presensi (koreksi)
     */
    public function update(Request $request, Pertemuan $pertemuan)
    {
        abort_if($pertemuan->guru_id !== Auth::id(), 403);

        $request->validate([
            'topik'        => ['nullable', 'string', 'max:200'],
            'materi'       => ['nullable', 'string', 'max:1000'],
            'catatan_guru' => ['nullable', 'string', 'max:500'],
            'presensi'     => ['required', 'array'],
            'presensi.*.santri_id' => ['required', 'exists:santri,id'],
            'presensi.*.status'    => ['required', 'in:hadir,sakit,izin,alpa'],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($request, $pertemuan) {
            $pertemuan->update([
                'topik'        => $request->topik,
                'materi'       => $request->materi,
                'catatan_guru' => $request->catatan_guru,
            ]);

            foreach ($request->presensi as $p) {
                PresensiKbm::updateOrCreate(
                    ['pertemuan_id' => $pertemuan->id, 'santri_id' => $p['santri_id']],
                    ['status' => $p['status'], 'keterangan' => $p['keterangan'] ?? null]
                );
            }

            ActivityLogService::log('presensi_kbm.updated', $pertemuan);
        });

        return redirect()->route('guru.presensi.show', $pertemuan)
            ->with('success', 'Presensi berhasil diperbarui.');
    }

    /**
     * Hapus pertemuan + presensi
     */
    public function destroy(Pertemuan $pertemuan)
    {
        abort_if($pertemuan->guru_id !== Auth::id(), 403);

        DB::transaction(function () use ($pertemuan) {
            PresensiKbm::where('pertemuan_id', $pertemuan->id)->delete();
            ActivityLogService::logDelete($pertemuan);
            $pertemuan->delete();
        });

        return redirect()->route('guru.presensi.index')
            ->with('success', 'Pertemuan presensi berhasil dihapus.');
    }
}
