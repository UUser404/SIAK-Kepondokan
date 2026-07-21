<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbPendaftar;
use App\Models\PpdbPeriode;
use App\Models\Santri;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PpdbController extends Controller
{
    // ==========================================
    // PERIODE PPDB
    // ==========================================

    public function index(Request $request)
    {
        $periodeList = PpdbPeriode::with('tahunAjaran')
            ->withCount([
                'pendaftar as total_pendaftar',
                'pendaftar as menunggu'   => fn($q) => $q->where('status', 'menunggu'),
                'pendaftar as diterima'   => fn($q) => $q->where('status', 'diterima'),
                'pendaftar as ditolak'    => fn($q) => $q->where('status', 'ditolak'),
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('ppdb.index', compact('periodeList'));
    }

    public function createPeriode()
    {
        $tahunAjaranList = TahunAjaran::orderByDesc('nama')->get();
        return view('ppdb.create-periode', compact('tahunAjaranList'));
    }

    public function storePeriode(Request $request)
    {
        $request->validate([
            'nama'            => ['required', 'string', 'max:100'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'tanggal_buka'    => ['required', 'date'],
            'tanggal_tutup'   => ['required', 'date', 'after:tanggal_buka'],
            'kuota'           => ['required', 'integer', 'min:1'],
            'persyaratan'     => ['nullable', 'string'],
        ]);

        $periode = PpdbPeriode::create($request->only(
            'nama','tahun_ajaran_id','tanggal_buka','tanggal_tutup','kuota','persyaratan'
        ) + ['is_active' => false]);

        ActivityLogService::logCreate($periode);

        return redirect()->route('admin.ppdb.index')
            ->with('success', 'Periode PPDB berhasil dibuat.');
    }

    public function aktifkanPeriode(PpdbPeriode $periode)
    {
        DB::transaction(function () use ($periode) {
            PpdbPeriode::where('is_active', true)->update(['is_active' => false]);
            $periode->update(['is_active' => true]);
        });

        return back()->with('success', "Periode {$periode->nama} diaktifkan.");
    }

    // ==========================================
    // PENDAFTAR
    // ==========================================

    public function showPendaftar(Request $request, ?PpdbPeriode $periode = null)
    {
        $periodeAktif = $periode ?? PpdbPeriode::where('is_active', true)->first();
        $periodeList  = PpdbPeriode::orderByDesc('created_at')->get();

        $query = PpdbPendaftar::with('diverifikasiOleh')
            ->when($periodeAktif, fn($q) => $q->where('ppdb_periode_id', $periodeAktif->id))
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('nomor_daftar', 'like', "%{$request->search}%")
                  ->orWhere('no_hp_wali', 'like', "%{$request->search}%");
            });
        }

        $pendaftar = $query->paginate(20)->withQueryString();

        $stats = $periodeAktif ? [
            'total'      => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->count(),
            'menunggu'   => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status','menunggu')->count(),
            'verifikasi' => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status','verifikasi')->count(),
            'diterima'   => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status','diterima')->count(),
            'ditolak'    => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status','ditolak')->count(),
            'sisa_kuota' => $periodeAktif->kuota - PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status','diterima')->count(),
        ] : [];

        return view('ppdb.pendaftar', compact(
            'pendaftar', 'periodeAktif', 'periodeList', 'stats'
        ));
    }

    public function showDetail(PpdbPendaftar $pendaftar)
    {
        return view('ppdb.detail', compact('pendaftar'));
    }

    public function verifikasi(PpdbPendaftar $pendaftar)
    {
        abort_if($pendaftar->status !== 'menunggu', 422, 'Status tidak valid.');

        $pendaftar->update([
            'status'              => 'verifikasi',
            'tanggal_verifikasi'  => now(),
            'diverifikasi_oleh'   => Auth::id(),
        ]);

        ActivityLogService::log('ppdb.verifikasi', $pendaftar);

        return back()->with('success', 'Berkas pendaftar sedang diverifikasi.');
    }

    public function terima(Request $request, PpdbPendaftar $pendaftar)
    {
        abort_if(!in_array($pendaftar->status, ['menunggu','verifikasi']), 422);

        $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:300'],
        ]);

        $pendaftar->update([
            'status'              => 'diterima',
            'catatan_admin'       => $request->catatan_admin,
            'tanggal_verifikasi'  => now(),
            'diverifikasi_oleh'   => Auth::id(),
        ]);

        ActivityLogService::log('ppdb.diterima', $pendaftar);

        return back()->with('success', 'Pendaftar diterima. Silakan konversi ke data santri.');
    }

    public function tolak(Request $request, PpdbPendaftar $pendaftar)
    {
        $request->validate([
            'catatan_admin' => ['required', 'string', 'max:300'],
        ]);

        $pendaftar->update([
            'status'              => 'ditolak',
            'catatan_admin'       => $request->catatan_admin,
            'tanggal_verifikasi'  => now(),
            'diverifikasi_oleh'   => Auth::id(),
        ]);

        ActivityLogService::log('ppdb.ditolak', $pendaftar);

        return back()->with('success', 'Pendaftar ditolak.');
    }

    /**
     * Konversi pendaftar diterima → santri baru
     */
    public function konversiKeSantri(PpdbPendaftar $pendaftar)
    {
        abort_if($pendaftar->status !== 'diterima', 422, 'Hanya pendaftar berstatus diterima yang bisa dikonversi.');
        abort_if($pendaftar->santri_id !== null, 422, 'Pendaftar sudah dikonversi.');

        // Dibuat DI LUAR transaksi supaya bisa tetap dibawa ke redirect
        // walaupun transaksinya sendiri cuma peduli data tersimpan.
        $plainPassword = Str::random(10);

        $santri = DB::transaction(function () use ($pendaftar, $plainPassword) {
            // Generate NIS otomatis: tahun masuk + urutan
            $tahunMasuk = now()->year;
            $urutan     = Santri::whereYear('created_at', $tahunMasuk)->count() + 1;
            $nis        = $tahunMasuk . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            // Buat user account (portal santri).
            // Sengaja NONAKTIF dulu (is_active = false) -- Staf Admin yang
            // aktifkan manual dari halaman Profil Santri setelah kredensial
            // ini disampaikan dengan aman ke santri/wali. Password RANDOM
            // (bukan NIS yang berurutan & bisa ditebak) -- ditampilkan SEKALI
            // ke Staf Admin lewat flash session, tidak pernah disimpan plain.
            $user = User::create([
                'name'      => $pendaftar->nama_lengkap,
                'email'     => strtolower(str_replace(' ', '.', $pendaftar->nama_lengkap))
                               . '.' . $nis . '@santri.alislam.sch.id',
                'password'  => Hash::make($plainPassword),
                'role'      => 'santri',
                'is_active' => false,
            ]);
            $user->assignRole('santri');

            // Buat data santri
            $santri = Santri::create([
                'user_id'       => $user->id,
                'nis'           => $nis,
                'nisn'          => $pendaftar->nisn,
                'nama_lengkap'  => $pendaftar->nama_lengkap,
                'tempat_lahir'  => $pendaftar->tempat_lahir,
                'tanggal_lahir' => $pendaftar->tanggal_lahir,
                'jenis_kelamin' => $pendaftar->jenis_kelamin,
                'alamat'        => $pendaftar->alamat,
                'asal_sekolah'  => $pendaftar->asal_sekolah,
                'nama_ayah'     => $pendaftar->nama_ayah,
                'nama_ibu'      => $pendaftar->nama_ibu,
                'nama_wali'     => $pendaftar->nama_wali,
                'no_hp_wali'    => $pendaftar->no_hp_wali,
                'angkatan'      => now()->year,
                'status'        => 'aktif',
            ]);

            // Link balik ke pendaftar
            $pendaftar->update(['santri_id' => $santri->id]);

            ActivityLogService::log('ppdb.konversi', $santri, [], ['nis' => $nis]);

            return $santri;
        });

        return redirect()->route('admin.santri.show', $santri)
            ->with('success', "Berhasil dikonversi. NIS santri: {$santri->nis}")
            ->with('new_password', $plainPassword);
    }

    // ==========================================
    // Helper
    // ==========================================

    public static function generateNomorDaftar(PpdbPeriode $periode): string
    {
        $prefix = config('siak.ppdb.prefix_nomor', 'PPDB');
        $tahun  = now()->year;
        $urutan = PpdbPendaftar::where('ppdb_periode_id', $periode->id)->count() + 1;
        return "{$prefix}-{$tahun}-" . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }
}
