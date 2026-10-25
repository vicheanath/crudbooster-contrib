<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopReview extends Model
{
    protected $fillable = ['is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'shop_product_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class, 'shop_customer_id');
    }
}
