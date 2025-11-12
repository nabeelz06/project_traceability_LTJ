<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model User - User management dengan role-based access
 * Support roles: super_admin, admin, operator, mitra_middlestream, mitra_downstream, g_bim, g_esdm
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'partner_id',
        'nomor_pegawai',
        'phone',
        'is_active',
        'last_login_at',
        'device_id',
        'verification_doc',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * User terkait partner mana (untuk role mitra)
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    /**
     * Batches yang dibuat oleh user ini
     */
    public function createdBatches()
    {
        return $this->hasMany(Batch::class, 'created_by_user_id');
    }

    /**
     * Batch logs dari user ini
     */
    public function batchLogs()
    {
        return $this->hasMany(BatchLog::class, 'actor_user_id');
    }

    /**
     * System logs dari user ini
     */
    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class, 'user_id');
    }

    /**
     * Shipments assigned ke user ini (untuk operator)
     */
    public function assignedShipments()
    {
        return $this->hasMany(Shipment::class, 'assigned_operator_id');
    }

    // ============================================
    // ROLE CHECKING METHODS (Optimized)
    // ============================================

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is Admin (PT Timah)
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is Operator
     */
    public function isOperator()
    {
        return $this->role === 'operator';
    }

    /**
     * Check if user is Mitra Middlestream
     */
    public function isMitraMiddlestream()
    {
        return $this->role === 'mitra_middlestream';
    }

    /**
     * Check if user is Mitra Downstream
     */
    public function isMitraDownstream()
    {
        return $this->role === 'mitra_downstream';
    }

    /**
     * Check if user is Government BIM
     */
    public function isGBIM()
    {
        return $this->role === 'g_bim';
    }

    /**
     * Check if user is Government ESDM
     */
    public function isGESDM()
    {
        return $this->role === 'g_esdm';
    }

    /**
     * Check if user is any government role (G:BIM atau G:ESDM)
     */
    public function isGovernment()
    {
        return in_array($this->role, ['g_bim', 'g_esdm']);
    }

    /**
     * Check if user is any mitra role
     */
    public function isMitra()
    {
        return in_array($this->role, ['mitra_middlestream', 'mitra_downstream']);
    }

    /**
     * Check if user is internal PT Timah (super_admin, admin, operator)
     */
    public function isInternal()
    {
        return in_array($this->role, ['super_admin', 'admin', 'operator']);
    }

    // ============================================
    // PERMISSION CHECKS
    // ============================================

    /**
     * Check if user can manage users
     */
    public function canManageUsers()
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can manage partners
     */
    public function canManagePartners()
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can create batch induk
     */
    public function canCreateParentBatch()
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Check if user can create child batch
     */
    public function canCreateChildBatch()
    {
        return $this->isMitraMiddlestream();
    }

    /**
     * Check if user can view audit logs
     */
    public function canViewAuditLogs()
    {
        return $this->isSuperAdmin() || $this->isGovernment();
    }

    /**
     * Check if user can correct batch data
     */
    public function canCorrectBatch()
    {
        return $this->isSuperAdmin();
    }

    /**
     * Check if user can export reports
     */
    public function canExportReports()
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isGovernment();
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Get role display name (Bahasa Indonesia)
     */
    public function getRoleLabel()
    {
        $labels = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin PT Timah',
            'operator' => 'Operator Lapangan',
            'mitra_middlestream' => 'Mitra - Pengolahan',
            'mitra_downstream' => 'Mitra - Industri Pengguna',
            'g_bim' => 'Regulator - BIM',
            'g_esdm' => 'Regulator - ESDM',
        ];

        return $labels[$this->role] ?? $this->role;
    }

    /**
     * Get role badge class untuk UI
     */
    public function getRoleBadgeClass()
    {
        $classes = [
            'super_admin' => 'badge-danger',
            'admin' => 'badge-primary',
            'operator' => 'badge-info',
            'mitra_middlestream' => 'badge-warning',
            'mitra_downstream' => 'badge-success',
            'g_bim' => 'badge-dark',
            'g_esdm' => 'badge-dark',
        ];

        return $classes[$this->role] ?? 'badge-secondary';
    }

    /**
     * Get full display name dengan partner (untuk mitra)
     */
    public function getFullNameAttribute()
    {
        if ($this->isMitra() && $this->partner) {
            return $this->name . ' (' . $this->partner->name . ')';
        }
        return $this->name;
    }

    /**
     * Get initials untuk avatar
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->is_active == true;
    }

    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}