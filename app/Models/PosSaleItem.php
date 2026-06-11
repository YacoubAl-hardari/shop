<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSaleItem extends Model
{
    protected $fillable = [
        'pos_sale_id',
        'merchant_product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function posSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class);
    }

    public function merchantProduct(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PosSaleReturnItem::class, 'pos_sale_item_id');
    }

    public function returnedQuantity(): float
    {
        if ($this->relationLoaded('returnItems')) {
            return (float) $this->returnItems
                ->filter(fn (PosSaleReturnItem $item) => $item->saleReturn?->status === 'completed')
                ->sum('quantity_returned');
        }

        return (float) PosSaleReturnItem::query()
            ->where('pos_sale_item_id', $this->id)
            ->whereHas('saleReturn', fn ($q) => $q->where('status', 'completed'))
            ->sum('quantity_returned');
    }

    public function returnableQuantity(): float
    {
        return max(0, (float) $this->quantity - $this->returnedQuantity());
    }

    public function hasBeenReturned(): bool
    {
        return $this->returnedQuantity() > 0;
    }

    public function isFullyReturned(): bool
    {
        return $this->returnedQuantity() >= (float) $this->quantity;
    }
}
