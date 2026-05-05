<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Review;

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

    protected $appends = ['image_url', 'average_rating', 'reviews_count'];

    // ─── Relaciones ───────────────────────────────────────

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

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ─── Accessors ────────────────────────────────────────

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;

        // URL completa de Cloudinary
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        // Public ID de Cloudinary
        if ($this->image_public_id) {
            return cloudinary()->image($this->image_public_id)->toUrl();
        }

        // Fallback storage local (desarrollo)
        return asset('storage/' . $this->image);
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    // ─── Helpers ──────────────────────────────────────────

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function calculateMiles(float $multiplier = 1.0): int
    {
        return (int) round($this->price * $this->miles_per_dollar * $multiplier);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query
            ->where('active', true)
            ->where('stock', '>', 0);
    }
}
