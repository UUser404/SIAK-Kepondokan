<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\Tingkatan;
use App\Models\MataPelajaran;
use App\Models\KomponenNilai;
use App\Models\JenisKegiatan;
use App\Models\KategoriPelanggaran;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            MasterDataSeeder::class,
            UsersSeeder::class,
            SimaqRoleSeeder::class,
        ]);
    }
}