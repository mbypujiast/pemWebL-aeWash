<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // Urutan tahapan proses (dipakai progress bar & validasi)
    public const STATUSES = [
        'diterima'   => 'Diterima',
        'dicuci'     => 'Dicuci',
        'disetrika'  => 'Disetrika',
        'siap_ambil' => 'Siap Ambil',
    ];

    protected $fillable = [
        'invoice_number', 'customer_id', 'staff_id', 'status', 'payment_status',
        'total_weight', 'total_price', 'order_date', 'estimated_finish_date',
        'paid_at', 'notes',
    ];

    protected $casts = [
        'order_date'            => 'date',
        'estimated_finish_date' => 'date',
        'paid_at'               => 'datetime',
        'total_weight'          => 'decimal:2',
        'total_price'           => 'decimal:2',
    ];

    // ---- Relasi ----
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class)->orderBy('created_at');
    }

    // ---- Accessor ----
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Persentase progress bar: diterima=25, dicuci=50, disetrika=75, siap_ambil=100 */
    public function getProgressPercentAttribute(): int
    {
        $index = array_search($this->status, array_keys(self::STATUSES));
        return (int) (($index + 1) / count(self::STATUSES) * 100);
    }

    /** Kode status tahap berikutnya, atau null jika sudah tahap terakhir */
    public function getNextStatusAttribute(): ?string
    {
        $keys  = array_keys(self::STATUSES);
        $index = array_search($this->status, $keys);

        return $keys[$index + 1] ?? null;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'lunas';
    }

    // ---- Scope ----
    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    // ---- Invoice ----
    /**
     * Format: INV-YYYYMMDD-XXX (nomor urut naik per hari).
     * Panggil di dalam DB::transaction() agar aman dari order yang masuk bersamaan.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $last = static::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->lockForUpdate()
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
