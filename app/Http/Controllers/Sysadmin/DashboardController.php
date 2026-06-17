<?php
// ============================================================
// app/Http/Controllers/Sysadmin/DashboardController.php
// ============================================================
namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\AiConversationLog;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers   = User::count();
        $activeUsers  = User::where('is_active', true)->count();
        $totalLogs    = ActivityLog::whereDate('created_at', today())->count();
        $totalAiChats = AiConversationLog::whereDate('created_at', today())->count();

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
            'logsHariIni'
        ));
    }
}
