<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleReturnItem extends Model
{
    protected $fillable = [
        'pos_sale_return_id',
        'pos_sale_item_id',
        'merchant_product_id',
        'product_name',
        'quantity_returned',
        'unit_price',
        'total_price',
        'unit_cost',
        'return_reason',
        'item_condition',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'unit_price'        => 'decimal:2',
        'total_price'       => 'decimal:2',
        'unit_cost'         => 'decimal:2',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(PosSaleReturn::class, 'pos_sale_return_id');
    }

    public function originalItem(): BelongsTo
    {
        return $this->belongsTo(PosSaleItem::class, 'pos_sale_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class, 'merchant_product_id');
    }

    public function isResellable(): bool
    {
        return $this->item_condition === 'resellable';
    }
}
