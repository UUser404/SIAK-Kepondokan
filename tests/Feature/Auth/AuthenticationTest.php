<?php

// tests/Feature/Auth/AuthenticationTest.php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ============================================================
// LOGIN
// ============================================================

it('halaman login dapat diakses', function () {
    $this->get('/login')
        ->assertStatus(200)
        ->assertSee('SIAK Kepondokan');
});

it('user tidak terautentikasi diarahkan ke login', function () {
    $this->get('/')->assertRedirect('/login');
});

it('login berhasil dengan kredensial valid', function () {
    $user = User::factory()->create([
        'email'     => 'test@alislam.sch.id',
        'password'  => bcrypt('password'),
        'role'      => 'admin',
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email'    => 'test@alislam.sch.id',
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

it('login gagal dengan password salah', function () {
    User::factory()->create([
        'email'    => 'test@alislam.sch.id',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', [
        'email'    => 'test@alislam.sch.id',
        'password' => 'salah-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('login gagal jika akun dinonaktifkan', function () {
    User::factory()->create([
        'email'     => 'nonaktif@alislam.sch.id',
        'password'  => bcrypt('password'),
        'role'      => 'guru',
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email'    => 'nonaktif@alislam.sch.id',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logout berhasil dan redirect ke login', function () {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('last_login_at diupdate saat login', function () {
    $user = User::factory()->create([
        'email'      => 'guru@alislam.sch.id',
        'password'   => bcrypt('password'),
        'role'       => 'guru',
        'is_active'  => true,
        'last_login_at' => null,
    ]);

    $this->post('/login', [
        'email'    => 'guru@alislam.sch.id',
        'password' => 'password',
    ]);

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

// ============================================================
// DASHBOARD REDIRECT PER ROLE
// ============================================================

dataset('role_dashboard_routes', [
    'mudir'           => ['mudir',           '/mudir/dashboard'],
    'wakil_kurikulum' => ['wakil_kurikulum',  '/kurikulum/dashboard'],
    'guru'            => ['guru',             '/guru/dashboard'],
    'kesantrian'      => ['kesantrian',       '/kesantrian/dashboard'],
    'admin'           => ['admin',            '/admin/dashboard'],
    'sysadmin'        => ['sysadmin',         '/sysadmin/dashboard'],
]);

it('redirect ke dashboard yang tepat sesuai role', function (string $role, string $expectedPath) {
    $user = User::factory()->create([
        'role'      => $role,
        'is_active' => true,
        'password'  => bcrypt('password'),
    ]);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/')
        ->assertRedirect($expectedPath);
})->with('role_dashboard_routes');

it('guru tidak bisa akses dashboard admin', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $user->assignRole('guru');

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('admin tidak bisa akses dashboard sysadmin', function () {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get('/sysadmin/dashboard')
        ->assertForbidden();
});

it('murid tidak bisa akses halaman mana pun tanpa login', function () {
    $paths = [
        '/guru/dashboard',
        '/admin/santri',
        '/kesantrian/pelanggaran',
        '/kurikulum/nilai',
    ];

    foreach ($paths as $path) {
        $this->get($path)->assertRedirect('/login');
    }
});

// ============================================================
// RBAC — PERMISSIONS
// ============================================================

it('guru memiliki permission input presensi kbm', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $user->assignRole('guru');

    expect($user->can('input presensi kbm'))->toBeTrue();
    expect($user->can('manage kelas'))->toBeFalse();
});

it('sysadmin memiliki semua permission', function () {
    $user = User::factory()->create(['role' => 'sysadmin', 'is_active' => true]);
    $user->assignRole('sysadmin');

    $criticalPermissions = [
        'view santri', 'create santri', 'delete santri',
        'view users', 'delete users',
        'view activity log',
        'finalize nilai',
    ];

    foreach ($criticalPermissions as $permission) {
        expect($user->can($permission))->toBeTrue("Sysadmin harus punya permission: {$permission}");
    }
});

it('mudir hanya bisa baca tidak bisa hapus santri', function () {
    $user = User::factory()->create(['role' => 'mudir', 'is_active' => true]);
    $user->assignRole('mudir');

    expect($user->can('view santri'))->toBeTrue();
    expect($user->can('delete santri'))->toBeFalse();
    expect($user->can('create santri'))->toBeFalse();
});

it('kesantrian tidak bisa akses fitur akademik', function () {
    $user = User::factory()->create(['role' => 'kesantrian', 'is_active' => true]);
    $user->assignRole('kesantrian');

    expect($user->can('input nilai'))->toBeFalse();
    expect($user->can('manage jadwal'))->toBeFalse();
    expect($user->can('finalize nilai'))->toBeFalse();
});
