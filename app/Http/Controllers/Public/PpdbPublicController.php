<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Admin\PpdbController;
use App\Http\Controllers\Controller;
use App\Models\PpdbBerkas;
use App\Models\PpdbPendaftar;
use App\Models\PpdbPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PpdbPublicController extends Controller
{
    /**
     * Landing page PPDB publik
     */
    public function index()
    {
        $periode = $this->periodeAktif();
        $isOpen  = $periode !== null;

        $stats = $periode ? [
            'total_daftar' => PpdbPendaftar::where('ppdb_periode_id', $periode->id)->count(),
            'sisa_kuota'   => $periode->sisa_kuota,
        ] : [];

        return view('public.ppdb-landing', compact('periode', 'isOpen', 'stats'));
    }

    /**
     * Form pendaftaran
     */
    public function create()
    {
        $periode = $this->periodeAktif();
        abort_if(!$periode, 404, 'PPDB sedang tidak dibuka.');

        return view('public.ppdb-form', compact('periode'));
    }

    /**
     * Simpan pendaftaran -- data diri lengkap dulu di sini. Upload berkas
     * dilakukan di step TERPISAH setelah ini (lihat uploadBerkas()), supaya
     * kalau ada 1 file gagal upload (koneksi putus, dsb), data yang sudah
     * diisi tidak ikut hilang -- pendaftar bisa balik lagi lewat halaman
     * cek status buat lanjutin upload berkas yang kurang.
     */
    public function store(Request $request)
    {
        $periode = $this->periodeAktif();
        abort_if(!$periode, 404, 'PPDB sedang tidak dibuka.');

        $validated = $request->validate([
            // Jalur
            'jalur'             => ['required', Rule::in(['reguler', 'prestasi'])],
            'bidang_prestasi'   => ['required_if:jalur,prestasi', 'nullable', 'string', 'max:100'],
            'tingkat_prestasi'  => ['required_if:jalur,prestasi', 'nullable', 'string', 'max:50'],
            'tahun_prestasi'    => ['required_if:jalur,prestasi', 'nullable', 'string', 'max:10'],
            // Data calon siswa
            'jenjang'           => ['required', Rule::in(['smp', 'sma'])],
            'nama_lengkap'      => ['required', 'string', 'max:100'],
            'nama_arab'         => ['nullable', 'string', 'max:150'],
            'nik'               => ['required', 'digits:16', Rule::unique('ppdb_pendaftar', 'nik')->where('ppdb_periode_id', $periode->id)],
            'nisn'              => ['nullable', 'digits:10'],
            'tempat_lahir'      => ['nullable', 'string', 'max:50'],
            'tanggal_lahir'     => ['required', 'date', 'before:today'],
            'jenis_kelamin'     => ['required', Rule::in(['L', 'P'])],
            'anak_ke'           => ['nullable', 'integer', 'min:1', 'max:20'],
            'dari_bersaudara'   => ['nullable', 'integer', 'min:1', 'max:20'],
            'golongan_darah'    => ['nullable', 'string', 'max:3'],
            'asal_sekolah'      => ['nullable', 'string', 'max:100'],
            'asal_provinsi'     => ['required', 'string', 'max:50'],
            'alamat'            => ['nullable', 'string', 'max:500'],
            // Kesehatan
            'riwayat_penyakit'  => ['nullable', 'string', 'max:500'],
            'alergi_makanan'    => ['nullable', 'string', 'max:300'],
            'alergi_obat'       => ['nullable', 'string', 'max:300'],
            'obat_rutin'        => ['nullable', 'string', 'max:300'],
            // Keluarga
            'no_kk'             => ['nullable', 'digits:16'],
            'nama_ayah'         => ['nullable', 'string', 'max:100'],
            'nik_ayah'          => ['nullable', 'digits:16'],
            'pekerjaan_ayah'    => ['nullable', 'string', 'max:100'],
            'penghasilan_ayah'  => ['nullable', 'integer', 'min:0'],
            'pendidikan_ayah'   => ['nullable', 'string', 'max:50'],
            'no_hp_ayah'        => ['nullable', 'string', 'max:20'],
            'nama_ibu'          => ['nullable', 'string', 'max:100'],
            'nik_ibu'           => ['nullable', 'digits:16'],
            'pekerjaan_ibu'     => ['nullable', 'string', 'max:100'],
            'penghasilan_ibu'   => ['nullable', 'integer', 'min:0'],
            'pendidikan_ibu'    => ['nullable', 'string', 'max:50'],
            'no_hp_ibu'         => ['nullable', 'string', 'max:20'],
            'nama_wali'         => ['nullable', 'string', 'max:100'],
            'hubungan_wali'     => ['nullable', 'string', 'max:50'],
            'nik_wali'          => ['nullable', 'digits:16'],
            'no_hp_wali'        => ['nullable', 'string', 'max:20'],
            'alamat_wali'       => ['nullable', 'string', 'max:500'],
            'nama_kontak_darurat'     => ['required', 'string', 'max:100'],
            'hubungan_kontak_darurat' => ['required', 'string', 'max:50'],
            'no_hp_kontak_darurat'    => ['required', 'string', 'max:20'],
            // Riwayat pendidikan agama
            'pernah_tpa'            => ['nullable', 'boolean'],
            'pernah_mondok'         => ['nullable', 'boolean'],
            'nama_pesantren_asal'   => ['required_if:pernah_mondok,1', 'nullable', 'string', 'max:150'],
            'estimasi_hafalan_juz'  => ['nullable', 'integer', 'min:0', 'max:30'],
            // Sumber informasi
            'sumber_informasi'          => ['required', 'string', 'max:50'],
            'sumber_informasi_lainnya'  => ['required_if:sumber_informasi,lainnya', 'nullable', 'string', 'max:150'],
        ], [
            'nik.unique'                  => 'NIK ini sudah terdaftar di periode PPDB ini. Kalau ini bukan pendaftaran Anda, hubungi panitia PPDB.',
            'nama_kontak_darurat.required' => 'Kontak darurat wajib diisi.',
        ]);

        $nomorDaftar = PpdbController::generateNomorDaftar($periode);

        $pendaftar = PpdbPendaftar::create(array_merge($validated, [
            'ppdb_periode_id' => $periode->id,
            'nomor_daftar'    => $nomorDaftar,
            'pernah_tpa'      => $request->boolean('pernah_tpa'),
            'pernah_mondok'   => $request->boolean('pernah_mondok'),
        ]));

        return redirect()->route('ppdb.public.berkas', $pendaftar->nomor_daftar)
            ->with('success', "Data berhasil disimpan! Nomor daftar Anda: {$nomorDaftar}. Sekarang lengkapi upload berkas persyaratan.");
    }

    /**
     * Form upload berkas -- step terpisah setelah data diri tersimpan.
     * Diakses lewat nomor_daftar (bukan NIK) -- nomor ini cuma dikasih tau
     * SEKALI lewat halaman sukses submit form, jadi cukup aman dipakai
     * sebagai "kunci sesi" buat lanjutin upload tanpa perlu akun/login.
     */
    public function formBerkas(string $nomor_daftar)
    {
        $pendaftar = PpdbPendaftar::where('nomor_daftar', $nomor_daftar)->firstOrFail();
        $berkasWajib = $this->berkasWajib($pendaftar);
        $berkasAda   = $pendaftar->berkas()->get()->keyBy('jenis');

        return view('public.ppdb-berkas', compact('pendaftar', 'berkasWajib', 'berkasAda'));
    }

    public function uploadBerkas(Request $request, string $nomor_daftar)
    {
        $pendaftar = PpdbPendaftar::where('nomor_daftar', $nomor_daftar)->firstOrFail();

        abort_if($pendaftar->status_akhir !== 'proses', 422, 'Pendaftaran ini sudah final diputuskan, tidak bisa upload berkas lagi.');

        $jenisValid = array_keys(PpdbBerkas::LABEL);

        $request->validate([
            'jenis' => ['required', Rule::in($jenisValid)],
            'file'  => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $path = $request->file('file')->store("ppdb/{$pendaftar->id}", 'private');

        // updateOrCreate -- upload ulang jenis yang sama TIMPA baris lama
        // (bukan numpuk baris baru), konsisten sama unique constraint di
        // migration (ppdb_pendaftar_id + jenis). Status balik ke 'menunggu'
        // setiap kali di-upload ulang -- kalau sebelumnya sempat ditandai
        // tidak_valid oleh admin, upload baru wajib dicek ulang dari awal.
        PpdbBerkas::updateOrCreate(
            ['ppdb_pendaftar_id' => $pendaftar->id, 'jenis' => $request->jenis],
            [
                'file_path' => $path,
                'status' => 'menunggu',
                'catatan' => null,
                'diverifikasi_oleh' => null,
                'diverifikasi_at' => null
            ]
        );

        return back()->with('success', PpdbBerkas::LABEL[$request->jenis] . ' berhasil diupload.');
    }

    private function berkasWajib(PpdbPendaftar $pendaftar): array
    {
        $wajib = PpdbBerkas::WAJIB_SEMUA;
        if ($pendaftar->jenjang === 'sma') $wajib[] = 'ijazah_skl';
        if ($pendaftar->jalur === 'prestasi') $wajib[] = 'piagam_prestasi';
        return $wajib;
    }

    // ==========================================
    // CEK STATUS -- pakai NIK + Tanggal Lahir, BUKAN nomor_daftar
    // (nomor_daftar berurutan & gampang ditebak -- lihat catatan di
    // migration soal kenapa ini diganti).
    // ==========================================

    public function cekStatusForm()
    {
        return view('public.ppdb-cek-status-form');
    }

    /**
     * Rate limit 5x percobaan per 1 menit, di-key per kombinasi
     * IP+session -- cegah brute-force NIK otomatis. Pesan error SENGAJA
     * generik ("data tidak ditemukan") baik buat NIK salah MAUPUN tanggal
     * lahir salah -- jangan kasih tahu mana yang salah duluan, itu bocorin
     * info ke penyerang soal NIK mana yang valid ada di database.
     */
    public function cekStatus(Request $request)
    {
        $request->validate([
            'nik'           => ['required', 'digits:16'],
            'tanggal_lahir' => ['required', 'date'],
        ]);

        $key = 'ppdb-cek-status:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'nik' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
            ]);
        }

        $pendaftar = PpdbPendaftar::where('nik', $request->nik)
            ->whereDate('tanggal_lahir', $request->tanggal_lahir)
            ->with(['berkas', 'ppdbPeriode'])
            ->orderByDesc('created_at') // kalau pernah daftar >1 periode, ambil yang terbaru
            ->first();

        if (!$pendaftar) {
            RateLimiter::hit($key, 60); // cuma hit counter kalau BENERAN gagal
            return back()->withErrors(['nik' => 'Data tidak ditemukan. Pastikan NIK & tanggal lahir sesuai saat pendaftaran.']);
        }

        RateLimiter::clear($key); // berhasil -- reset supaya tidak kehabisan jatah gara-gara 1x sukses

        $berkasWajib = $this->berkasWajib($pendaftar);

        return view('public.ppdb-status', compact('pendaftar', 'berkasWajib'));
    }

    /**
     * Upload bukti pembayaran -- cuma bisa kalau status_berkas sudah
     * terverifikasi (siapUntukBayar()). Diakses dari halaman status
     * (butuh NIK+tanggal lahir lagi buat konfirmasi, BUKAN nomor_daftar
     * publik seperti upload berkas awal -- ini tahap lebih sensitif
     * karena menyangkut uang, jadi pakai kunci yang sama kuatnya dengan
     * cek status).
     */
    public function uploadBukti(Request $request)
    {
        $request->validate([
            'nik'           => ['required', 'digits:16'],
            'tanggal_lahir' => ['required', 'date'],
            'bukti'         => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $pendaftar = PpdbPendaftar::where('nik', $request->nik)
            ->whereDate('tanggal_lahir', $request->tanggal_lahir)
            ->first();

        abort_if(!$pendaftar, 404);
        abort_if(!$pendaftar->siapUntukBayar(), 422, 'Belum bisa upload bukti pembayaran pada tahap ini.');

        $path = $request->file('bukti')->store("ppdb/{$pendaftar->id}/pembayaran", 'private');

        $pendaftar->update([
            'bukti_pembayaran'  => $path,
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diupload, menunggu diverifikasi panitia.');
    }

    private function periodeAktif(): ?PpdbPeriode
    {
        return PpdbPeriode::where('is_active', true)
            ->where('tanggal_buka', '<=', today())
            ->where('tanggal_tutup', '>=', today())
            ->first();
    }
}
