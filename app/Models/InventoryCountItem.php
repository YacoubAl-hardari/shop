<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCountItem extends Model
{
    protected $fillable = [
        'inventory_count_id',
        'merchant_product_id',
        'product_name',
        'unit',
        'book_quantity',
        'counted_quantity',
        'variance_quantity',
        'unit_cost',
        'book_value',
        'counted_value',
        'variance_value',
        'notes',
    ];

    protected $casts = [
        'book_quantity'     => 'decimal:2',
        'counted_quantity'  => 'decimal:2',
        'variance_quantity' => 'decimal:2',
        'unit_cost'         => 'decimal:2',
        'book_value'        => 'decimal:2',
        'counted_value'     => 'decimal:2',
        'variance_value'    => 'decimal:2',
    ];

    public function inventoryCount(): BelongsTo
    {
        return $this->belongsTo(InventoryCount::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class, 'merchant_product_id');
    }

    public function getSoldQuantity(): float
    {
        $fiscalYear = $this->inventoryCount?->fiscal_year ?? now()->year;

        $outgoing = \App\Models\StockMovement::where('merchant_product_id', $this->merchant_product_id)
            ->whereYear('created_at', $fiscalYear)
            ->whereIn('movement_type', [
                \App\Enums\StockMovementType::SALE,
                \App\Enums\StockMovementType::EXCHANGE_OUT
            ])
            ->sum('quantity');

        $incoming = \App\Models\StockMovement::where('merchant_product_id', $this->merchant_product_id)
            ->whereYear('created_at', $fiscalYear)
            ->whereIn('movement_type', [
                \App\Enums\StockMovementType::SALE_RETURN,
                \App\Enums\StockMovementType::EXCHANGE_IN
            ])
            ->sum('quantity');

        return (float) ($outgoing - $incoming);
    }

    public function getDamagedQuantity(): float
    {
        $fiscalYear = $this->inventoryCount?->fiscal_year ?? now()->year;

        return (float) \App\Models\StockMovement::where('merchant_product_id', $this->merchant_product_id)
            ->whereYear('created_at', $fiscalYear)
            ->where('movement_type', \App\Enums\StockMovementType::WRITE_OFF)
            ->sum('quantity');
    }

    public function hasVariance(): bool
    {
        return $this->counted_quantity !== null
            && bccomp((string) $this->counted_quantity, (string) $this->book_quantity, 2) !== 0;
    }

    public function isGain(): bool
    {
        return (float) $this->variance_quantity > 0;
    }

    public function isLoss(): bool
    {
        return (float) $this->variance_quantity < 0;
    }

    /**
     * Recalculate variance fields after counted_quantity is set.
     */
    public function recalculate(): void
    {
        if ($this->counted_quantity === null) {
            return;
        }

        $this->variance_quantity = (float) $this->counted_quantity - (float) $this->book_quantity;
        $this->book_value        = (float) $this->book_quantity * (float) $this->unit_cost;
        $this->counted_value     = (float) $this->counted_quantity * (float) $this->unit_cost;
        $this->variance_value    = (float) $this->variance_quantity * (float) $this->unit_cost;
    }
}
