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
                $movement = \App\Models\StockMovement::create([
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

                // ترحيل قيد محاسبي للرصيد الافتتاحي
                try {
                    $totalCost = (float) $product->stock_quantity * (float) $product->cost;
                    if ($totalCost > 0 && $product->team) {
                        $entry = app(\App\Services\AccountingService::class)->post(
                            $product->team,
                            [
                                ['account_code' => '1201', 'debit_amount' => $totalCost, 'description' => 'رصيد افتتاحي للمنتج — '.$product->name],
                                ['account_code' => '3001', 'credit_amount' => $totalCost, 'description' => 'رصيد افتتاحي للمنتج — '.$product->name],
                            ],
                            'إثبات رصيد افتتاحي مخزون — '.$product->name,
                            MerchantProduct::class,
                            $product->id
                        );
                        $movement->update(['journal_entry_id' => $entry->id]);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not post opening balance journal entry for product {$product->id}: " . $e->getMessage());
                }
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

                    $movement = \App\Models\StockMovement::create([
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

                    // ترحيل القيد المحاسبي للتسوية
                    try {
                        $totalCost = abs($diff) * (float) $product->cost;
                        if ($totalCost > 0 && $product->team) {
                            $accountingService = app(\App\Services\AccountingService::class);
                            if ($diff > 0) {
                                // إضافة تسوية: مدين 1201 مخزون / دائن 4005 إيرادات متنوعة
                                $entry = $accountingService->post(
                                    $product->team,
                                    [
                                        ['account_code' => '1201', 'debit_amount' => $totalCost, 'description' => 'زيادة مخزون تسوية يدوية — '.$product->name],
                                        ['account_code' => '4005', 'credit_amount' => $totalCost, 'description' => 'إيراد تسوية مخزون — '.$product->name],
                                    ],
                                    'تسوية مخزون يدوية (إضافة) — '.$product->name,
                                    \App\Models\StockMovement::class,
                                    $movement->id
                                );
                            } else {
                                // خصم تسوية: مدين 5001 تكلفة / دائن 1201 مخزون
                                $entry = $accountingService->post(
                                    $product->team,
                                    [
                                        ['account_code' => '5001', 'debit_amount' => $totalCost, 'description' => 'عجز مخزون تسوية يدوية — '.$product->name],
                                        ['account_code' => '1201', 'credit_amount' => $totalCost, 'description' => 'خصم مخزون تسوية يدوية — '.$product->name],
                                    ],
                                    'تسوية مخزون يدوية (خصم) — '.$product->name,
                                    \App\Models\StockMovement::class,
                                    $movement->id
                                );
                            }
                            $movement->update(['journal_entry_id' => $entry->id]);
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Could not post manual stock adjustment journal entry for movement {$movement->id}: " . $e->getMessage());
                    }
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
