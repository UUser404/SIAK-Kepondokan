<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ==========================================
        // PPDB (Penerimaan Peserta Didik Baru)
        // ==========================================
        Schema::create('ppdb_periode', function (Blueprint $table) {
            $table->id();
            $table->string('nama');                         // e.g. "PPDB 2025/2026"
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->date('tanggal_buka');
            $table->date('tanggal_tutup');
            $table->integer('kuota')->default(0);
            $table->boolean('is_active')->default(false);
            $table->text('persyaratan')->nullable();        // JSON atau teks
            $table->timestamps();
        });

        Schema::create('ppdb_pendaftar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppdb_periode_id')->constrained('ppdb_periode')->cascadeOnDelete();
            $table->string('nomor_daftar')->unique();
            // Data Calon Santri
            $table->string('nama_lengkap');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('asal_sekolah')->nullable();
            $table->string('nisn')->nullable();
            // Data Wali
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('no_hp_wali');
            $table->string('email_wali')->nullable();
            $table->text('alamat')->nullable();
            // Status
            $table->enum('status', ['menunggu', 'verifikasi', 'diterima', 'ditolak', 'mengundurkan_diri'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            // Konversi ke santri
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();
            $table->timestamps();
        });

        // ==========================================
        // Surat Menyurat
        // ==========================================
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->longText('konten');                     // Template dengan placeholder
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_surat_id')->nullable()->constrained('template_surat')->nullOnDelete();
            $table->string('nomor_surat')->unique();
            $table->string('perihal');
            $table->string('ditujukan_kepada')->nullable();
            $table->foreignId('santri_id')->nullable()->constrained('santri')->nullOnDelete();
            $table->date('tanggal_surat');
            $table->longText('konten');
            $table->enum('status', ['draft', 'diterbitkan'])->default('draft');
            $table->foreignId('dibuat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ==========================================
        // AI Advisor Log
        // ==========================================
        Schema::create('ai_conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->text('question');
            $table->text('answer');
            $table->string('model_used')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->boolean('guard_applied')->default(false);
            $table->json('guard_issues')->nullable();
            $table->timestamps();
        });

        // ==========================================
        // Activity Log
        // ==========================================
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');                       // e.g. "santri.created"
            $table->string('model_type')->nullable();       // e.g. "App\Models\Santri"
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // ==========================================
        // Notifications
        // ==========================================
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('ai_conversation_logs');
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('template_surat');
        Schema::dropIfExists('ppdb_pendaftar');
        Schema::dropIfExists('ppdb_periode');
    }
};
