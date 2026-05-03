<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilesAccount extends Model
{
    protected $fillable = [
        'user_id',
        'tier_id',
        'balance',
        'lifetime_miles',
    ];

    protected $casts = [
        'balance'        => 'integer',
        'lifetime_miles' => 'integer',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tier()
    {
        return $this->belongsTo(Tier::class);
    }

    // Helpers
    public function getMultiplier(): float
    {
        return $this->tier->multiplier ?? 1.0;
    }

    public function hasSufficientBalance(int $amount): bool
    {
        return $this->balance >= $amount;
    }
}
