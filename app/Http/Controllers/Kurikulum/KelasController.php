<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Tingkatan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil tahun ajaran yang sedang aktif
        $ta = TahunAjaran::aktif();

        // Query dasar: Hanya menampilkan kelas di tahun ajaran aktif + hitung santri otomatis
        $query = Kelas::where('tahun_ajaran_id', $ta?->id)
            ->with(['tingkatan', 'waliKelas'])
            ->withCount('santri as jumlah_santri');

        // Fitur Filter Tingkatan (dari Kode Kedua)
        if ($request->filled('tingkatan_id')) {
            $query->where('tingkatan_id', $request->tingkatan_id);
        }

        $kelas = $query->orderBy('nama')->paginate(20)->withQueryString();
        $tingkatanList = Tingkatan::orderBy('urutan')->get();

        return view('kelas.index', compact('kelas', 'tingkatanList', 'ta'));
    }

    public function create()
    {
        $tingkatanList = Tingkatan::orderBy('urutan')->get();
        // Mengambil user dengan role guru/kurikulum yang aktif (Lebih spesifik dibanding Kode Kesatu)
        $guruList = User::whereIn('role', ['guru', 'wakil_kurikulum'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $ta = TahunAjaran::aktif();

        return view('kelas.create', compact('tingkatanList', 'guruList', 'ta'));
    }

    public function store(Request $request)
    {
        $ta = TahunAjaran::aktif();
        abort_if(!$ta, 422, 'Tidak ada tahun ajaran aktif.');

        $validated = $request->validate([
            // Validasi: Nama kelas tidak boleh kembar DI TAHUN AJARAN YANG SAMA
            'nama' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kelas')->where(fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ],
            'tingkatan_id'  => ['required', 'exists:tingkatan,id'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'kapasitas'     => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        // Otomatis menyuntikkan tahun_ajaran_id aktif saat sesudah validasi
        $kelas = Kelas::create(array_merge($validated, ['tahun_ajaran_id' => $ta->id]));
        ActivityLogService::logCreate($kelas);

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', "Kelas {$kelas->nama} berhasil ditambahkan.");
    }

    public function show(Kelas $kelas)
    {
        // Memperbaiki penamaan dari $kela menjadi $kelas agar Route Model Binding bekerja
        $kelas->load(['tingkatan', 'waliKelas', 'tahunAjaran']);
        $santriList = $kelas->santri()->orderBy('nama_lengkap')->get();
        $jadwalList = $kelas->jadwalPelajaran()->with(['mataPelajaran', 'guru'])
            ->orderBy('hari')->orderBy('jam_mulai')->get();

        return view('kelas.show', compact('kelas', 'santriList', 'jadwalList'));
    }

    public function edit(Kelas $kelas)
    {
        $tingkatanList = Tingkatan::orderBy('urutan')->get();
        $guruList = User::whereIn('role', ['guru', 'wakil_kurikulum'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('kelas.edit', compact('kelas', 'tingkatanList', 'guruList'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            // Validasi Unik: Mengabaikan ID kelas ini sendiri agar bisa di-update tanpa dibilang "duplikat"
            'nama' => [
                'required',
                'string',
                'max:20',
                Rule::unique('kelas')->where(fn($q) => $q->where('tahun_ajaran_id', $kelas->tahun_ajaran_id))
                    ->ignore($kelas->id)
            ],
            'tingkatan_id'  => ['required', 'exists:tingkatan,id'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'kapasitas'     => ['required', 'integer', 'min:1', 'max:60'],
        ]);

        $old = $kelas->toArray();
        $kelas->update($validated);
        ActivityLogService::logUpdate($kelas, $old);

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        // Proteksi krusial: Cek apakah ada anak santri di dalam kelas ini sebelum dihapus
        if ($kelas->santri()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kelas yang masih memiliki santri.');
        }

        ActivityLogService::logDelete($kelas);
        $kelas->delete();

        return redirect()->route('kurikulum.kelas.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
