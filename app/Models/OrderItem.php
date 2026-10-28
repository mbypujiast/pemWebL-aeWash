<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'service_id', 'fabric_type',
        'weight_kg', 'price_per_kg', 'subtotal',
    ];

    protected $casts = [
        'weight_kg'    => 'decimal:2',
        'price_per_kg' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
