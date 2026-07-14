{{-- ============================================================ --}}
{{-- resources/views/ai/index.blade.php                          --}}
{{-- AI Advisor Chat Interface                                   --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">AI Advisor</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5 mb-1">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center"
                    style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707
                             m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0
                             11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h2 class="text-xl font-semibold text-siakad-dark">AI Advisor</h2>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                         bg-purple-100 text-purple-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                    Online
                </span>
            </div>
            <p class="text-sm text-siakad-secondary">
                Tanyakan analisis akademik, nilai santri, atau strategi pembelajaran.
            </p>
        </div>

        {{-- Pilih santri (opsional) --}}
        @if(in_array(auth()->user()->role, ['guru','wakil_kurikulum','kesantrian','admin','sysadmin']))
        <div class="flex-shrink-0">
            <label class="block text-xs text-siakad-secondary mb-1">
                Analisis santri spesifik (opsional)
            </label>
            <select id="santri-select"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200
                       bg-gray-50 text-siakad-dark
                       focus:ring-2 outline-none transition w-56">
                <option value="">-- Tidak terkait santri --</option>
                @foreach(\App\Models\Santri::aktif()->orderBy('nama_lengkap')->get() as $s)
                <option value="{{ $s->id }}">{{ $s->nama_lengkap }} ({{ $s->nis }})</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Chat area --}}
        <div class="lg:col-span-2 flex flex-col" style="height: 70vh;">

            {{-- Messages --}}
            <div class="card-saas flex-1 overflow-y-auto p-5 space-y-4 mb-4"
                id="chat-messages">

                {{-- Welcome message --}}
                <div class="flex gap-3" id="welcome-msg">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0
                             text-white text-xs font-bold"
                        style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">AI</div>
                    <div class="flex-1">
                        <div class="inline-block px-4 py-3 rounded-2xl rounded-tl-sm text-sm
                                bg-gray-100 dark:bg-gray-700 text-siakad-dark
                                max-w-[80%]">
                            <p>Assalamu'alaikum! Saya AI Advisor SIAK Kepondokan.</p>
                            <p class="mt-1">Saya dapat membantu menganalisis:</p>
                            <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-siakad-secondary">
                                <li>Nilai dan ketuntasan belajar santri</li>
                                <li>Kehadiran dan pola absensi</li>
                                <li>Identifikasi santri yang perlu perhatian</li>
                                <li>Strategi pembelajaran dan pembinaan</li>
                            </ul>
                            <p class="mt-1.5 text-xs text-siakad-secondary">
                                Pilih santri di atas untuk analisis individual, atau tanyakan tentang kelas/pondok secara umum.
                            </p>
                        </div>
                        <p class="text-xs text-siakad-secondary mt-1 px-1">
                            AI Advisor · {{ now()->format('H:i') }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Input area --}}
            <div class="card-saas p-3">
                <div class="flex gap-2">
                    <textarea id="chat-input"
                        rows="2"
                        placeholder="Tanyakan sesuatu tentang santri, nilai, atau strategi pembelajaran..."
                        class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                 bg-gray-50
                                 text-siakad-dark placeholder-gray-400
                                 focus:ring-2 outline-none resize-none transition"
                        onkeydown="handleEnter(event)"></textarea>
                    <button id="btn-send" onclick="sendMessage()"
                        class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition-all
                               hover:-translate-y-0.5 hover:shadow-lg flex items-center gap-2 flex-shrink-0
                               self-end"
                        style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Kirim
                    </button>
                </div>
                <p class="text-xs text-siakad-secondary mt-2 px-1">
                    Enter untuk kirim · Shift+Enter untuk baris baru · Dijawab oleh Groq AI ({{ config('siak.ai.groq.model','llama-3.3-70b') }})
                </p>
            </div>
        </div>

        {{-- Sidebar: riwayat + quick prompts --}}
        <div class="space-y-4">

            {{-- Quick prompts --}}
            <div class="card-saas p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-4 rounded-full bg-purple-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Pertanyaan Cepat</h3>
                </div>
                <div class="space-y-2">
                    @foreach([
                    'Siapa santri dengan nilai terendah?',
                    'Bagaimana tren kehadiran kelas bulan ini?',
                    'Santri mana yang perlu perhatian khusus?',
                    'Berikan rekomendasi strategi pembelajaran untuk mapel dengan nilai rendah.',
                    'Apa saja pola pelanggaran yang sering terjadi?',
                    'Santri mana yang paling berprestasi?',
                    ] as $prompt)
                    <button type="button" onclick="usePrompt('{{ $prompt }}')"
                        class="w-full text-left px-3 py-2 text-xs rounded-xl border transition
                               border-gray-200
                               text-siakad-secondary
                               hover:border-purple-400
                               hover:bg-purple-50
                               hover:text-purple-700">
                        {{ $prompt }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Riwayat --}}
            @if($riwayat->count())
            <div class="card-saas p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-1 h-4 rounded-full bg-gray-400"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Riwayat Terakhir</h3>
                </div>
                <div class="space-y-2">
                    @foreach($riwayat as $log)
                    <div class="p-2.5 rounded-xl bg-gray-50">
                        <p class="text-xs font-medium text-siakad-dark truncate">
                            {{ $log->question }}
                        </p>
                        <p class="text-[10px] text-siakad-secondary mt-0.5">
                            {{ $log->created_at->diffForHumans() }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Info --}}
            <div class="px-4 py-3 rounded-xl bg-amber-50
                    border border-amber-200">
                <p class="text-xs font-semibold text-amber-700 mb-1">
                    ⚠️ Perhatian
                </p>
                <p class="text-xs text-amber-600">
                    AI Advisor hanya menjawab berdasarkan data yang ada di sistem. Selalu verifikasi
                    hasil analisis dengan data asli sebelum mengambil keputusan.
                </p>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        let chatHistory = [];
        const CHAT_ROUTE = '{{ auth()->user()->role === "guru" ? route("guru.ai.chat") : route("kurikulum.ai.chat") }}';

        function usePrompt(text) {
            document.getElementById('chat-input').value = text;
            document.getElementById('chat-input').focus();
        }

        function handleEnter(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function addMessage(role, content, time) {
            const container = document.getElementById('chat-messages');
            const isUser = role === 'user';

            const div = document.createElement('div');
            div.className = `flex gap-3 ${isUser ? 'flex-row-reverse' : ''}`;

            const avatar = isUser ?
                `<div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-xs font-bold text-white" style="background-color: var(--siakad-primary);">
               {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
           </div>` :
                `<div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-xs font-bold" style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">AI</div>`;

            // Convert markdown-like formatting
            const formatted = content
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code class="bg-gray-200 dark:bg-gray-600 px-1 rounded text-xs">$1</code>')
                .replace(/\n/g, '<br>');

            const bubble = isUser ?
                `<div class="inline-block px-4 py-3 rounded-2xl rounded-tr-sm text-sm text-white max-w-[80%]" style="background-color: var(--siakad-primary);">${formatted}</div>` :
                `<div class="inline-block px-4 py-3 rounded-2xl rounded-tl-sm text-sm bg-gray-100 dark:bg-gray-700 text-siakad-dark max-w-[80%]">${formatted}</div>`;

            const label = isUser ?
                `<p class="text-xs text-right text-siakad-secondary mt-1 px-1">Anda · ${time}</p>` :
                `<p class="text-xs text-siakad-secondary mt-1 px-1">AI Advisor · ${time}</p>`;

            div.innerHTML = `${avatar}<div class="flex-1">${bubble}${label}</div>`;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        function addTypingIndicator() {
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.id = 'typing-indicator';
            div.className = 'flex gap-3';
            div.innerHTML = `
        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 text-white text-xs font-bold" style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">AI</div>
        <div class="flex-1">
            <div class="inline-flex items-center gap-1.5 px-4 py-3 rounded-2xl rounded-tl-sm bg-gray-100">
                <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                <span class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
            </div>
        </div>`;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }

        async function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            const santriId = document.getElementById('santri-select')?.value || null;
            const btnSend = document.getElementById('btn-send');

            if (!message) return;

            const now = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            // Show user message
            addMessage('user', message, now);
            chatHistory.push({
                role: 'user',
                content: message
            });

            input.value = '';
            btnSend.disabled = true;
            addTypingIndicator();

            try {
                const res = await fetch(CHAT_ROUTE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        message,
                        history: chatHistory.slice(-10),
                        santri_id: santriId
                    }),
                });

                const data = await res.json();

                document.getElementById('typing-indicator')?.remove();

                const reply = data.message ?? 'Maaf, terjadi kesalahan.';
                addMessage('assistant', reply, now);
                chatHistory.push({
                    role: 'assistant',
                    content: reply
                });

            } catch (err) {
                document.getElementById('typing-indicator')?.remove();
                addMessage('assistant', 'Koneksi gagal. Periksa jaringan Anda.', now);
            } finally {
                btnSend.disabled = false;
                input.focus();
            }
        }
    </script>
    @endpush
</x-app-layout>