<?php
// ============================================================
// RolesPermissionsSeeder.php
// ============================================================
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---- Define all permissions ----
        $permissions = [
            // Dashboard
            'view dashboard',

            // Santri
            'view santri', 'create santri', 'edit santri', 'delete santri',
            'export santri',

            // Tenaga Pendidik
            'view pendidik', 'create pendidik', 'edit pendidik', 'delete pendidik',

            // Kelas & Jadwal
            'view kelas', 'manage kelas',
            'view jadwal', 'manage jadwal',

            // Presensi KBM
            'view presensi kbm', 'input presensi kbm', 'edit presensi kbm',

            // Presensi Kegiatan
            'view presensi kegiatan', 'input presensi kegiatan',

            // Penilaian
            'view nilai', 'input nilai', 'edit nilai', 'finalize nilai',
            'view rapor', 'cetak rapor',

            // Kesantrian
            'view kesantrian',
            'view kamar', 'manage kamar',
            'view pelanggaran', 'create pelanggaran', 'edit pelanggaran',
            'view prestasi', 'create prestasi',

            // PPDB
            'view ppdb', 'manage ppdb', 'verifikasi ppdb',

            // Surat
            'view surat', 'create surat', 'manage template surat',

            // AI Advisor
            'use ai advisor',

            // Laporan
            'view laporan', 'export laporan',

            // Manajemen Akun (sysadmin)
            'view users', 'create users', 'edit users', 'delete users',
            'view activity log',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ---- Define roles & assign permissions ----

        // 1. Mudir Pondok — akses baca semua, laporan eksekutif
        $mudir = Role::firstOrCreate(['name' => 'mudir']);
        $mudir->syncPermissions([
            'view dashboard',
            'view santri', 'export santri',
            'view pendidik',
            'view kelas', 'view jadwal',
            'view presensi kbm', 'view presensi kegiatan',
            'view nilai', 'view rapor',
            'view kesantrian', 'view kamar',
            'view pelanggaran', 'view prestasi',
            'view ppdb',
            'view surat',
            'view laporan', 'export laporan',
        ]);

        // 2. Wakil Kurikulum — kelola kurikulum, jadwal, nilai, presensi KBM
        $wakilKurikulum = Role::firstOrCreate(['name' => 'wakil_kurikulum']);
        $wakilKurikulum->syncPermissions([
            'view dashboard',
            'view santri',
            'view pendidik',
            'view kelas', 'manage kelas',
            'view jadwal', 'manage jadwal',
            'view presensi kbm', 'input presensi kbm', 'edit presensi kbm',
            'view nilai', 'input nilai', 'edit nilai', 'finalize nilai',
            'view rapor', 'cetak rapor',
            'use ai advisor',
            'view laporan', 'export laporan',
        ]);

        // 3. Guru — presensi kelas sendiri, input nilai mapel sendiri
        $guru = Role::firstOrCreate(['name' => 'guru']);
        $guru->syncPermissions([
            'view dashboard',
            'view santri',
            'view jadwal',
            'view presensi kbm', 'input presensi kbm',
            'view nilai', 'input nilai',
            'view rapor',
            'use ai advisor',
        ]);

        // 4. Bagian Kesantrian — presensi kegiatan, asrama, pelanggaran, prestasi
        $kesantrian = Role::firstOrCreate(['name' => 'kesantrian']);
        $kesantrian->syncPermissions([
            'view dashboard',
            'view santri',
            'view presensi kegiatan', 'input presensi kegiatan',
            'view kesantrian',
            'view kamar', 'manage kamar',
            'view pelanggaran', 'create pelanggaran', 'edit pelanggaran',
            'view prestasi', 'create prestasi',
            'view laporan',
        ]);

        // 5. Staf Admin — data master, santri full, PPDB, surat
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view dashboard',
            'view santri', 'create santri', 'edit santri', 'export santri',
            'view pendidik', 'create pendidik', 'edit pendidik',
            'view kelas', 'manage kelas',
            'view jadwal',
            'view ppdb', 'manage ppdb', 'verifikasi ppdb',
            'view surat', 'create surat', 'manage template surat',
            'view laporan', 'export laporan',
        ]);

        // 6. Administrator Sistem — full akses
        $sysadmin = Role::firstOrCreate(['name' => 'sysadmin']);
        $sysadmin->syncPermissions(Permission::all());

        $this->command->info('✅ Roles & permissions seeded.');
    }
}