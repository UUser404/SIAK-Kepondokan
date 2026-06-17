<?php

namespace App\Services;

use App\Models\AiConversationLog;
use App\Models\User;
use App\Models\Santri;
use App\Models\NilaiAkhir;
use App\Models\Pelanggaran;
use App\Models\TahunAjaran;
use App\Models\Pertemuan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAdvisorService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY', '');
        $this->model  = config('siak.ai.groq.model', 'llama-3.3-70b-versatile');
    }

    public function chat(User $user, string $message, array $history = [], ?int $santriId = null): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'API key Groq belum dikonfigurasi.'];
        }

        $startTime = microtime(true);

        try {
            $context      = $this->buildContext($user, $santriId);
            $systemPrompt = $this->buildSystemPrompt($user, $context);
            $response     = $this->callGroq($systemPrompt, $message, $history);
            $ms           = (int) ((microtime(true) - $startTime) * 1000);

            $this->logConversation($user, $message, $response['message'] ?? '', $ms);
            return $response;
        } catch (\Exception $e) {
            Log::error('AiAdvisorService: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.'];
        }
    }

    protected function buildContext(User $user, ?int $santriId = null): array
    {
        $ta   = TahunAjaran::aktif();
        $base = [
            'pondok'       => config('siak.pondok.nama'),
            'tahun_ajaran' => $ta?->nama_lengkap ?? '-',
            'user_role'    => $user->role,
            'user_name'    => $user->name,
        ];

        if (in_array($user->role, ['guru', 'wakil_kurikulum'])) {
            $base['stat'] = [
                'pertemuan_bulan_ini' => Pertemuan::where('guru_id', $user->id)
                    ->whereMonth('tanggal', now()->month)->count(),
            ];
        }

        if ($santriId) {
            $santri = Santri::with(['santriKelas.kelas'])->find($santriId);
            if ($santri) {
                $nilai      = NilaiAkhir::where('santri_id', $santri->id)
                    ->where('tahun_ajaran_id', $ta?->id)->with('mataPelajaran')->get();
                $pelanggaran = Pelanggaran::where('santri_id', $santri->id)
                    ->where('status', 'aktif')->with('kategori')->get();

                $base['santri'] = [
                    'nama'            => $santri->nama_lengkap,
                    'nis'             => $santri->nis,
                    'kelas'           => $santri->santriKelas->where('status', 'aktif')->first()?->kelas?->nama ?? '-',
                    'nilai'           => $nilai->map(fn($n) => [
                        'mapel'   => $n->mataPelajaran->nama,
                        'kkm'     => $n->mataPelajaran->kkm,
                        'nilai'   => $n->nilai_akhir,
                        'predikat' => $n->predikat,
                        'tuntas'  => $n->tuntas,
                    ])->toArray(),
                    'rata_nilai'      => round($nilai->avg('nilai_akhir') ?? 0, 1),
                    'tuntas'          => $nilai->where('tuntas', true)->count(),
                    'total_mapel'     => $nilai->count(),
                    'poin_pelanggaran' => $pelanggaran->sum(fn($p) => $p->kategori->poin),
                ];
            }
        }

        return $base;
    }

    protected function buildSystemPrompt(User $user, array $context): string
    {
        $json   = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $pondok = config('siak.pondok.nama');

        return <<<PROMPT
<IDENTITAS>
Kamu adalah AI Advisor {$pondok}. Bantu {$user->role} bernama {$user->name} menganalisis data akademik dan kesantrian.
</IDENTITAS>

<ATURAN>
1. Jawab HANYA berdasarkan DATA CONTEXT JSON di bawah.
2. Jika data tidak ada: "Data tersebut belum tersedia dalam sistem."
3. JANGAN mengarang angka atau fakta.
4. Bahasa Indonesia yang sopan dan profesional.
5. Fokus: nilai santri, presensi, pelanggaran, kurikulum, strategi pembelajaran.
6. Tolak pertanyaan tidak relevan dengan pendidikan/pesantren.
7. Berikan saran konstruktif berbasis data.
</ATURAN>

<DATA_CONTEXT>
{$json}
</DATA_CONTEXT>

Jawab ringkas, terstruktur, dan berbasis data.
PROMPT;
    }

    protected function callGroq(string $systemPrompt, string $message, array $history): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach ($history as $h) {
            $messages[] = ['role' => $h['role'] === 'user' ? 'user' : 'assistant', 'content' => $h['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $res = Http::timeout(30)
            ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey, 'Content-Type' => 'application/json'])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => $this->model,
                'messages'    => $messages,
                'max_tokens'  => 1024,
                'temperature' => 0.3,
            ]);

        if ($res->successful()) {
            return ['success' => true, 'message' => $res->json('choices.0.message.content') ?? 'Tidak ada respons.'];
        }
        return ['success' => false, 'message' => 'Gagal: ' . ($res->json('error.message') ?? 'Unknown error')];
    }

    protected function logConversation(User $user, string $question, string $answer, int $ms): void
    {
        try {
            AiConversationLog::create([
                'user_id'          => $user->id,
                'session_id'       => session()->getId(),
                'question'         => $question,
                'answer'           => $answer,
                'model_used'       => $this->model,
                'response_time_ms' => $ms,
                'guard_applied'    => false,
            ]);
        } catch (\Exception $e) {
            Log::warning('AI log failed: ' . $e->getMessage());
        }
    }
}
