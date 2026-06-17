<?php
// ============================================================
// UsersSeeder.php
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\TenagaPendidik;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'  => 'Administrator Sistem',
                'email' => 'sysadmin@alislam.sch.id',
                'role'  => 'sysadmin',
            ],
            [
                'name'  => 'Mudir Pondok',
                'email' => 'mudir@alislam.sch.id',
                'role'  => 'mudir',
            ],
            [
                'name'  => 'Wakil Kurikulum',
                'email' => 'kurikulum@alislam.sch.id',
                'role'  => 'wakil_kurikulum',
            ],
            [
                'name'  => 'Ustadz Demo Guru',
                'email' => 'guru@alislam.sch.id',
                'role'  => 'guru',
            ],
            [
                'name'  => 'Bagian Kesantrian',
                'email' => 'kesantrian@alislam.sch.id',
                'role'  => 'kesantrian',
            ],
            [
                'name'  => 'Staf Admin',
                'email' => 'admin@alislam.sch.id',
                'role'  => 'admin',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => Hash::make('password'),
                    'role'      => $data['role'],
                    'is_active' => true,
                ]
            );

            // Assign Spatie role
            $user->syncRoles([$data['role']]);

            // Buat profil tenaga pendidik untuk guru & wakil kurikulum
            if (in_array($data['role'], ['guru', 'wakil_kurikulum', 'mudir', 'kesantrian', 'admin'])) {
                TenagaPendidik::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip'                => str_pad($user->id, 10, '0', STR_PAD_LEFT),
                        'status_kepegawaian' => 'tetap',
                        'tanggal_masuk'      => '2020-07-01',
                    ]
                );
            }
        }

        $this->command->info('✅ Users seeded.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            collect($users)->map(fn($u) => [$u['role'], $u['email'], 'password'])->toArray()
        );
    }
}
