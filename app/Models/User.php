<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user has admin or manager role.
     */
    public function canManage(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user email is verified.
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get email verification status badge.
     */
    public function getEmailVerificationBadgeAttribute(): string
    {
        if ($this->isEmailVerified()) {
            return '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Verified</span>';
        }
        return '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>';
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope to get unverified users.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Get role display name.
     */
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'user' => 'User',
            default => 'Unknown'
        };
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomEmailVerificationNotification);
    }
}
