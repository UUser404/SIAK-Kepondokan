<?php
// ============================================================
// app/Http/Controllers/Admin/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Models\TenagaPendidik;
use App\Models\PpdbPendaftar;
use App\Models\SuratKeluar;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSantri   = Santri::aktif()->count();
        $totalPendidik = TenagaPendidik::count();
        $totalPpdb     = PpdbPendaftar::where('status', 'menunggu')->count();
        $totalSurat    = SuratKeluar::where('status', 'diterbitkan')
            ->whereMonth('created_at', now()->month)->count();

        $ppdbTerbaru = PpdbPendaftar::orderByDesc('created_at')->limit(5)->get();

        return view('dashboards.admin', compact(
            'totalSantri',
            'totalPendidik',
            'totalPpdb',
            'totalSurat',
            'ppdbTerbaru'
        ));
    }
}
