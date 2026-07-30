<?php
// ============================================================
// app/Http/Requests/Admin/PendidikRequest.php
// ============================================================
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PendidikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create pendidik') || $this->user()->can('edit pendidik');
    }

    public function rules(): array
    {
        $pendidikId = $this->route('pendidik');
        $userId     = $pendidikId?->user_id;
        $isUpdate   = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // Data user/akun
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:100',
                           Rule::unique('users', 'email')->ignore($userId)],
            'role' => 'required|string|in:guru,guru_tahsin_tahfizh,wakil_kurikulum,bagian_kesantrian,staf_admin,mudir',                 'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8'],

            // Data pendidik
            'nip'                 => ['nullable', 'string', 'max:20',
                                      Rule::unique('tenaga_pendidik', 'nip')->ignore($pendidikId?->id)],
            'nik'                 => ['nullable', 'string', 'digits:16',
                                      Rule::unique('tenaga_pendidik', 'nik')->ignore($pendidikId?->id)],
            'tempat_lahir'        => ['nullable', 'string', 'max:50'],
            'tanggal_lahir'       => ['nullable', 'date', 'before:today'],
            'jenis_kelamin'       => ['nullable', Rule::in(['L', 'P'])],
            'alamat'              => ['nullable', 'string', 'max:500'],
            'no_hp'               => ['nullable', 'string', 'max:20'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:10'],
            'jurusan'             => ['nullable', 'string', 'max:100'],
            'status_kepegawaian'  => ['nullable', Rule::in(['tetap', 'kontrak', 'honorer'])],
            'tanggal_masuk'       => ['nullable', 'date'],
            'foto'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Nama wajib diisi.',
            'email.required'  => 'Email wajib diisi.',
            'email.unique'    => 'Email sudah digunakan.',
            'role.required'   => 'Role wajib dipilih.',
            'password.min'    => 'Password minimal 8 karakter.',
            'nik.digits'      => 'NIK harus 16 digit.',
        ];
    }
}
