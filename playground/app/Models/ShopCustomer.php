<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopCustomer extends Model
{
    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShopReview::class);
    }
}
