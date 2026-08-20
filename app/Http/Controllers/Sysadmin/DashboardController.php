<?php
// ============================================================
// app/Http/Controllers/Sysadmin/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AiConversationLog;
use App\Models\TahunAjaran;

class DashboardController extends Controller
{
    /**
     * Label tampilan per role -- dipakai buat breakdown user di dashboard.
     * Urutan array ini SENGAJA sesuai hierarki (paling atas paling luas
     * kewenangannya), bukan alfabetis, biar enak dibaca sysadmin sekilas.
     */
    private const ROLE_LABEL = [
        'sysadmin'        => 'Sysadmin',
        'mudir'           => 'Mudir',
        'wakil_kurikulum' => 'Wakil Kurikulum',
        'kesantrian'      => 'Kesantrian',
        'admin'           => 'Staf Admin',
        'guru'            => 'Guru',
    ];

    public function index()
    {
        $totalUsers   = User::count();
        $activeUsers  = User::where('is_active', true)->count();
        $totalLogs    = ActivityLog::whereDate('created_at', today())->count();
        $totalAiChats = AiConversationLog::whereDate('created_at', today())->count();

        // Breakdown user per role -- sebelumnya cuma "Total User: 47" polos,
        // sysadmin yang mau audit akses harus buka halaman User dulu buat
        // tahu komposisinya.
        $userPerRole = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        // Peringatan Tahun Ajaran aktif -- PENTING khusus buat sistem ini:
        // puluhan controller (Rapor, Leger, Nilai, Presensi, dst) pakai
        // abort_if(!$ta, ...) yang diam-diam menghentikan fitur itu kalau
        // tidak ada TA aktif. Sysadmin harus jadi yang PERTAMA tahu kalau
        // ini kosong, bukan nunggu laporan user "fitur X tidak bisa dipakai".
        $taAktif = TahunAjaran::aktif();

        $logsHariIni = ActivityLog::with('user')
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboards.sysadmin', compact(
            'totalUsers',
            'activeUsers',
            'totalLogs',
            'totalAiChats',
            'userPerRole',
            'taAktif',
            'logsHariIni'
        ))->with('roleLabels', self::ROLE_LABEL);
    }
}
