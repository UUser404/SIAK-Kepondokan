<?php

namespace App\Services;

use App\Models\AiConversationLog;
use App\Models\User;
use App\Models\Santri;
use App\Models\Kelas;
use App\Models\NilaiAkhir;
use App\Models\Pelanggaran;
use App\Models\PenugasanMengajar;
use App\Models\PresensiKbm;
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

                // KKM per tingkatan (kkmUntukTingkatan), BUKAN kolom mata_pelajaran.kkm
                // global -- sama seperti PenilaianService/RaporController/NilaiController.
                // Lihat DEVELOPER_GUIDE.md poin 17 kalau lupa kenapa ini penting.
                $kelasAktifSantri = $santri->santriKelas->where('status', 'aktif')->first()?->kelas;
                $tingkatanId      = $kelasAktifSantri?->tingkatan_id;

                $base['santri'] = [
                    'nama'            => $santri->nama_lengkap,
                    'nis'             => $santri->nis,
                    'kelas'           => $kelasAktifSantri?->nama ?? '-',
                    'nilai'           => $nilai->map(fn($n) => [
                        'mapel'   => $n->mataPelajaran->nama,
                        'kkm'     => $n->mataPelajaran->kkmUntukTingkatan($tingkatanId),
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
        } else {
            // Tidak ada santri spesifik dipilih -- kasih ringkasan AGREGAT per
            // kelas (bukan detail 1-per-1 semua santri, supaya context tidak
            // membengkak) supaya AI beneran bisa jawab pertanyaan umum kayak
            // "santri dengan nilai terendah", "tren kehadiran kelas", dst
            // (sebelumnya cuma dapat 1 angka jumlah pertemuan doang, AI
            // secara struktural tidak mungkin jawab pertanyaan semacam itu).
            $ringkasanKelas = $this->buildKelasSummary($user, $ta);
            if (!empty($ringkasanKelas)) {
                $base['ringkasan_kelas'] = $ringkasanKelas;
            }
        }

        return $base;
    }

    /**
     * Ringkasan agregat per kelas yang relevan untuk user ini -- guru cuma
     * kelas yang benar dia ajar (Penugasan Mengajar), role di
     * ROLE_AKSES_PENUH_LOKAL semua kelas TA aktif. SENGAJA cuma agregat
     * (rata-rata, jumlah belum tuntas, top-3 tertinggi/terendah) BUKAN
     * daftar lengkap semua santri per kelas -- kalau role-nya luas
     * (wakil_kurikulum dkk, bisa puluhan kelas), dump semua santri mentah
     * bisa bikin context membengkak jauh melebihi batas token yang wajar.
     * Dibatasi maks 30 kelas (safety cap) -- kalau ada yang butuh lebih,
     * lebih baik pakai fitur "analisis santri spesifik" per orang.
     */
    protected function buildKelasSummary(User $user, ?TahunAjaran $ta): array
    {
        $roleAksesPenuh = ['wakil_kurikulum', 'kesantrian', 'admin', 'sysadmin'];

        $kelasQuery = Kelas::when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id));

        if ($user->role === 'guru') {
            $kelasIds = PenugasanMengajar::where('guru_id', $user->id)
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->pluck('kelas_id')->unique();
            $kelasQuery->whereIn('id', $kelasIds);
        } elseif (!in_array($user->role, $roleAksesPenuh, true)) {
            return [];
        }
        // role di $roleAksesPenuh: tidak difilter, semua kelas TA aktif.

        $kelasList = $kelasQuery->with('santri')->take(30)->get();

        $ringkasan = [];
        foreach ($kelasList as $kelas) {
            $santriIds = $kelas->santri->pluck('id');
            if ($santriIds->isEmpty()) {
                continue;
            }

            $nilaiAkhir = NilaiAkhir::whereIn('santri_id', $santriIds)
                ->where('kelas_id', $kelas->id)
                ->when($ta, fn($q) => $q->where('tahun_ajaran_id', $ta->id))
                ->get();

            $perSantri = $nilaiAkhir->groupBy('santri_id')->map(fn($rows) => [
                'rata'         => round($rows->avg('nilai_akhir'), 1),
                'belum_tuntas' => $rows->where('tuntas', false)->count(),
            ]);

            $namaMap = $kelas->santri->pluck('nama_lengkap', 'id');

            $terendah = $perSantri->sortBy('rata')->take(3)
                ->map(fn($v, $k) => ['nama' => $namaMap[$k] ?? '-', 'rata' => $v['rata']])
                ->values();
            $tertinggi = $perSantri->sortByDesc('rata')->take(3)
                ->map(fn($v, $k) => ['nama' => $namaMap[$k] ?? '-', 'rata' => $v['rata']])
                ->values();

            $pertemuanIdsBulanIni = Pertemuan::where('kelas_id', $kelas->id)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->pluck('id');
            $totalPresensi = PresensiKbm::whereIn('pertemuan_id', $pertemuanIdsBulanIni)->count();
            $totalHadir    = PresensiKbm::whereIn('pertemuan_id', $pertemuanIdsBulanIni)
                ->where('status', 'hadir')->count();

            $pelanggaranAktif = Pelanggaran::whereIn('santri_id', $santriIds)
                ->where('status', 'aktif')->count();

            $ringkasan[] = [
                'kelas'                       => $kelas->nama,
                'jumlah_santri'               => $santriIds->count(),
                'rata_rata_kelas'             => $perSantri->isNotEmpty() ? round($perSantri->avg('rata'), 1) : null,
                'jumlah_belum_tuntas'         => $perSantri->sum('belum_tuntas'),
                'santri_nilai_terendah'       => $terendah,
                'santri_nilai_tertinggi'      => $tertinggi,
                'kehadiran_bulan_ini_persen'  => $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100, 1) : null,
                'pelanggaran_aktif'           => $pelanggaranAktif,
            ];
        }

        return $ringkasan;
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
8. Kalau ada "ringkasan_kelas" di DATA_CONTEXT, itu AGREGAT per kelas (rata-rata, 3 nilai tertinggi/terendah, dst) -- BUKAN daftar lengkap semua santri. Kalau butuh detail lebih dalam soal 1 santri tertentu, sarankan pilih santri itu lewat dropdown "Analisis santri spesifik".
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
