<?php

namespace crocodicstudio\crudbooster\tests\fixtures\Panel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * No $fillable: the resource form is the whitelist.
 */
class Product extends Model
{
    protected $table = 'panel_products';

    protected $casts = ['is_active' => 'boolean', 'released_on' => 'date'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'panel_product_tag');
    }
}
