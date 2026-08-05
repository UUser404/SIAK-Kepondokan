<?php

namespace Database\Seeders;

use App\Models\TenagaPendidik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuruContohSeeder extends Seeder
{
    /**
     * 5 akun guru contoh (dipilih acak dari daftar nama yang diberikan),
     * password default 'password' -- ganti sebelum dipakai sungguhan.
     *
     * Idempotent: User::firstOrCreate keyed by email, jadi aman dijalankan
     * ulang (tidak akan bikin akun duplikat).
     */
    public function run(): void
    {
        $namaList = [
            "Romi Solihudin Ma'ruf, S.Pd",
            'Shabrina Haibaty, S.Pd',
            'Muhammad Faris Nuriman',
            'Sipa Puadah, S.Ag',
            'Luqmanul Hakim, S.T.',
        ];

        foreach ($namaList as $namaLengkap) {
            $email = $this->buatEmail($namaLengkap);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'      => $namaLengkap,
                    'password'  => Hash::make('password'),
                    'role'      => 'guru',
                    'is_active' => true,
                ]
            );
            $user->assignRole('guru');

            TenagaPendidik::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status_kepegawaian' => 'honorer', // Default aman, sesuaikan manual nanti
                    'tanggal_masuk'      => now(),
                    'status'             => 'aktif',
                ]
            );

            $this->command->info("  → {$namaLengkap} ({$email})");
        }

        $this->command->info('✅ 5 akun guru contoh dibuat/dipastikan ada. Password default: password');
    }

    /**
     * Bikin email dari 2 kata pertama nama, huruf kecil, tanpa gelar/simbol.
     * "Romi Solihudin Ma'ruf, S.Pd" -> romi.solihudin@alislam.sch.id
     */
    private function buatEmail(string $namaLengkap): string
    {
        $namaBersih = explode(',', $namaLengkap)[0]; // Buang gelar setelah koma
        $namaBersih = preg_replace("/[^A-Za-z\s]/", '', $namaBersih); // Buang simbol (') dsb
        $kata = array_filter(explode(' ', trim($namaBersih)));
        $duaKata = array_slice(array_values($kata), 0, 2);

        return strtolower(implode('.', $duaKata)) . '@alislam.sch.id';
    }
}
