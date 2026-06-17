<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache setiap test
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Seed semua role
        $roles = ['mudir', 'wakil_kurikulum', 'guru', 'kesantrian', 'admin', 'sysadmin'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
