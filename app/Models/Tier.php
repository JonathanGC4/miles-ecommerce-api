<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    protected $fillable = [
        'name',
        'min_miles',
        'multiplier',
        'benefits',
    ];

    protected $casts = [
        'benefits'   => 'array',
        'multiplier' => 'float',
        'min_miles'  => 'integer',
    ];

    // Relaciones
    public function milesAccounts()
    {
        return $this->hasMany(MilesAccount::class);
    }

    // Obtener el tier correcto según millas acumuladas
    public static function getForMiles(int $lifetimeMiles): self
    {
        return self::where('min_miles', '<=', $lifetimeMiles)
                   ->orderByDesc('min_miles')
                   ->firstOrFail();
    }
}
