<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MerchantProduct extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'supplier_id',
        'distributor_id',
        'name',
        'sku',
        'barcode',
        'price',
        'cost',
        'stock_quantity',
        'unit',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (MerchantProduct $product) {
            if (\App\Services\StockMovementService::$skipProductEvents) {
                return;
            }

            if ((float) $product->stock_quantity > 0) {
                \App\Models\StockMovement::create([
                    'team_id'            => $product->team_id,
                    'merchant_product_id' => $product->id,
                    'movement_type'      => \App\Enums\StockMovementType::OPENING_BALANCE,
                    'direction'          => 'in',
                    'quantity'           => (float) $product->stock_quantity,
                    'unit_cost'          => (float) $product->cost,
                    'total_cost'         => (float) $product->stock_quantity * (float) $product->cost,
                    'quantity_before'    => 0.0,
                    'quantity_after'     => (float) $product->stock_quantity,
                    'notes'              => 'رصيد افتتاحي عند إنشاء المنتج',
                    'created_by'         => \Illuminate\Support\Facades\Auth::id(),
                ]);
            }
        });

        static::updated(function (MerchantProduct $product) {
            if (\App\Services\StockMovementService::$skipProductEvents) {
                return;
            }

            if ($product->wasChanged('stock_quantity')) {
                $old = (float) $product->getOriginal('stock_quantity');
                $new = (float) $product->stock_quantity;
                $diff = $new - $old;

                if (abs($diff) > 0.001) {
                    $type = $diff > 0 
                        ? \App\Enums\StockMovementType::ADJUSTMENT_ADD 
                        : \App\Enums\StockMovementType::ADJUSTMENT_REMOVE;

                    \App\Models\StockMovement::create([
                        'team_id'            => $product->team_id,
                        'merchant_product_id' => $product->id,
                        'movement_type'      => $type,
                        'direction'          => $type->direction(),
                        'quantity'           => abs($diff),
                        'unit_cost'          => (float) $product->cost,
                        'total_cost'         => abs($diff) * (float) $product->cost,
                        'quantity_before'    => $old,
                        'quantity_after'     => $new,
                        'notes'              => 'تسوية مخزون يدوية من صفحة تعديل المنتج',
                        'created_by'         => \Illuminate\Support\Facades\Auth::id(),
                    ]);
                }
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    public function posSaleItems(): HasMany
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PosSaleReturnItem::class);
    }

    public function latestCost(): float
    {
        return (float) ($this->cost ?? 0);
    }
}
