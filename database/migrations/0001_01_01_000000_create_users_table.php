<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat table users dengan kolom lengkap sesuai seeder
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique()->nullable(); // Username untuk login (opsional)
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', [
                'super_admin', 
                'admin', 
                'operator', 
                'mitra_middlestream', 
                'mitra_downstream', 
                'auditor',  // ← DITAMBAHKAN untuk auditor internal/eksternal
                'g_bim',    // Government BIM
                'g_esdm'    // Government ESDM
            ]);
            $table->unsignedBigInteger('partner_id')->nullable(); // Untuk mitra
            $table->string('nomor_pegawai')->nullable(); // Untuk internal PT Timah (admin, operator, auditor)
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('enable_2fa')->default(false); // Two-Factor Authentication
            $table->rememberToken();
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('email');
            $table->index('username');
            $table->index('role');
            $table->index('partner_id');
            $table->index('is_active');
            $table->index('nomor_pegawai');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};