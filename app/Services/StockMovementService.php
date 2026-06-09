<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\MerchantProduct;
use App\Models\StockMovement;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockMovementService
{
    public static bool $skipProductEvents = false;

    /**
     * تسجيل حركة مخزون وتحديث رصيد المنتج.
     *
     * @param  Model|null  $reference  الوثيقة المرجعية (PosSale, PosSaleReturn, InventoryCount, …)
     */
    public function record(
        Team $team,
        MerchantProduct $product,
        StockMovementType $type,
        float $quantity,
        float $unitCost = 0,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $journalEntryId = null,
    ): StockMovement {
        static::$skipProductEvents = true;

        try {
            $quantityBefore = (float) $product->stock_quantity;
            $direction = $type->direction();
            $totalCost = $quantity * $unitCost;

            // تحديث الرصيد
            if ($direction === 'in') {
                $product->increment('stock_quantity', $quantity);
            } else {
                $product->decrement('stock_quantity', $quantity);
            }

            $quantityAfter = (float) $product->fresh()->stock_quantity;

            return StockMovement::create([
                'team_id'            => $team->id,
                'merchant_product_id' => $product->id,
                'movement_type'      => $type,
                'direction'          => $direction,
                'quantity'           => $quantity,
                'unit_cost'          => $unitCost > 0 ? $unitCost : (float) $product->cost,
                'total_cost'         => $totalCost > 0 ? $totalCost : $quantity * (float) $product->cost,
                'quantity_before'    => $quantityBefore,
                'quantity_after'     => $quantityAfter,
                'reference_type'     => $reference ? get_class($reference) : null,
                'reference_id'       => $reference?->id,
                'journal_entry_id'   => $journalEntryId,
                'notes'              => $notes,
                'created_by'         => Auth::id(),
            ]);
        } finally {
            static::$skipProductEvents = false;
        }
    }

    /**
     * تسجيل إضافة يدوية للمخزون (شراء / استلام).
     */
    public function recordPurchase(
        Team $team,
        MerchantProduct $product,
        float $quantity,
        float $unitCost,
        ?Model $reference = null,
        ?string $notes = null,
    ): StockMovement {
        // تحديث متوسط التكلفة
        $this->updateAverageCost($product, $quantity, $unitCost);

        return $this->record($team, $product, StockMovementType::PURCHASE, $quantity, $unitCost, $reference, $notes);
    }

    /**
     * تسجيل بيع (صرف من المخزون).
     */
    public function recordSale(
        Team $team,
        MerchantProduct $product,
        float $quantity,
        ?Model $reference = null,
    ): StockMovement {
        return $this->record(
            $team, $product,
            StockMovementType::SALE,
            $quantity,
            (float) $product->cost,
            $reference,
        );
    }

    /**
     * تسجيل إرجاع من عميل (إدخال للمخزون).
     */
    public function recordSaleReturn(
        Team $team,
        MerchantProduct $product,
        float $quantity,
        float $unitCost,
        ?Model $reference = null,
    ): StockMovement {
        return $this->record(
            $team, $product,
            StockMovementType::SALE_RETURN,
            $quantity,
            $unitCost,
            $reference,
        );
    }

    /**
     * تسجيل تسوية يدوية.
     */
    public function recordAdjustment(
        Team $team,
        MerchantProduct $product,
        float $quantity,        // موجب = إضافة، سالب = خصم
        ?string $notes = null,
        ?Model $reference = null,
    ): StockMovement {
        $type = $quantity > 0
            ? StockMovementType::ADJUSTMENT_ADD
            : StockMovementType::ADJUSTMENT_REMOVE;

        return $this->record($team, $product, $type, abs($quantity), (float) $product->cost, $reference, $notes);
    }

    /**
     * تحديث متوسط التكلفة المرجح عند الشراء.
     */
    protected function updateAverageCost(MerchantProduct $product, float $newQty, float $newCost): void
    {
        $currentQty  = (float) $product->stock_quantity;
        $currentCost = (float) $product->cost;

        if ($currentQty + $newQty <= 0) {
            return;
        }

        $avgCost = (($currentQty * $currentCost) + ($newQty * $newCost)) / ($currentQty + $newQty);
        $product->update(['cost' => round($avgCost, 4)]);
    }
}
