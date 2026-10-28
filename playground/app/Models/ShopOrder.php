<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShopOrder extends Model
{
    public const STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = ['status', 'total'];

    protected $casts = ['total' => 'decimal:2', 'placed_at' => 'datetime'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class, 'shop_customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class);
    }

    /** Sets the total from the order's items. */
    public function recalculateTotal(): void
    {
        $this->update(['total' => $this->items()->selectRaw('coalesce(sum(quantity * unit_price), 0) as total')->value('total')]);
    }
}
