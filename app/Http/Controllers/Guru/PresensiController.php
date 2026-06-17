<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
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
     * Daftar jadwal guru — ringkasan sudah/belum presensi per hari
     */
    public function index(Request $request)
    {
        $user  = Auth::user();
        $ta    = TahunAjaran::aktif();
        $minggu = $request->get('minggu', now()->startOfWeek()->format('Y-m-d'));
        $mulai  = \Carbon\Carbon::parse($minggu)->startOfWeek();
        $selesai = $mulai->copy()->endOfWeek();

        // Jadwal milik guru ini di TA aktif
        $jadwalList = JadwalPelajaran::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->whereHas('kelas', fn($k) =>
                $k->where('tahun_ajaran_id', $ta->id)
            ))
            ->with(['mataPelajaran', 'kelas.tingkatan'])
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Pertemuan yang sudah dibuat minggu ini
        $pertemuanMingguIni = Pertemuan::where('guru_id', $user->id)
            ->whereBetween('tanggal', [$mulai, $selesai])
            ->get()
            ->keyBy(fn($p) => $p->jadwal_pelajaran_id . '_' . $p->tanggal->format('Y-m-d'));

        // Hitung tanggal untuk setiap jadwal di minggu ini
        $hariMap = ['senin'=>1,'selasa'=>2,'rabu'=>3,'kamis'=>4,'jumat'=>5,'sabtu'=>6];
        $jadwalMingguIni = $jadwalList->map(function ($jadwal) use ($mulai, $pertemuanMingguIni, $hariMap) {
            $offset  = ($hariMap[$jadwal->hari] ?? 1) - 1;
            $tanggal = $mulai->copy()->addDays($offset);
            $key     = $jadwal->id . '_' . $tanggal->format('Y-m-d');

            return [
                'jadwal'         => $jadwal,
                'tanggal'        => $tanggal,
                'sudah_presensi' => isset($pertemuanMingguIni[$key]),
                'pertemuan'      => $pertemuanMingguIni[$key] ?? null,
            ];
        });

        // Riwayat pertemuan (10 terakhir)
        $riwayat = Pertemuan::where('guru_id', $user->id)
            ->with(['mataPelajaran', 'kelas', 'presensiKbm'])
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        return view('presensi-kbm.index', compact(
            'jadwalMingguIni', 'riwayat', 'minggu', 'mulai', 'selesai'
        ));
    }

    /**
     * Form input presensi untuk satu jadwal
     */
    public function create(JadwalPelajaran $jadwal)
    {
        // Pastikan jadwal ini milik guru yang login
        abort_if($jadwal->guru_id !== Auth::id(), 403);

        $ta = TahunAjaran::aktif();

        // Daftar santri di kelas ini
        $santriList = SantriKelas::where('kelas_id', $jadwal->kelas_id)
            ->where('status', 'aktif')
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('santri')
            ->get()
            ->sortBy('santri.nama_lengkap');

        // Hitung pertemuan ke-n
        $pertemuanKe = Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)->count() + 1;

        // Cek sudah ada pertemuan hari ini?
        $sudahAda = Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($sudahAda) {
            return redirect()->route('guru.presensi.show', $sudahAda)
                ->with('error', 'Presensi untuk jadwal ini hari ini sudah diinput.');
        }

        $jadwal->load(['mataPelajaran', 'kelas']);

        return view('presensi-kbm.create', compact('jadwal', 'santriList', 'pertemuanKe'));
    }

    /**
     * Simpan pertemuan + presensi semua santri
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal'             => ['required', 'date', 'before_or_equal:today'],
            'jam_mulai'           => ['required', 'date_format:H:i'],
            'jam_selesai'         => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'topik'               => ['nullable', 'string', 'max:200'],
            'materi'              => ['nullable', 'string', 'max:1000'],
            'catatan_guru'        => ['nullable', 'string', 'max:500'],
            'presensi'            => ['required', 'array', 'min:1'],
            'presensi.*.santri_id'=> ['required', 'exists:santri,id'],
            'presensi.*.status'   => ['required', 'in:hadir,sakit,izin,alpa'],
            'presensi.*.keterangan'=> ['nullable', 'string', 'max:200'],
        ], [
            'presensi.required'   => 'Data presensi santri wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $jadwal = JadwalPelajaran::findOrFail($request->jadwal_pelajaran_id);
        abort_if($jadwal->guru_id !== Auth::id(), 403);

        // Cek duplikat
        $exists = Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();
        abort_if($exists, 422, 'Presensi tanggal ini sudah diinput.');

        $pertemuan = DB::transaction(function () use ($request, $jadwal) {
            $pertemuanKe = Pertemuan::where('jadwal_pelajaran_id', $jadwal->id)->count() + 1;

            $pertemuan = Pertemuan::create([
                'jadwal_pelajaran_id' => $jadwal->id,
                'guru_id'             => Auth::id(),
                'kelas_id'            => $jadwal->kelas_id,
                'mata_pelajaran_id'   => $jadwal->mata_pelajaran_id,
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
            'mataPelajaran', 'kelas',
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
            'presensi.*.keterangan'=> ['nullable', 'string', 'max:200'],
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
}
