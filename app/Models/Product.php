<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'miles_per_dollar',
        'active',
        'image',
        'image_public_id',
    ];

    protected $casts = [
        'price'            => 'float',
        'stock'            => 'integer',
        'miles_per_dollar' => 'integer',
        'active'           => 'boolean',
    ];

    protected $appends = ['image_url'];

    // ─── Relaciones ───────────────────────────────

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ─── Accessor imagen ──────────────────────────

public function getImageUrlAttribute(): ?string
{
    if (!$this->image) return null;

    // URL completa — ya es una URL válida
    if (str_starts_with($this->image, 'http')) {
        return $this->image;
    }

    // Storage local — evitar /storage/storage/
    $path = ltrim($this->image, '/');
    $path = str_replace('storage/', '', $path);
    return url('storage/' . $path);
}

    // ─── Helpers ──────────────────────────────────

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function calculateMiles(float $multiplier = 1.0): int
    {
        return (int) round($this->price * $this->miles_per_dollar * $multiplier);
    }

    // ─── Scopes ───────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query
            ->where('active', true)
            ->where('stock', '>', 0);
    }
}
