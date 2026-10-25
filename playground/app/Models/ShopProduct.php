<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopProduct extends Model
{
    protected $fillable = ['is_featured', 'stock'];

    protected $casts = ['is_featured' => 'boolean', 'available_on' => 'date', 'price' => 'decimal:2'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ShopCategory::class, 'shop_category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ShopReview::class);
    }
}
