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
                        <label class="block text-xs text-siakad-secondary mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <select name="jam_mulai" id="jam_mulai" required
                            onchange="calculateJamSelesai()"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-50 text-siakad-dark
                              focus:ring-2 outline-none transition">
                            <option value="">-- Pilih Jam Mulai --</option>
                            @foreach(config('siak.presensi.jam_mulai') as $jam => $label)
                            <option value="{{ $jam }}" {{ old('jam_mulai') === $jam ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-siakad-secondary mb-1">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_selesai" id="jam_selesai"
                            value="{{ old('jam_selesai') }}"
                            placeholder="--:--"
                            required readonly
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200
                              bg-gray-100 text-siakad-dark
                              focus:ring-2 outline-none transition cursor-not-allowed opacity-75
                              placeholder-gray-400">
                        <p class="text-[11px] text-siakad-secondary mt-1">Otomatis terisi dari jam mulai + {{ config('siak.presensi.durasi_jam') }}</p>
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
                            @foreach(['hadir'=>['H','green','Hadir'],'sakit'=>['S','blue','Sakit'],'izin'=>['I','yellow','Izin'],'alpa'=>['A','red','Alpa']] as $status => [$label, $color, $title])
                            <label class="cursor-pointer" title="{{ $title }}">
                                <input type="radio"
                                    name="presensi[{{ $i }}][status]"
                                    value="{{ $status }}"
                                    class="sr-only peer"
                                    onchange="updateCount()">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-xs
                                     font-bold border-2 transition select-none cursor-pointer
                                     peer-checked:shadow-md
                                     @if($color === 'green')
                                        border-gray-200 text-gray-400 hover:border-green-300
                                        peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700
                                     @elseif($color === 'blue')
                                        border-gray-200 text-gray-400 hover:border-blue-300
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                     @elseif($color === 'yellow')
                                        border-gray-200 text-gray-400 hover:border-yellow-300
                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-50 peer-checked:text-yellow-700
                                     @elseif($color === 'red')
                                        border-gray-200 text-gray-400 hover:border-red-300
                                        peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700
                                     @endif">
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
        // Durasi default dari config (format HH:MM)
        const DURASI_JAM = '{{ config("siak.presensi.durasi_jam") }}'; // contoh: '01:20'

        function calculateJamSelesai() {
            const jamMulaiSelect = document.getElementById('jam_mulai');
            const jamSelesaiInput = document.getElementById('jam_selesai');

            if (!jamMulaiSelect.value) {
                jamSelesaiInput.value = '';
                jamSelesaiInput.placeholder = '--:--';
                return;
            }

            // Parse jam mulai (format HH:MM)
            const [jamStr, menitStr] = jamMulaiSelect.value.split(':');
            let jam = parseInt(jamStr);
            let menit = parseInt(menitStr);

            // Parse durasi (format HH:MM)
            const [durasiJamStr, durasiMenitStr] = DURASI_JAM.split(':');
            const durasiJam = parseInt(durasiJamStr);
            const durasiMenit = parseInt(durasiMenitStr);

            // Tambahkan durasi
            menit += durasiMenit;
            if (menit >= 60) {
                jam += Math.floor(menit / 60);
                menit = menit % 60;
            }
            jam += durasiJam;

            // Pastikan jam tidak melebihi 24
            if (jam >= 24) {
                jam = jam % 24;
            }

            // Format ke HH:MM
            const jamSelesai = String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');
            jamSelesaiInput.value = jamSelesai;
            jamSelesaiInput.placeholder = jamSelesai;

            console.log('Jam Mulai:', jamMulaiSelect.value, 'Durasi:', DURASI_JAM, 'Jam Selesai:', jamSelesai);
        }

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

        // Initialize jam selesai jika ada old value
        @if(old('jam_mulai'))
        calculateJamSelesai();
        @endif

        updateCount();
    </script>
    @endpush
</x-app-layout>