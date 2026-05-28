<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'payment_method',
        'payment_amount',
        'change_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_amount'   => 'integer',
            'payment_amount' => 'integer',
            'change_amount'  => 'integer',
        ];
    }

    // Auto-generate order number
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd');
        $last   = static::whereDate('created_at', today())
                        ->orderByDesc('id')
                        ->first();

        $seq = $last ? (int) substr($last->order_number, -4) + 1 : 1;

        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}