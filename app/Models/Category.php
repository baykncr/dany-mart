<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'association_categories',
        'priority_order',
    ];

    protected function casts(): array
    {
        return [
            'association_categories' => 'array',
            'priority_order'         => 'integer',
        ];
    }

    // Relations
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scope: ambil kategori asosiasi sebagai koleksi
    public function associatedCategories()
    {
        $ids = $this->association_categories;

        if(empty($ids)) {
            return collect(); // Kembalikan koleksi kosong jika tidak ada asosiasi
        }

        return static::whereIn('id', $ids)
                     ->orderBy('priority_order')
                     ->get();
    }
}