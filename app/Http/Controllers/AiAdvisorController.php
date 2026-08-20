<?php
// ============================================================
// app/Http/Controllers/AiAdvisorController.php
// ============================================================
namespace App\Http\Controllers;

use App\Services\AiAdvisorService;
use App\Models\AiConversationLog;
use App\Models\PenugasanMengajar;
use App\Models\Santri;
use App\Models\SantriKelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAdvisorController extends Controller
{
    public function __construct(private AiAdvisorService $aiService) {}

    /**
     * Role yang boleh akses data SEMUA santri tanpa batasan (kesantrian/
     * kurikulum/admin punya cakupan lintas kelas secara jabatan). Guru
     * SENGAJA tidak dimasukkan -- dia cuma boleh akses santri yang benar
     * dia ajar, lihat scopeSantriUntukUser() & boleAksesSantri() di bawah.
     */
    private const ROLE_AKSES_PENUH = ['wakil_kurikulum', 'kesantrian', 'admin', 'sysadmin'];

    /**
     * Peta role -> prefix grup route, dipakai buat hitung nama route
     * "{prefix}.ai.chat" yang benar sesuai role user yang login. Ini
     * GANTIKAN ternary rapuh yang sebelumnya ada di JS (cuma cover role
     * 'guru' vs fallback 'kurikulum' -- 3 role lain, kesantrian/admin/
     * sysadmin, kalau kirim chat bakal nyasar ke route yang salah/tidak
     * ada). Sekarang dihitung sekali di sini (PHP, 1 sumber kebenaran),
     * dikirim ke view sebagai $chatRoute, JS tinggal pakai APA ADANYA.
     *
     * PENTING kalau nambah role baru yang boleh akses AI Advisor: tambah
     * di sini JUGA tambah grup route "{prefix}.ai.index"/"{prefix}.ai.chat"
     * di routes/web.php buat prefix itu -- 2 tempat ini WAJIB SINKRON.
     */
    private const ROLE_ROUTE_PREFIX = [
        'guru'            => 'guru',
        'wakil_kurikulum' => 'kurikulum',
        'kesantrian'      => 'kesantrian',
        'admin'           => 'admin',
        'sysadmin'        => 'sysadmin',
    ];

    public function index()
    {
        $user    = Auth::user();
        $riwayat = AiConversationLog::where('user_id', $user->id)
            ->orderByDesc('created_at')->limit(5)->get();

        $santriList = $this->scopeSantriUntukUser($user);

        $routePrefix = self::ROLE_ROUTE_PREFIX[$user->role] ?? 'guru';
        $chatRoute   = route("{$routePrefix}.ai.chat");

        return view('ai.index', compact('riwayat', 'santriList', 'chatRoute'));
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message'   => ['required', 'string', 'max:1000'],
            'history'   => ['nullable', 'array'],
            'santri_id' => ['nullable', 'exists:santri,id'],
        ]);

        // Guard WAJIB di backend -- jangan andalkan dropdown di view sudah
        // di-scope dengan benar. Kalau santri_id dikirim tapi user (guru)
        // tidak benar mengajar santri itu, tolak sebelum data-nya sempat
        // masuk ke context AI. Pola sama seperti authorizeSantri()/
        // authorizeKelas()/guruBolehAkses() di RaporController/
        // LegerController/PresensiController.
        if ($request->filled('santri_id') && !$this->boleAksesSantri(Auth::user(), (int) $request->santri_id)) {
            abort(403, 'Anda tidak berwenang mengakses data santri ini.');
        }

        $response = $this->aiService->chat(
            Auth::user(),
            $request->message,
            $request->history ?? [],
            $request->santri_id
        );

        return response()->json($response);
    }

    /**
     * Daftar santri untuk dropdown "Analisis santri spesifik" -- guru CUMA
     * lihat santri di kelas yang benar dia ajar (Penugasan Mengajar, TA
     * aktif). Role di ROLE_AKSES_PENUH lihat semua santri aktif. Role lain
     * (mis. santri/wali sendiri kalau suatu saat login ke sini) dapat
     * collection kosong -- dropdown-nya otomatis tidak tampil apa-apa.
     */
    private function scopeSantriUntukUser($user)
    {
        if (in_array($user->role, self::ROLE_AKSES_PENUH, true)) {
            return Santri::aktif()->orderBy('nama_lengkap')->get();
        }

        if ($user->role !== 'guru') {
            return collect();
        }

        $ta = TahunAjaran::aktif();
        $kelasIds = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->pluck('kelas_id')
            ->unique();

        return Santri::aktif()
            ->whereHas('santriKelas', function ($q) use ($kelasIds, $ta) {
                $q->whereIn('kelas_id', $kelasIds)
                    ->where('status', 'aktif')
                    ->when($ta, fn($q2) => $q2->where('tahun_ajaran_id', $ta->id));
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    /**
     * Guard backend -- versi query langsung (bukan lewat relasi Eloquent
     * yang belum tentu namanya persis), supaya tidak bergantung pada
     * asumsi nama relasi yang belum sempat diverifikasi ke model Kelas.
     */
    private function boleAksesSantri($user, int $santriId): bool
    {
        if (in_array($user->role, self::ROLE_AKSES_PENUH, true)) {
            return true;
        }

        if ($user->role !== 'guru') {
            return false;
        }

        $ta = TahunAjaran::aktif();
        $kelasIds = PenugasanMengajar::where('guru_id', $user->id)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->pluck('kelas_id');

        return SantriKelas::where('santri_id', $santriId)
            ->where('status', 'aktif')
            ->whereIn('kelas_id', $kelasIds)
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->exists();
    }
}
