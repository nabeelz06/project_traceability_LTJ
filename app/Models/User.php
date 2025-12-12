<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

    // Relationships
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function createdBatches()
    {
        return $this->hasMany(Batch::class, 'created_by');
    }

    public function batchLogs()
    {
        return $this->hasMany(BatchLog::class, 'actor_user_id');
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class, 'user_id');
    }

    public function assignedShipments()
    {
        return $this->hasMany(Shipment::class, 'assigned_operator_id');
    }

    // Role Checking - Original Roles
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOperator()
    {
        return $this->role === 'operator';
    }

    public function isMitraMiddlestream()
    {
        return $this->role === 'mitra_middlestream';
    }

    public function isMitraDownstream()
    {
        return $this->role === 'mitra_downstream';
    }

    public function isGBIM()
    {
        return $this->role === 'g_bim';
    }

    public function isGESDM()
    {
        return $this->role === 'g_esdm';
    }

    public function isGovernment()
    {
        return in_array($this->role, ['g_bim', 'g_esdm']);
    }

    public function isMitra()
    {
        return in_array($this->role, ['mitra_middlestream', 'mitra_downstream']);
    }

    public function isInternal()
    {
        return in_array($this->role, ['super_admin', 'admin', 'operator']);
    }

    // NEW: Process Operator Roles
    public function isWetOperator()
    {
        return $this->role === 'wet_operator';
    }

    public function isDryOperator()
    {
        return $this->role === 'dry_operator';
    }

    public function isWarehouseOperator()
    {
        return $this->role === 'warehouse_operator';
    }

    public function isLabOperator()
    {
        return $this->role === 'lab_operator';
    }

    public function isProcessOperator()
    {
        return in_array($this->role, ['wet_operator', 'dry_operator', 'warehouse_operator', 'lab_operator']);
    }

    // Permission Checks
    public function canManageUsers()
    {
        return $this->isSuperAdmin();
    }

    public function canManagePartners()
    {
        return $this->isSuperAdmin();
    }

    public function canCreateParentBatch()
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isWetOperator();
    }

    public function canCreateChildBatch()
    {
        return $this->isMitraMiddlestream() || $this->isDryOperator();
    }

    public function canViewAuditLogs()
    {
        return $this->isSuperAdmin() || $this->isGovernment();
    }

    public function canCorrectBatch()
    {
        return $this->isSuperAdmin();
    }

    public function canExportReports()
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isGovernment();
    }

    // Helper Methods
    public function getRoleLabel()
    {
        $labels = [
            'super_admin' => 'Super Administrator',
            'admin' => 'Admin PT Timah',
            'operator' => 'Operator Lapangan',
            'wet_operator' => 'Operator Wet Process',
            'dry_operator' => 'Operator Dry Process',
            'warehouse_operator' => 'Operator Warehouse',
            'lab_operator' => 'Operator Lab/Project Plan',
            'mitra_middlestream' => 'Mitra - Pengolahan',
            'mitra_downstream' => 'Mitra - Industri Pengguna',
            'g_bim' => 'Regulator - BIM',
            'g_esdm' => 'Regulator - ESDM',
        ];

        return $labels[$this->role] ?? $this->role;
    }

    public function getRoleBadgeClass()
    {
        $classes = [
            'super_admin' => 'badge-danger',
            'admin' => 'badge-primary',
            'operator' => 'badge-info',
            'wet_operator' => 'badge-info',
            'dry_operator' => 'badge-info',
            'warehouse_operator' => 'badge-info',
            'lab_operator' => 'badge-info',
            'mitra_middlestream' => 'badge-warning',
            'mitra_downstream' => 'badge-success',
            'g_bim' => 'badge-dark',
            'g_esdm' => 'badge-dark',
        ];

        return $classes[$this->role] ?? 'badge-secondary';
    }

    public function getFullNameAttribute()
    {
        if ($this->isMitra() && $this->partner) {
            return $this->name . ' (' . $this->partner->name . ')';
        }
        return $this->name;
    }

    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function isActive()
    {
        return $this->is_active == true;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}