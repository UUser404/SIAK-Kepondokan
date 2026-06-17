<?php
// ============================================================
// app/Http/Controllers/Sysadmin/ActivityLogController.php
// ============================================================
namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(
                fn($q) =>
                $q->where('action', 'like', "%{$request->search}%")
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%"))
            );
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->paginate(30)->withQueryString();

        return view('sysadmin.activity-log', compact('logs'));
    }
}
