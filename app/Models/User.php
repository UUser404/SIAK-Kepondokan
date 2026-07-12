<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ==========================================
    // Relations
    // ==========================================

    public function tenagaPendidik()
    {
        return $this->hasOne(TenagaPendidik::class);
    }

    public function penugasanMengajar()
    {
        return $this->hasMany(PenugasanMengajar::class, 'guru_id');
    }

    public function santri()
    {
        return $this->hasOne(Santri::class);
    }

    // ==========================================
    // Role Helpers
    // ==========================================

    public function isMudir(): bool
    {
        return $this->role === 'mudir';
    }

    public function isWakilKurikulum(): bool
    {
        return $this->role === 'wakil_kurikulum';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isKesantrian(): bool
    {
        return $this->role === 'kesantrian';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSysAdmin(): bool
    {
        return $this->role === 'sysadmin';
    }

    public function isManajemen(): bool
    {
        return in_array($this->role, ['mudir', 'wakil_kurikulum', 'sysadmin']);
    }

    /**
     * Roles yang bisa akses semua data santri
     */
    public function canViewAllSantri(): bool
    {
        return in_array($this->role, ['mudir', 'wakil_kurikulum', 'kesantrian', 'admin', 'sysadmin']);
    }

    /**
     * Dashboard redirect berdasarkan role
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            'mudir'            => 'mudir.dashboard',
            'wakil_kurikulum'  => 'kurikulum.dashboard',
            'guru'             => 'guru.dashboard',
            'kesantrian'       => 'kesantrian.dashboard',
            'admin'            => 'admin.dashboard',
            'sysadmin'         => 'sysadmin.dashboard',
            default            => 'dashboard',
        };
    }
}
