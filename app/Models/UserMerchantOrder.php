<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMerchantOrder extends Model
{
    protected $fillable = [
        'user_merchant_id',
        'user_id',
        'order_number',
        'note',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the merchant that owns the order.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the user that placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(UserMerchantOrderItem::class);
    }
}
