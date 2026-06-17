{{-- ============================================================ --}}
{{-- resources/views/presensi-kegiatan/show.blade.php - revised  --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">{{ $kegiatan->nama }}</x-slot>

    <form method="POST" action="{{ route('kesantrian.presensi.store') }}">
        @csrf
        <input type="hidden" name="jenis_kegiatan_id" value="{{ $kegiatan->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="space-y-5 max-w-3xl">

            {{-- Banner --}}
            <div class="p-5 rounded-2xl bg-gradient-to-r from-siakad-primary to-siakad-dark">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-white/70 text-sm">
                            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                        </p>
                        <h2 class="text-2xl font-bold text-white mt-0.5">{{ $kegiatan->nama }}</h2>
                        @if($kegiatan->waktu_default)
                        <p class="text-white/70 text-sm mt-1">
                            {{ \Carbon\Carbon::parse($kegiatan->waktu_default)->format('H:i') }} WIB
                        </p>
                        @endif
                    </div>
                    <a href="{{ route('kesantrian.presensi.index') }}"
                        class="text-white/70 hover:text-white text-sm transition-colors">← Kembali</a>
                </div>

                {{-- Rekap badge --}}
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach(['hadir'=>['H','green'],'sakit'=>['S','blue'],'izin'=>['I','yellow'],'alpa'=>['A','red']] as $s => [$l, $c])
                    <div class="flex items-center gap-1.5 px-3 py-1.5 bg-white/10 rounded-xl">
                        <span class="text-xs font-bold text-white">{{ $l }}</span>
                        <span class="text-sm font-bold text-white" id="count-{{ $s }}">
                            {{ $sudahInput->where('status', $s)->count() }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- List santri --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center justify-between flex-wrap gap-3"
                    style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                        <h3 class="font-semibold text-sm text-siakad-dark">
                            Daftar Kehadiran · {{ $santriList->count() }} santri
                        </h3>
                    </div>
                    <button type="button" onclick="setAllStatus('hadir')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl text-white transition"
                        style="background-color: var(--siakad-primary);">
                        Semua Hadir
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-5 py-3" style="border-bottom: 1px solid var(--border-color);">
                    <input type="text" id="search-santri" placeholder="Cari nama santri..."
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark
                          placeholder-gray-400 focus:ring-2 outline-none transition">
                </div>

                <div class="divide-y max-h-[65vh] overflow-y-auto"
                    style="border-color: var(--border-color);" id="santri-list">
                    @foreach($santriList as $i => $santri)
                    @php $existing = $sudahInput[$santri->id] ?? null; @endphp
                    <div class="px-5 py-3 flex items-center gap-4 santri-row"
                        data-nama="{{ strtolower($santri->nama_lengkap) }}">
                        <span class="text-xs text-siakad-secondary w-5 flex-shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold
                                text-white flex-shrink-0"
                                style="background-color: var(--siakad-secondary);">
                                {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-siakad-dark truncate">
                                    {{ $santri->nama_lengkap }}
                                </p>
                                @if($santri->kamarAktif)
                                <p class="text-xs text-siakad-secondary">
                                    {{ $santri->kamarAktif->asrama->nama }} - Kamar {{ $santri->kamarAktif->nomor_kamar }}
                                </p>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="presensi[{{ $i }}][santri_id]" value="{{ $santri->id }}">

                        <div class="flex gap-1.5 flex-shrink-0">
                            @foreach(['hadir'=>['H','green'],'sakit'=>['S','blue'],'izin'=>['I','yellow'],'alpa'=>['A','red']] as $status => [$label, $color])
                            <label class="cursor-pointer">
                                <input type="radio"
                                    name="presensi[{{ $i }}][status]"
                                    value="{{ $status }}"
                                    class="sr-only peer"
                                    onchange="updateCount()"
                                    {{ ($existing?->status ?? 'hadir') === $status ? 'checked' : '' }}>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs
                                     font-bold border-2 transition select-none
                                     border-gray-200 text-gray-400
                                     peer-checked:border-{{ $color }}-400
                                     peer-checked:bg-{{ $color }}-50 $color }}-900/30
                                     peer-checked:text-{{ $color }}-600 $color }}-400">
                                    {{ $label }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pb-6">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan Presensi
                </button>
                <a href="{{ route('kesantrian.presensi.index') }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary
                  hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function updateCount() {
            const counts = {
                hadir: 0,
                sakit: 0,
                izin: 0,
                alpa: 0
            };
            document.querySelectorAll('input[type=radio]:checked').forEach(r => {
                if (counts[r.value] !== undefined) counts[r.value]++;
            });
            Object.keys(counts).forEach(s => {
                const el = document.getElementById('count-' + s);
                if (el) el.textContent = counts[s];
            });
        }

        function setAllStatus(status) {
            document.querySelectorAll(`input[type=radio][value="${status}"]`).forEach(r => r.checked = true);
            updateCount();
        }
        document.getElementById('search-santri')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.santri-row').forEach(row => {
                row.style.display = row.dataset.nama.includes(q) ? '' : 'none';
            });
        });
        updateCount();
    </script>
    @endpush
</x-app-layout>