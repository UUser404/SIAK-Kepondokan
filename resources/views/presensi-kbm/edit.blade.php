{{-- ============================================================ --}}
{{-- resources/views/presensi-kbm/edit.blade.php                 --}}
{{-- File ini SEBELUMNYA isinya salah total -- ke-isi copy dari    --}}
{{-- index.blade.php (butuh $penugasanList, $riwayat), padahal      --}}
{{-- PresensiController::edit() kirim $pertemuan & $santriList.    --}}
{{-- Ini versi form edit sungguhan, field-nya PERSIS sama dengan    --}}
{{-- yang divalidasi PresensiController::update(): topik, materi,   --}}
{{-- catatan_guru, presensi per santri. Tanggal/jam SENGAJA tidak   --}}
{{-- ada di sini -- update() tidak menerima/mengubah field itu.     --}}
{{-- ============================================================ --}}
<x-app-layout>
    <x-slot name="header">Edit Presensi</x-slot>

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('guru.presensi.show', $pertemuan) }}"
            class="p-2 rounded-xl border border-gray-200
              text-siakad-secondary
              hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-semibold text-siakad-dark">
                Edit Presensi — {{ $pertemuan->mataPelajaran->nama }}
            </h2>
            <p class="text-sm text-siakad-secondary mt-0.5">
                {{ $pertemuan->kelas->nama }} · Pertemuan ke-{{ $pertemuan->pertemuan_ke }} ·
                {{ $pertemuan->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
    </div>

    <div class="p-3 mb-6 rounded-xl text-xs" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af;">
        Tanggal & jam pertemuan tidak bisa diubah lewat form ini. Yang bisa dikoreksi: topik, materi,
        catatan, dan status kehadiran tiap santri.
    </div>

    <form method="POST" action="{{ route('guru.presensi.update', $pertemuan) }}">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="mb-6 px-4 py-3 rounded-xl text-sm" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Topik / Materi / Catatan --}}
        <div class="card-saas p-5 mb-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">Topik</label>
                <input type="text" name="topik" value="{{ old('topik', $pertemuan->topik) }}"
                    placeholder="Topik singkat pertemuan ini"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400 focus:ring-2 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">Materi</label>
                <textarea name="materi" rows="2" placeholder="Materi yang diajarkan (opsional)"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400 focus:ring-2 outline-none resize-none transition">{{ old('materi', $pertemuan->materi) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-siakad-dark mb-1.5">Catatan Guru</label>
                <textarea name="catatan_guru" rows="2" placeholder="Catatan tambahan (opsional)"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400 focus:ring-2 outline-none resize-none transition">{{ old('catatan_guru', $pertemuan->catatan_guru) }}</textarea>
            </div>
        </div>

        {{-- Presensi per santri --}}
        <div class="card-saas overflow-hidden">
            <div class="px-5 py-3.5 flex items-center gap-2"
                style="border-bottom: 1px solid var(--border-color); background-color: rgba(35,76,106,0.04);">
                <div class="w-1 h-4 rounded-full" style="background-color: var(--siakad-primary);"></div>
                <h3 class="font-semibold text-sm text-siakad-dark">Presensi Santri</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-saas">
                    <thead style="background-color: rgba(35,76,106,0.04);">
                        <tr>
                            @foreach(['Santri','Status','Keterangan'] as $h)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide
                                   text-siakad-secondary">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--border-color);">
                        @foreach($santriList as $i => $sk)
                        @php
                        $santri = $sk->santri;
                        // presensiKbm sudah di-load dengan relasi santri lewat
                        // $pertemuan->load(['presensiKbm.santri']) di controller.
                        $existing = $pertemuan->presensiKbm->firstWhere('santri_id', $santri->id);
                        @endphp
                        <tr>
                            <td class="px-5 py-3">
                                <input type="hidden" name="presensi[{{ $i }}][santri_id]" value="{{ $santri->id }}">
                                <p class="text-sm font-medium text-siakad-dark">{{ $santri->nama_lengkap }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $santri->nis }}</p>
                            </td>
                            <td class="px-5 py-3">
                                <select name="presensi[{{ $i }}][status]" required
                                    class="w-28 px-2.5 py-2 text-sm rounded-lg border border-gray-200
                                          bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                                    @foreach(['hadir'=>'Hadir','sakit'=>'Sakit','izin'=>'Izin','alpa'=>'Alpa'] as $val => $label)
                                    <option value="{{ $val }}" {{ old("presensi.$i.status", $existing?->status) === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-3">
                                <input type="text" name="presensi[{{ $i }}][keterangan]"
                                    value="{{ old("presensi.$i.keterangan", $existing?->keterangan) }}"
                                    placeholder="Opsional"
                                    class="w-48 px-2.5 py-2 text-sm rounded-lg border border-gray-200
                                          bg-gray-50 text-siakad-dark placeholder-gray-400 focus:ring-2 outline-none transition">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 flex items-center gap-3" style="border-top: 1px solid var(--border-color);">
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                       hover:-translate-y-0.5 hover:shadow-lg"
                    style="background-color: var(--siakad-primary);"
                    onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                    onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                    Simpan Perubahan
                </button>
                <a href="{{ route('guru.presensi.show', $pertemuan) }}"
                    class="px-4 py-2.5 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                    Batal
                </a>
            </div>
        </div>
    </form>
</x-app-layout>