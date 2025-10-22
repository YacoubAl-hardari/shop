<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMerchantOrderItem extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'user_merchant_order_id',
        'user_merchant_product_id',
        'unit',
        'quantity',
        'price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(UserMerchantOrder::class, 'user_merchant_order_id');
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(UserMerchantProduct::class, 'user_merchant_product_id');
    }
}
