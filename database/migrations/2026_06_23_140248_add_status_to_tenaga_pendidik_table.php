<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToTenagaPendidikTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenaga_pendidik', function (Blueprint $table) {
            // Menghapus ->after('nama') agar tidak ketergantungan kolom lain
            $table->string('status', 20)->default('aktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenaga_pendidik', function (Blueprint $table) {
            // Fungsi untuk menghapus kolom jika migrasi di-rollback
            $table->dropColumn('status');
        });
    }
}
