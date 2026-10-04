<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    /** Auditor atau Super Admin. */
    public function canAudit(): bool
    {
        return in_array($this->role, [UserRole::Auditor, UserRole::SuperAdmin], true);
    }

    /** Admin atau Super Admin. */
    public function hasAdminPowers(): bool
    {
        return in_array($this->role, [UserRole::Admin, UserRole::SuperAdmin], true);
    }

    /** Bendahara, Admin, atau Super Admin. */
    public function canManageCampaigns(): bool
    {
        return in_array($this->role, [UserRole::Bendahara, UserRole::Admin, UserRole::SuperAdmin], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isBendahara(): bool
    {
        return $this->role === UserRole::Bendahara;
    }

    public function isAuditor(): bool
    {
        return $this->role === UserRole::Auditor;
    }
}
