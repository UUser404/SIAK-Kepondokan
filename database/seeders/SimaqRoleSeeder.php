<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\TenagaPendidik;

class SimaqRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini membuat role dan permission untuk modul SIMAQ.
     * Juga mengassign role guru_simaq kepada guru-guru yang memiliki kategori tahsin/tahfizh.
     */
    public function run(): void
    {
        // Buat permissions untuk SIMAQ
        $permissions = [
            'manage_simaq',                  // Full access ke SIMAQ
            'view_simaq',                    // View SIMAQ dashboard
            'view_any_simaq_penilaian',      // View semua penilaian SIMAQ
            'create_simaq_penilaian',        // Create penilaian baru
            'update_simaq_penilaian',        // Update penilaian (own + any untuk admin)
            'delete_simaq_penilaian',        // Delete penilaian (own + any untuk admin)
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Buat role guru_simaq
        $guruSimaqRole = Role::firstOrCreate(['name' => 'guru_simaq', 'guard_name' => 'web']);

        // Assign permissions ke role guru_simaq
        $guruSimaqRole->syncPermissions([
            'manage_simaq',
            'view_simaq',
            'view_any_simaq_penilaian',
            'create_simaq_penilaian',
            'update_simaq_penilaian',
            'delete_simaq_penilaian',
        ]);

        // Cari admin role (atau super admin) dan berikan semua permission SIMAQ
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(array_merge(
                $adminRole->permissions()->pluck('name')->toArray(),
                $permissions
            ));
        }

        // Assign role guru_simaq ke guru-guru yang memiliki kategori tahsin/tahfizh
        $guruSimaq = TenagaPendidik::query()
            ->whereIn('kategori_guru', ['tahsin', 'tahfizh'])
            ->get();

        foreach ($guruSimaq as $guru) {
            if ($guru->user && !$guru->user->hasRole('guru_simaq')) {
                $guru->user->assignRole('guru_simaq');
                $guru->update(['is_simaq_active' => true]);
            }
        }

        $this->command->info('✅ SIMAQ Role & Permission seeder berhasil dijalankan.');
        $this->command->info('   - Role "guru_simaq" dibuat');
        $this->command->info('   - 6 permissions untuk SIMAQ dibuat');
        $this->command->info('   - ' . $guruSimaq->count() . ' guru tahsin/tahfizh sudah di-assign role guru_simaq');
    }
}
