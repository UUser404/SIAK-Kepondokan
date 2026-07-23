<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Santri tidak pernah login ke sistem ini (tidak ada role 'santri' di RBAC —
 * lihat RolesPermissionsSeeder). Kolom santri.user_id sebelumnya NOT NULL,
 * yang berarti Admin\SantriController::store() (Tambah Santri lewat form)
 * selalu gagal dengan "NOT NULL constraint failed: santri.user_id" karena
 * form itu memang tidak (dan seharusnya tidak perlu) membuat akun User.
 * Kolom ini dibuat nullable; relasi user() tetap ada untuk kasus di masa
 * depan kalau santri butuh akun login sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
