{{-- ============================================================ --}}
{{-- resources/views/users/_form.blade.php                        --}}
{{-- Dipakai bersama oleh create.blade.php & edit.blade.php        --}}
{{-- ============================================================ --}}
@php
$roleOptions = ['mudir', 'wakil_kurikulum', 'guru', 'kesantrian', 'admin', 'sysadmin'];
@endphp

<div class="card-saas overflow-hidden">
    <div class="px-5 py-3.5 flex items-center gap-2"
        style="border-bottom: 1px solid var(--border-color);
                background-color: rgba(35,76,106,0.04);">
        <div class="w-1 h-4 rounded-full bg-blue-500"></div>
        <h3 class="font-semibold text-sm text-siakad-dark">Data Akun</h3>
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

        {{-- Nama --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Nama Lengkap <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                placeholder="Nama sesuai identitas"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        {{-- Nama Arab (opsional, dipakai kalau user ini jadi wali kelas/kepsek/
             mudir yang tanda tangan di Rapor) --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Nama (Arab)
            </label>
            <input type="text" name="nama_arab" value="{{ old('nama_arab', $user->nama_arab ?? '') }}"
                placeholder="Contoh: فخر رضا — dipakai di Rapor kalau jadi wali kelas/kepsek/mudir"
                dir="rtl"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
                placeholder="nama@alislam.sch.id"
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
        </div>

        {{-- Role --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Role <span class="text-red-500">*</span>
            </label>
            <select name="role" required
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                           bg-gray-50 text-siakad-dark focus:ring-2 outline-none transition">
                <option value="">-- Pilih Role --</option>
                @foreach($roleOptions as $r)
                <option value="{{ $r }}" @selected(old('role', $user->role ?? '') === $r)>
                    {{ ucfirst(str_replace('_', ' ', $r)) }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm font-medium text-siakad-dark mb-1.5">
                Password
                @if(!isset($user) || !$user)
                <span class="text-red-500">*</span>
                @else
                <span class="text-xs font-normal text-siakad-secondary">(kosongkan jika tidak diganti)</span>
                @endif
            </label>
            <input type="password" name="password" autocomplete="new-password"
                placeholder="{{ (!isset($user) || !$user) ? 'Minimal 8 karakter' : 'Isi hanya jika ingin mengganti password' }}"
                {{ (!isset($user) || !$user) ? 'required' : '' }}
                class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200
                          bg-gray-50 text-siakad-dark placeholder-gray-400
                          focus:ring-2 outline-none transition">
            <p class="text-xs text-siakad-secondary mt-1.5">Minimal 8 karakter.</p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl text-white transition-all
                           hover:-translate-y-0.5 hover:shadow-lg"
                style="background-color: var(--siakad-primary);"
                onmouseover="this.style.backgroundColor='var(--siakad-dark)'"
                onmouseout="this.style.backgroundColor='var(--siakad-primary)'">
                {{ $submitLabel ?? 'Simpan' }}
            </button>
            <a href="{{ route('sysadmin.users.index') }}"
                class="px-4 py-2.5 text-sm text-siakad-secondary hover:text-siakad-dark transition-colors">
                Batal
            </a>
        </div>
    </div>
</div>