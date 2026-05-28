<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'name',
        'unit',
        'purchase_price',
        'selling_price',
        'stock',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'integer',
            'selling_price'  => 'integer',
            'stock'          => 'integer',
        ];
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopePopular(Builder $query):Builder
    {
        return $query->withSum('orderItems as total_sold', 'quantity')
                     ->orderByDesc('total_sold  DESC NULLS LAST');
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(ProductStockHistory::class);
    }

    // Helper: total terjual (untuk best-seller)
    public function totalSold(): int
    {
        return $this->orderItems()->sum('quantity');
    }
}