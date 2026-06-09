<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosExchangeItem extends Model
{
    protected $fillable = [
        'pos_sale_return_id',
        'merchant_product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'unit_cost',
    ];

    protected $casts = [
        'quantity'    => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
        'unit_cost'   => 'decimal:2',
    ];

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(PosSaleReturn::class, 'pos_sale_return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class, 'merchant_product_id');
    }
}
