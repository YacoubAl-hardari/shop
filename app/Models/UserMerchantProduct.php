<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMerchantProduct extends Model
{
    protected $fillable = [
        'user_merchant_id',
        'name',
        'price',
        'barcode',
        'description',
        'image',
        'brand',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the merchant that owns the product.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(UserMerchantOrderItem::class);
    }
}
