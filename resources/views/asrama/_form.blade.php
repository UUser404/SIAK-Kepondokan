{{-- ============================================================ --}}
{{-- resources/views/asrama/_form.blade.php                       --}}
{{-- Dipakai bersama oleh create.blade.php & edit.blade.php        --}}
{{-- ============================================================ --}}
<div class="card-saas overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full bg-blue-500"></div>
        <h3 class="font-semibold text-sm text-siakad-dark">Data Asrama</h3>
    </div>
    <div class="p-5 space-y-5">

        @if($errors->any())
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Nama Asrama <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama" value="{{ old('nama', $asrama->nama ?? '') }}" required
                placeholder="e.g. Asrama Al-Fatih"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Jenis <span class="text-red-500">*</span>
            </label>
            <select name="jenis" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">-- Pilih Jenis --</option>
                @foreach(['putra' => 'Putra', 'putri' => 'Putri'] as $val => $label)
                <option value="{{ $val }}" @selected(old('jenis', $asrama->jenis ?? '') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">Pengurus / Pembina</label>
            <input type="text" name="pengurus" value="{{ old('pengurus', $asrama->pengurus ?? '') }}"
                placeholder="Nama pengurus/pembina asrama"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">Keterangan</label>
            <textarea name="keterangan" rows="3"
                placeholder="Catatan tambahan (opsional)"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                             bg-gray-50 text-siakad-dark placeholder-gray-400
                             focus:ring-2 outline-none resize-none transition">{{ old('keterangan', $asrama->keterangan ?? '') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                           hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $submitLabel ?? 'Simpan' }}
            </button>
            <a href="{{ route('kesantrian.asrama.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>
