{{-- ============================================================ --}}
{{-- resources/views/pendidik/show.blade.php                      --}}
{{-- File ini sebelumnya belum ada, padahal sudah dipanggil oleh  --}}
{{-- Admin\PendidikController::show()                              --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Profil Tenaga Pendidik</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.pendidik.index') }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">{{ $pendidik->user->name }}</h2>
            <p class="text-sm text-siakad-secondary mt-0.5">NIP: {{ $pendidik->nip ?? '-' }}</p>
        </div>
    </div>

    <div class="space-y-5 max-w-4xl">

        {{-- Header Card --}}
        <div class="card-saas overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-siakad-primary to-siakad-dark">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white/20 flex-shrink-0
                            flex items-center justify-center">
                        @if($pendidik->foto)
                        <img src="{{ Storage::url($pendidik->foto) }}" class="w-full h-full object-cover">
                        @else
                        <span class="text-3xl font-bold text-white">
                            {{ strtoupper(substr($pendidik->user->name, 0, 1)) }}
                        </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold text-white">{{ $pendidik->user->name }}</h2>
                        <p class="text-white/80 text-sm mt-1">
                            {{ $pendidik->user->email }}
                            @if($pendidik->nip) · NIP: {{ $pendidik->nip }} @endif
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                     bg-white/20 text-white capitalize">
                                {{ str_replace('_', ' ', $pendidik->user->role) }}
                            </span>
                            @if($pendidik->status_kepegawaian)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white capitalize">
                                {{ $pendidik->status_kepegawaian }}
                            </span>
                            @endif
                            @if($pendidik->jenis_kelamin)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white">
                                {{ $pendidik->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                     {{ $pendidik->user->is_active ? 'bg-white/20' : 'bg-red-500/40' }} text-white">
                                {{ $pendidik->user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('admin.pendidik.edit', $pendidik) }}"
                        class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold
                          bg-white/20 hover:bg-white/30 text-white transition">
                        Edit Data
                    </a>
                </div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid md:grid-cols-2 gap-5">
            {{-- Identitas --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Identitas</h3>
                </div>
                <dl class="space-y-2.5 text-sm">
                    @foreach([
                    ['NIK', $pendidik->nik ?? '-'],
                    ['Tempat, Tgl Lahir', ($pendidik->tempat_lahir ?? '-') . ', ' . ($pendidik->tanggal_lahir?->format('d/m/Y') ?? '-')],
                    ['No. HP', $pendidik->no_hp ?? '-'],
                    ['Alamat', $pendidik->alamat ?? '-'],
                    ] as [$label, $value])
                    <div class="flex justify-between gap-4">
                        <dt class="text-siakad-secondary flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-siakad-dark font-medium text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>

            {{-- Kepegawaian --}}
            <div class="card-saas p-5">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                    <h3 class="font-semibold text-sm text-siakad-dark">Kepegawaian</h3>
                </div>
                <dl class="space-y-2.5 text-sm">
                    @foreach([
                    ['Pendidikan Terakhir', $pendidik->pendidikan_terakhir ?? '-'],
                    ['Jurusan', $pendidik->jurusan ?? '-'],
                    ['Status Kepegawaian', $pendidik->status_kepegawaian ? ucfirst($pendidik->status_kepegawaian) : '-'],
                    ['Tanggal Masuk', $pendidik->tanggal_masuk?->format('d/m/Y') ?? '-'],
                    ] as [$label, $value])
                    <div class="flex justify-between gap-4">
                        <dt class="text-siakad-secondary flex-shrink-0">{{ $label }}</dt>
                        <dd class="text-siakad-dark font-medium text-right">{{ $value }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Jadwal Mengajar --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color);
                        background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full bg-blue-500"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Jadwal Mengajar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Hari','Jam','Kelas','Mata Pelajaran','Ruangan'] as $h)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                       text-siakad-secondary whitespace-nowrap">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @forelse($pendidik->jadwalPelajaran as $j)
                        <tr>
                            <td class="px-5 py-3.5 text-siakad-dark font-medium">{{ $j->hari }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">
                                {{ \Illuminate\Support\Carbon::parse($j->jam_mulai)->format('H:i') }} -
                                {{ \Illuminate\Support\Carbon::parse($j->jam_selesai)->format('H:i') }}
                            </td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $j->kelas->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $j->mataPelajaran->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-siakad-secondary text-xs">{{ $j->ruangan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-siakad-secondary">
                                Belum ada jadwal mengajar untuk tenaga pendidik ini
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
