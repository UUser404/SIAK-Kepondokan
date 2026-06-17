<?php
// ============================================================
// app/Http/Controllers/Sysadmin/AiLogController.php
// ============================================================
namespace App\Http\Controllers\Sysadmin;

use App\Http\Controllers\Controller;
use App\Models\AiConversationLog;
use Illuminate\Http\Request;

class AiLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AiConversationLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(30);

        $stats = [
            'total'           => AiConversationLog::count(),
            'hari_ini'        => AiConversationLog::whereDate('created_at', today())->count(),
            'avg_response_ms' => round(AiConversationLog::avg('response_time_ms') ?? 0),
        ];

        return view('sysadmin.ai-log', compact('logs', 'stats'));
    }
}
