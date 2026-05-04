<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;
use App\Models\CartItem;

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
    ];

    protected $casts = [
        'price'           => 'float',
        'stock'           => 'integer',
        'miles_per_dollar'=> 'integer',
        'active'          => 'boolean',
    ];

    // Relaciones
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Helpers
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function calculateMiles(float $multiplier = 1.0): int
    {
        return (int) round($this->price * $this->miles_per_dollar * $multiplier);
    }

    // Solo productos activos con stock
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query
            ->where('active', 1)
            ->whereNotNull('stock')
            ->where('stock', '>', 0);
    }
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;

        return asset('storage/' . $this->image);
    }
}
