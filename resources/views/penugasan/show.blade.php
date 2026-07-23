{{-- ============================================================ --}}
{{-- resources/views/penugasan/show.blade.php                     --}}
{{-- Langkah 2: tambah mapel -> pilih kelas (bisa banyak),         --}}
{{-- diulang kalau guru mengampu mapel lain                        --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Penugasan Mengajar</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('kurikulum.penugasan.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $guru->name }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $guru->email }}
                @if($ta) &middot; Tahun Ajaran {{ $ta->nama }} ({{ ucfirst($ta->semester) }}) @endif
            </p>
        </div>
    </div>

    @if(!$ta)
    <div class="p-4 mb-6 rounded-xl text-sm" style="background:#fef3c7; border:1px solid #fde68a; color:#92400e;">
        Belum ada tahun ajaran aktif. Aktifkan salah satu tahun ajaran terlebih dahulu di Data Master.
    </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-5">

        {{-- Daftar penugasan saat ini --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($penugasanList as $mapelId => $rows)
            @php $mapel = $rows->first()->mataPelajaran; @endphp
            <div class="card-saas overflow-hidden">
                <div class="px-5 py-3.5 flex items-center gap-2"
                    style="border-bottom: 1px solid var(--border-color);
                            background-color: rgba(35,76,106,0.04);">
                    <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">{{ $mapel->nama }}</h3>
                    <span class="text-xs text-siakad-secondary">({{ $rows->count() }} kelas)</span>
                </div>
                <div class="p-4 flex flex-wrap gap-2">
                    @foreach($rows as $p)
                    <span class="inline-flex items-center gap-2 pl-3 pr-1.5 py-1.5 rounded-xl text-xs font-medium
                                 bg-gray-100 dark:bg-gray-700 text-siakad-dark">
                        {{ $p->kelas->nama }}
                        <form method="POST" action="{{ route('kurikulum.penugasan.destroy', $p) }}"
                            onsubmit="return confirm('Hapus penugasan {{ $mapel->nama }} - {{ $p->kelas->nama }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-5 h-5 rounded-full flex items-center justify-center
                                       text-gray-400 hover:bg-red-100 hover:text-red-600 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </span>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="card-saas p-10 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-siakad-secondary">
                    Guru ini belum memiliki penugasan mengajar. Tambahkan lewat form di samping.
                </p>
            </div>
            @endforelse
        </div>

        {{-- Form tambah mapel + kelas --}}
        <div>
            <div class="card-saas p-5 sticky top-24">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Tambah Mapel & Kelas</h3>
                </div>

                @if($errors->any())
                <div class="px-3 py-2.5 rounded-xl text-xs mb-4"
                    style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('kurikulum.penugasan.store', $guru) }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <select name="mata_pelajaran_id" required
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                                   bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($mapelList as $m)
                            <option value="{{ $m->id }}" @selected(old('mata_pelajaran_id')==$m->id)>{{ $m->nama }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-siakad-secondary mt-1.5">
                            Tambahkan satu mapel dulu, lalu ulangi form ini kalau guru mengampu mapel lain.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                            Kelas yang Diampu <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-1.5 max-h-64 overflow-y-auto p-2 rounded-xl border border-gray-200 bg-gray-50">
                            @forelse($kelasList as $k)
                            <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-white transition cursor-pointer">
                                <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}"
                                    {{ collect(old('kelas_id'))->contains($k->id) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-siakad-primary focus:ring-siakad-primary">
                                <span class="text-sm text-siakad-dark">{{ $k->nama }}</span>
                            </label>
                            @empty
                            <p class="text-xs text-siakad-secondary px-2 py-1.5">Belum ada data kelas.</p>
                            @endforelse
                        </div>
                    </div>

                    <button type="submit" {{ (!$ta || $kelasList->isEmpty()) ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                                   hover:-translate-y-0.5 hover:shadow-lg disabled:opacity-50 disabled:pointer-events-none"
                        style="background-color: var(--siakad-primary);"
                        onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                        onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                        Simpan Penugasan
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>