<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'miles_earned',
        'status',
        'shipping_address',
        'payment_method',
        'discount_miles',
        'discount_amount',
        'final_total',
    ];

    protected $casts = [
        'total'           => 'float',
        'miles_earned'    => 'integer',
        'discount_miles'  => 'integer',
        'discount_amount' => 'float',
        'final_total'     => 'float',
        'shipping_address'=> 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
