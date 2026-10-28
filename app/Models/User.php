<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ---- Relasi ----
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function handledOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'staff_id');
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'changed_by');
    }

    // ---- Helper role ----
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isStaff(): bool    { return $this->role === 'staff'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    /** Nama route halaman utama sesuai role (dipakai setelah login) */
    public function homeRoute(): string
    {
        return match ($this->role) {
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            default => 'customer.orders',
        };
    }
}
