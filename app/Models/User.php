<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Menggunakan guarded agar seeder bisa mengisi semua kolom kustom.
     */
    protected $guarded = [];

    /**
     * Atribut yang harus disembunyikan.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mengubah tipe data kolom kustom.
     * (Menggunakan properti $casts gaya L10 agar konsisten)
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'enable_2fa' => 'boolean',
        'password' => 'hashed', // Otomatis hash jika di-update
    ];

    // === FUNGSI PENGECUALIAN ROLE ===
    // Ini akan memperbaiki error 'isSuperAdmin() not found'

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isMitraMiddlestream(): bool
    {
        return $this->role === 'mitra_middlestream';
    }

    public function isMitraDownstream(): bool
    {
        return $this->role === 'mitra_downstream';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    // === RELASI DATABASE ===

    /**
     * Mendapatkan data partner jika user ini adalah mitra.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}