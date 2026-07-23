<?php

namespace Database\Factories;

use App\Models\Santri;
use Illuminate\Database\Eloquent\Factories\Factory;

class SantriFactory extends Factory
{
    protected $model = Santri::class;

    public function definition(): array
    {
        $jenisKelamin = fake()->randomElement(['L', 'P']);
        $namaDepan    = $jenisKelamin === 'L' ? fake('id_ID')->firstNameMale() : fake('id_ID')->firstNameFemale();
        $namaLengkap  = $namaDepan . ' ' . fake('id_ID')->lastName();

        return [
            'user_id'        => null, // Dummy data -- sengaja tanpa akun portal
            'nis'            => fake()->unique()->numerify('2026####'),
            'nisn'           => fake()->unique()->numerify('##########'),
            'nama_lengkap'   => $namaLengkap,
            'nama_panggilan' => $namaDepan,
            'tempat_lahir'   => fake('id_ID')->city(),
            'tanggal_lahir'  => fake()->dateTimeBetween('-18 years', '-12 years'),
            'jenis_kelamin'  => $jenisKelamin,
            'alamat'         => fake('id_ID')->address(),
            'asal_sekolah'   => 'SD/MI ' . fake('id_ID')->city(),
            'no_hp_santri'   => fake()->numerify('08##########'),
            'nama_ayah'      => fake('id_ID')->firstNameMale() . ' ' . fake('id_ID')->lastName(),
            'nama_ibu'       => fake('id_ID')->firstNameFemale() . ' ' . fake('id_ID')->lastName(),
            'nama_wali'      => null,
            'no_hp_wali'     => fake()->numerify('08##########'),
            'pekerjaan_wali' => fake('id_ID')->jobTitle(),
            'status'         => 'aktif',
            'angkatan'       => now()->year,
            'foto'           => null,
        ];
    }
}
