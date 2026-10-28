<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['name', 'price_per_kg', 'duration_days', 'is_express', 'is_active'];

    protected $casts = [
        'price_per_kg' => 'decimal:2',
        'is_express'   => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
