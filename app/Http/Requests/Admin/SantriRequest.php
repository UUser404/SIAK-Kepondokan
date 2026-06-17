<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create santri') || $this->user()->can('edit santri');
    }

    public function rules(): array
    {
        $santriId = $this->route('santri')?->id;

        return [
            // Identitas
            'nis'           => ['required', 'string', 'max:20',
                                Rule::unique('santri', 'nis')->ignore($santriId)],
            'nisn'          => ['nullable', 'string', 'digits:10',
                                Rule::unique('santri', 'nisn')->ignore($santriId)],
            'nama_lengkap'  => ['required', 'string', 'max:100'],
            'nama_panggilan'=> ['nullable', 'string', 'max:30'],
            'tempat_lahir'  => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'alamat'        => ['nullable', 'string', 'max:500'],
            'asal_sekolah'  => ['nullable', 'string', 'max:100'],
            'no_hp_santri'  => ['nullable', 'string', 'max:20'],
            'angkatan'      => ['nullable', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'status'        => ['sometimes', Rule::in(['aktif', 'alumni', 'keluar', 'pindah'])],

            // Data Wali
            'nama_ayah'     => ['nullable', 'string', 'max:100'],
            'nama_ibu'      => ['nullable', 'string', 'max:100'],
            'nama_wali'     => ['nullable', 'string', 'max:100'],
            'no_hp_wali'    => ['nullable', 'string', 'max:20'],
            'pekerjaan_wali'=> ['nullable', 'string', 'max:100'],

            // Foto
            'foto'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Penempatan kelas (opsional saat create)
            'kelas_id'         => ['nullable', 'exists:kelas,id'],
            'tahun_ajaran_id'  => ['nullable', 'exists:tahun_ajaran,id'],

            // Akun
            'email'         => ['nullable', 'email', 'max:100',
                                Rule::unique('users', 'email')
                                    ->ignore($this->route('santri')?->user_id)],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required'          => 'NIS wajib diisi.',
            'nis.unique'            => 'NIS sudah digunakan oleh santri lain.',
            'nisn.digits'           => 'NISN harus 10 digit.',
            'nisn.unique'           => 'NISN sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required'=> 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.before'  => 'Tanggal lahir tidak valid.',
            'foto.max'              => 'Ukuran foto maksimal 2MB.',
            'foto.mimes'            => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Bersihkan nomor HP dari spasi/strip
        if ($this->filled('no_hp_wali')) {
            $this->merge(['no_hp_wali' => preg_replace('/[^0-9+]/', '', $this->no_hp_wali)]);
        }
        if ($this->filled('no_hp_santri')) {
            $this->merge(['no_hp_santri' => preg_replace('/[^0-9+]/', '', $this->no_hp_santri)]);
        }
    }
}
