<?php

/*
|--------------------------------------------------------------------------
| Jembatan Pengujian Pest & Laravel 11
|--------------------------------------------------------------------------
*/

uses(
    Tests\TestCase::class,
    Illuminate\Foundation\Testing\RefreshDatabase::class,
)->beforeEach(function () {
    // 1. Bersihkan sisa-sisa cache Spatie agar tidak merusak lingkungan testing
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // 2. KUNCI SUKSES: Hanya panggil seeder Spatie, abaikan UsersSeeder agar tidak tabrakan email!
    $this->seed(\Database\Seeders\RolesPermissionsSeeder::class);
})->in('Feature');
