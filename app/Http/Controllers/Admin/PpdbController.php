<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PpdbBerkas;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                'pendaftar as menunggu_berkas'   => fn($q) => $q->where('status_berkas', 'menunggu'),
                'pendaftar as siap_bayar'        => fn($q) => $q->where('status_berkas', 'terverifikasi')
                    ->where('status_pembayaran', 'belum_bayar'),
                'pendaftar as menunggu_verifikasi_bayar' => fn($q) => $q->where('status_pembayaran', 'menunggu_verifikasi'),
                'pendaftar as diterima'          => fn($q) => $q->where('status_akhir', 'diterima'),
                'pendaftar as ditolak'           => fn($q) => $q->where('status_akhir', 'ditolak'),
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
            'nama'               => ['required', 'string', 'max:100'],
            'tahun_ajaran_id'    => ['required', 'exists:tahun_ajaran,id'],
            'tanggal_buka'       => ['required', 'date'],
            'tanggal_tutup'      => ['required', 'date', 'after:tanggal_buka'],
            'kuota'              => ['required', 'integer', 'min:1'],
            'biaya_pendaftaran'  => ['required', 'integer', 'min:0'],
            'info_pembayaran'    => ['nullable', 'string'],
            'persyaratan'        => ['nullable', 'string'],
        ]);

        $periode = PpdbPeriode::create($request->only(
            'nama',
            'tahun_ajaran_id',
            'tanggal_buka',
            'tanggal_tutup',
            'kuota',
            'biaya_pendaftaran',
            'info_pembayaran',
            'persyaratan'
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
    // PENDAFTAR -- daftar & detail
    // ==========================================

    public function showPendaftar(Request $request, ?PpdbPeriode $periode = null)
    {
        $periodeAktif = $periode ?? PpdbPeriode::where('is_active', true)->first();
        $periodeList  = PpdbPeriode::orderByDesc('created_at')->get();

        $query = PpdbPendaftar::with('diverifikasiBerkasOleh')
            ->when($periodeAktif, fn($q) => $q->where('ppdb_periode_id', $periodeAktif->id))
            ->orderByDesc('created_at');

        // Filter sekarang berdasarkan 3 dimensi status -- $request->tahap
        // adalah label gabungan yang dipetakan ke kombinasi status_berkas/
        // status_pembayaran/status_akhir yang sesuai, supaya admin tidak
        // perlu paham 3 kolom terpisah buat sekadar filter listing.
        if ($request->filled('tahap')) {
            match ($request->tahap) {
                'menunggu_berkas' => $query->where('status_berkas', 'menunggu'),
                'berkas_ditolak'  => $query->where('status_berkas', 'ditolak'),
                'siap_bayar'      => $query->where('status_berkas', 'terverifikasi')
                    ->where('status_pembayaran', 'belum_bayar'),
                'menunggu_verifikasi_bayar' => $query->where('status_pembayaran', 'menunggu_verifikasi'),
                'diterima'        => $query->where('status_akhir', 'diterima'),
                'ditolak'         => $query->where('status_akhir', 'ditolak'),
                default           => null,
            };
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('nomor_daftar', 'like', "%{$request->search}%")
                    ->orWhere('nik', 'like', "%{$request->search}%")
                    ->orWhere('no_hp_ayah', 'like', "%{$request->search}%");
            });
        }

        $pendaftar = $query->paginate(20)->withQueryString();

        $stats = $periodeAktif ? [
            'total'                     => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->count(),
            'menunggu_berkas'           => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status_berkas', 'menunggu')->count(),
            'siap_bayar'                => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status_berkas', 'terverifikasi')->where('status_pembayaran', 'belum_bayar')->count(),
            'menunggu_verifikasi_bayar' => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status_pembayaran', 'menunggu_verifikasi')->count(),
            'diterima'                  => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status_akhir', 'diterima')->count(),
            'ditolak'                   => PpdbPendaftar::where('ppdb_periode_id', $periodeAktif->id)->where('status_akhir', 'ditolak')->count(),
            'sisa_kuota'                => $periodeAktif->sisa_kuota,
        ] : [];

        return view('ppdb.pendaftar', compact(
            'pendaftar',
            'periodeAktif',
            'periodeList',
            'stats'
        ));
    }

    public function showDetail(PpdbPendaftar $pendaftar)
    {
        $pendaftar->load(['berkas.diverifikasiOleh', 'diverifikasiBerkasOleh', 'diverifikasiPembayaranOleh', 'ppdbPeriode']);

        // Daftar jenis berkas yang WAJIB buat pendaftar ini (beda-beda
        // tergantung jenjang & jalur) -- dipakai di view buat kasih tahu
        // admin berkas apa saja yang seharusnya ada tapi belum di-upload.
        $berkasWajib = $this->berkasWajib($pendaftar);

        return view('ppdb.detail', compact('pendaftar', 'berkasWajib'));
    }

    /**
     * Jenis berkas yang wajib ada, tergantung jenjang & jalur pendaftar ini.
     * SATU-SATUNYA tempat logic ini didefinisikan -- dipakai baik di admin
     * (nentuin apa saja yang perlu dicek) maupun nanti di form publik
     * (nentuin field upload apa saja yang ditampilkan).
     */
    private function berkasWajib(PpdbPendaftar $pendaftar): array
    {
        $wajib = PpdbBerkas::WAJIB_SEMUA;

        if ($pendaftar->jenjang === 'sma') {
            $wajib[] = 'ijazah_skl';
        }
        if ($pendaftar->jalur === 'prestasi') {
            $wajib[] = 'piagam_prestasi';
        }

        return $wajib;
    }

    // ==========================================
    // VERIFIKASI BERKAS (per dokumen)
    // ==========================================

    /**
     * Verifikasi 1 dokumen tertentu (valid / tidak_valid + catatan kalau
     * tidak valid). TIDAK otomatis mengubah status_berkas pendaftar --
     * admin harus eksplisit klik "Selesai Verifikasi Berkas" (lihat
     * selesaiVerifikasiBerkas() di bawah) setelah semua dokumen dicek,
     * supaya tidak ada status yang ke-update prematur di tengah proses
     * cek satu-satu.
     */
    public function verifikasiBerkas(Request $request, PpdbPendaftar $pendaftar, PpdbBerkas $berkas)
    {
        abort_if($berkas->ppdb_pendaftar_id !== $pendaftar->id, 404);
        abort_if($pendaftar->status_akhir !== 'proses', 422, 'Pendaftar ini sudah final diputuskan, tidak bisa ubah verifikasi berkas lagi.');

        $request->validate([
            'status'  => ['required', 'in:valid,tidak_valid'],
            'catatan' => ['nullable', 'string', 'max:300', 'required_if:status,tidak_valid'],
        ]);

        $berkas->update([
            'status'            => $request->status,
            'catatan'           => $request->catatan,
            'diverifikasi_oleh' => Auth::id(),
            'diverifikasi_at'   => now(),
        ]);

        return back()->with('success', "Berkas \"{$berkas->label}\" ditandai " . ($request->status === 'valid' ? 'valid' : 'tidak valid') . '.');
    }

    /**
     * Tutup tahap verifikasi berkas untuk pendaftar ini. Cuma bisa
     * 'terverifikasi' kalau SEMUA berkas wajib sudah diupload DAN semuanya
     * berstatus 'valid' -- kalau ada yang belum diupload/masih menunggu/
     * ada yang tidak_valid, dipaksa jadi 'ditolak' (pendaftar upload ulang
     * yang bermasalah, bukan admin klik tombol tolak/terima subjektif).
     */
    public function selesaiVerifikasiBerkas(Request $request, PpdbPendaftar $pendaftar)
    {
        abort_if($pendaftar->status_akhir !== 'proses', 422, 'Pendaftar ini sudah final diputuskan.');

        $wajib = $this->berkasWajib($pendaftar);
        $berkasAda = $pendaftar->berkas()->whereIn('jenis', $wajib)->get()->keyBy('jenis');

        $semuaValid = collect($wajib)->every(
            fn($jenis) => $berkasAda->has($jenis) && $berkasAda[$jenis]->status === 'valid'
        );

        if ($semuaValid) {
            $pendaftar->update([
                'status_berkas'             => 'terverifikasi',
                'catatan_berkas'            => null,
                'diverifikasi_berkas_oleh'  => Auth::id(),
                'diverifikasi_berkas_at'    => now(),
            ]);
            ActivityLogService::log('ppdb.berkas_terverifikasi', $pendaftar);

            return back()->with('success', 'Berkas dinyatakan lengkap & valid. Pendaftar sekarang bisa lanjut ke tahap pembayaran.');
        }

        $request->validate([
            'catatan_berkas' => ['required', 'string', 'max:500'],
        ], [
            'catatan_berkas.required' => 'Jelaskan berkas apa yang kurang/tidak valid, supaya pendaftar tahu apa yang perlu diperbaiki.',
        ]);

        $pendaftar->update([
            'status_berkas'             => 'ditolak',
            'catatan_berkas'            => $request->catatan_berkas,
            'diverifikasi_berkas_oleh'  => Auth::id(),
            'diverifikasi_berkas_at'    => now(),
        ]);
        ActivityLogService::log('ppdb.berkas_ditolak', $pendaftar);

        return back()->with('success', 'Pendaftar ditandai perlu melengkapi/memperbaiki berkas.');
    }

    // ==========================================
    // VERIFIKASI PEMBAYARAN
    // ==========================================

    /**
     * Verifikasi bukti transfer yang di-upload pendaftar. Kalau 'lunas' --
     * LANGSUNG set status_akhir jadi 'diterima' & generate QR token
     * sekaligus (bukan langkah terpisah) -- begitu pembayaran dikonfirmasi,
     * pendaftar itu sudah pasti diterima, tidak ada keputusan tambahan lagi
     * yang perlu admin buat secara manual setelah ini.
     */
    public function verifikasiPembayaran(Request $request, PpdbPendaftar $pendaftar)
    {
        abort_if($pendaftar->status_akhir !== 'proses', 422, 'Pendaftar ini sudah final diputuskan.');
        abort_if(
            $pendaftar->status_pembayaran !== 'menunggu_verifikasi',
            422,
            'Pendaftar ini belum upload bukti pembayaran, atau sudah diverifikasi sebelumnya.'
        );

        $request->validate([
            'status'              => ['required', 'in:lunas,ditolak'],
            'catatan_pembayaran'  => ['nullable', 'string', 'max:300', 'required_if:status,ditolak'],
        ]);

        DB::transaction(function () use ($request, $pendaftar) {
            $pendaftar->update([
                'status_pembayaran'             => $request->status,
                'catatan_pembayaran'            => $request->catatan_pembayaran,
                'diverifikasi_pembayaran_oleh'  => Auth::id(),
                'diverifikasi_pembayaran_at'    => now(),
            ]);

            if ($request->status === 'lunas') {
                $pendaftar->update(['status_akhir' => 'diterima']);
                $pendaftar->generateQrToken();
                ActivityLogService::log('ppdb.diterima', $pendaftar);
            } else {
                ActivityLogService::log('ppdb.pembayaran_ditolak', $pendaftar);
            }
        });

        return back()->with('success', $request->status === 'lunas'
            ? 'Pembayaran dikonfirmasi. Pendaftar dinyatakan DITERIMA.'
            : 'Bukti pembayaran ditolak, pendaftar perlu upload ulang.');
    }

    // ==========================================
    // KEPUTUSAN AKHIR (di luar alur berkas/pembayaran normal)
    // ==========================================

    /**
     * Tolak final -- buat kasus yang bukan soal berkas/pembayaran (mis.
     * tidak lolos syarat usia, kuota penuh, dsb). BEDA dari berkas ditolak
     * (yang recoverable, pendaftar bisa upload ulang) -- ini status akhir
     * permanen.
     *
     * FIX dari bug lama: versi sebelumnya method tolak() TIDAK ADA guard
     * status sama sekali, jadi bisa dipanggil ke pendaftar yang sudah
     * 'diterima' bahkan yang sudah dikonversi jadi santri sungguhan.
     * Sekarang WAJIB status_akhir masih 'proses'.
     */
    public function tolakAkhir(Request $request, PpdbPendaftar $pendaftar)
    {
        abort_if(
            $pendaftar->status_akhir !== 'proses',
            422,
            'Pendaftar ini sudah final diputuskan sebelumnya, tidak bisa ditolak lagi.'
        );

        $request->validate([
            'catatan_admin' => ['required', 'string', 'max:300'],
        ]);

        $pendaftar->update([
            'status_akhir'  => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
        ]);

        ActivityLogService::log('ppdb.ditolak', $pendaftar);

        return back()->with('success', 'Pendaftar ditolak.');
    }

    // ==========================================
    // KONVERSI KE SANTRI
    // ==========================================

    public function konversiKeSantri(PpdbPendaftar $pendaftar)
    {
        abort_if($pendaftar->status_akhir !== 'diterima', 422, 'Hanya pendaftar berstatus DITERIMA yang bisa dikonversi.');
        abort_if($pendaftar->santri_id !== null, 422, 'Pendaftar sudah dikonversi.');

        $plainPassword = Str::random(10);

        $santri = DB::transaction(function () use ($pendaftar, $plainPassword) {
            $tahunMasuk = now()->year;
            $urutan     = Santri::whereYear('created_at', $tahunMasuk)->count() + 1;
            $nis        = $tahunMasuk . str_pad($urutan, 4, '0', STR_PAD_LEFT);

            $user = User::create([
                'name'      => $pendaftar->nama_lengkap,
                'email'     => strtolower(str_replace(' ', '.', $pendaftar->nama_lengkap))
                    . '.' . $nis . '@santri.alislam.sch.id',
                'password'  => Hash::make($plainPassword),
                'role'      => 'santri',
                'is_active' => false,
            ]);
            $user->assignRole('santri');

            $santri = Santri::create([
                'user_id'       => $user->id,
                'nis'           => $nis,
                'nisn'          => $pendaftar->nisn,
                'nama_lengkap'  => $pendaftar->nama_lengkap,
                'nama_arab'     => $pendaftar->nama_arab,
                'tempat_lahir'  => $pendaftar->tempat_lahir,
                'tanggal_lahir' => $pendaftar->tanggal_lahir,
                'jenis_kelamin' => $pendaftar->jenis_kelamin,
                'alamat'        => $pendaftar->alamat,
                'asal_sekolah'  => $pendaftar->asal_sekolah,
                'nama_ayah'     => $pendaftar->nama_ayah,
                'nama_ibu'      => $pendaftar->nama_ibu,
                'nama_wali'     => $pendaftar->nama_wali,
                'no_hp_wali'    => $pendaftar->no_hp_wali ?? $pendaftar->no_hp_ayah,
                'angkatan'      => now()->year,
                'status'        => 'aktif',
            ]);

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
