<?php
// ============================================================
// app/Http/Controllers/AiAdvisorController.php
// ============================================================
namespace App\Http\Controllers;

use App\Services\AiAdvisorService;
use App\Models\AiConversationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAdvisorController extends Controller
{
    public function __construct(private AiAdvisorService $aiService) {}

    public function index()
    {
        $riwayat = AiConversationLog::where('user_id', Auth::id())
            ->orderByDesc('created_at')->limit(5)->get();
        return view('ai.index', compact('riwayat'));
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message'   => ['required', 'string', 'max:1000'],
            'history'   => ['nullable', 'array'],
            'santri_id' => ['nullable', 'exists:santri,id'],
        ]);

        $response = $this->aiService->chat(
            Auth::user(),
            $request->message,
            $request->history ?? [],
            $request->santri_id
        );

        return response()->json($response);
    }
}
