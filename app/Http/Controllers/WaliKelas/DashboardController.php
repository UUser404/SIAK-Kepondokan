<?php
// ============================================================
// app/Http/Controllers/WaliKelas/DashboardController.php
// ============================================================
namespace App\Http\Controllers\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ta   = TahunAjaran::aktif();

        $kelasList = $user->waliKelasKelas()
            ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->with('tingkatan')
            ->withCount('santri as jumlah_santri')
            ->get();

        return view('wali-kelas.dashboard', compact('kelasList', 'ta'));
    }
}
