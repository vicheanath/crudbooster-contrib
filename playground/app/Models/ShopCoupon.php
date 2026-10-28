<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopCoupon extends Model
{
    protected $fillable = ['is_active'];

    protected $casts = ['discount_value' => 'decimal:2', 'expires_on' => 'date', 'is_active' => 'boolean'];
}
