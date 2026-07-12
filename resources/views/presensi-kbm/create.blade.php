{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/create.blade.php                --}}
{{-- Diganti dari basis jadwal ke basis Penugasan Mengajar;        --}}
{{-- guru pilih tanggal manual (maksimal hari ini)                 --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Input Presensi</x-slot>

    <form method="POST" action="{{ route('guru.presensi.store') }}" id="form-presensi">
        @csrf
        <input type="hidden" name="penugasan_id" value="{{ $penugasan->id }}">

        <div class="space-y-5 max-w-3xl">

            {{-- Banner info --}}
            <div class="p-5 rounded-2xl bg-gradient-to-r from-siakad-primary to-siakad-dark">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-white/70 text-sm">Pertemuan ke-{{ $pertemuanKe }}</p>
                        <h2 class="text-2xl font-bold text-white mt-0.5">
                            {{ $penugasan->mataPelajaran->nama }}
                        </h2>
                        <p class="text-white/80 text-sm mt-1">
                            {{ $penugasan->kelas->nama }}
                        </p>
                    </div>
                    <a href="{{ route('guru.presensi.index') }}"
                        class="text-white/70 hover:text-white text-sm transition-colors">← Batal</a>
                </div>
            </div>

            {{-- Detail pertemuan --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Detail Pertemuan</h3>
                </div>
                <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs text-siakad-secondary mb-1">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', today()->format('Y-m-d')) }}"
                            max="{{ today()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none transition">
                        <p class="text-[11px] text-siakad-secondary mt-1">Tidak boleh lebih dari hari ini.</p>
                    </div>
                    <div>
                        <label class="block text-xs text-siakad-secondary mb-1">Jam Mulai</label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-siakad-secondary mb-1">Jam Selesai</label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs text-siakad-secondary mb-1">Topik</label>
                        <input type="text" name="topik" value="{{ old('topik') }}" placeholder="Topik pembelajaran"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              placeholder-gray-400 focus:ring-2 outline-none transition">
                    </div>
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-xs text-siakad-secondary mb-1">Materi yang Disampaikan</label>
                        <textarea name="materi" rows="2"
                            placeholder="Ringkasan materi yang diajarkan pada pertemuan ini..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                                 bg-gray-50 text-siakad-dark
                                 placeholder-gray-400 focus:ring-2 outline-none resize-none transition">{{ old('materi') }}</textarea>
                    </div>
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-xs text-siakad-secondary mb-1">Catatan Guru</label>
                        <textarea name="catatan_guru" rows="2"
                            placeholder="Catatan tambahan (opsional)..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                                 bg-gray-50 text-siakad-dark
                                 placeholder-gray-400 focus:ring-2 outline-none resize-none transition">{{ old('catatan_guru') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Daftar hadir --}}
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center justify-between flex-wrap gap-3"
                    style="border-bottom: 1px solid var(--border-color);
                    background-color: rgba(35,76,106,0.04);">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                        <div>
                            <h3 class="font-semibold text-sm text-siakad-dark">Daftar Kehadiran</h3>
                            <p class="text-xs text-siakad-secondary">
                                {{ $santriList->count() }} santri
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="setAllStatus('hadir')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl text-white transition"
                        style="background-color: var(--siakad-primary);">
                        Semua Hadir
                    </button>
                </div>

                {{-- Counter --}}
                <div class="px-5 py-2.5 flex gap-5 text-xs"
                    style="background-color: rgba(35,76,106,0.03);
                    border-bottom: 1px solid var(--border-color);">
                    @foreach(['hadir'=>['green','H'],'sakit'=>['blue','S'],'izin'=>['yellow','I'],'alpa'=>['red','A']] as $s => [$c, $l])
                    <span class="flex items-center gap-1.5 font-medium text-{{ $c }}-600 $c }}-400">
                        <span class="w-5 h-5 rounded-md bg-{{ $c }}-100 $c }}-900/30 flex items-center
                             justify-center text-[10px] font-bold">{{ $l }}</span>
                        <span id="count-{{ $s }}">0</span>
                    </span>
                    @endforeach
                </div>

                {{-- Santri list --}}
                <div class="divide-y max-h-[65vh] overflow-y-auto"
                    style="border-color: var(--border-color);">
                    @foreach($santriList as $i => $sk)
                    @php $santri = $sk->santri; @endphp
                    <div class="px-5 py-3 flex items-center gap-4">
                        <span class="text-xs text-siakad-secondary w-5 flex-shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold
                                text-white flex-shrink-0"
                                style="background-color: var(--siakad-primary);">
                                {{ strtoupper(substr($santri->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-siakad-dark truncate">
                                    {{ $santri->nama_lengkap }}
                                </p>
                                <p class="text-xs text-siakad-secondary">{{ $santri->nis }}</p>
                            </div>
                        </div>

                        <input type="hidden" name="presensi[{{ $i }}][santri_id]" value="{{ $santri->id }}">

                        {{-- Status toggle --}}
                        <div class="flex gap-1.5 flex-shrink-0">
                            @foreach(['hadir'=>['H','green'],'sakit'=>['S','blue'],'izin'=>['I','yellow'],'alpa'=>['A','red']] as $status => [$label, $color])
                            <label class="cursor-pointer">
                                <input type="radio"
                                    name="presensi[{{ $i }}][status]"
                                    value="{{ $status }}"
                                    class="sr-only peer"
                                    onchange="updateCount()"
                                    {{ $status === 'hadir' ? 'checked' : '' }}>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs
                                     font-bold border-2 transition select-none cursor-pointer
                                     border-gray-200
                                     text-gray-400
                                     peer-checked:border-{{ $color }}-400
                                     peer-checked:bg-{{ $color }}-50 $color }}-900/30
                                     peer-checked:text-{{ $color }}-600 $color }}-400
                                     hover:border-{{ $color }}-300">
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
                <a href="{{ route('guru.presensi.index') }}"
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
        updateCount();
    </script>
    @endpush
</x-app-layout>
