<?php

namespace App\Services;

use App\Enums\InventoryCountStatus;
use App\Enums\StockMovementType;
use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Models\MerchantProduct;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryCountService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected StockMovementService $stockMovementService,
    ) {}

    /**
     * إنشاء جلسة جرد جديدة وتحميل الكميات الدفترية تلقائياً.
     */
    public function createCount(Team $team, \DateTimeInterface $countDate, int $fiscalYear): InventoryCount
    {
        return DB::transaction(function () use ($team, $countDate, $fiscalYear) {
            $count = InventoryCount::create([
                'team_id'     => $team->id,
                'count_number' => $this->generateCountNumber($team),
                'count_date'  => $countDate,
                'fiscal_year' => $fiscalYear,
                'status'      => InventoryCountStatus::DRAFT,
                'created_by'  => Auth::id(),
            ]);

            // تحميل جميع منتجات الفريق مع كمياتها الدفترية
            $products = MerchantProduct::where('team_id', $team->id)
                ->where('is_active', true)
                ->get();

            foreach ($products as $product) {
                InventoryCountItem::create([
                    'inventory_count_id' => $count->id,
                    'merchant_product_id' => $product->id,
                    'product_name'       => $product->name,
                    'unit'               => $product->unit,
                    'book_quantity'      => (float) $product->stock_quantity,
                    'counted_quantity'   => null, // لم تُعدَّ بعد
                    'variance_quantity'  => 0,
                    'unit_cost'          => (float) $product->cost,
                    'book_value'         => (float) $product->stock_quantity * (float) $product->cost,
                    'counted_value'      => 0,
                    'variance_value'     => 0,
                ]);
            }

            return $count->load('items');
        });
    }

    /**
     * تحديث كمية مُعدَّة لصنف محدد.
     */
    public function updateCountedQuantity(
        InventoryCountItem $item,
        float $countedQty,
        ?string $notes = null,
    ): InventoryCountItem {
        if (! $item->inventoryCount->isEditable()) {
            throw new InvalidArgumentException('لا يمكن تعديل جرد معتمد أو مغلق');
        }

        $item->counted_quantity = $countedQty;
        $item->variance_quantity = $countedQty - (float) $item->book_quantity;
        $item->counted_value = $countedQty * (float) $item->unit_cost;
        $item->variance_value = $item->variance_quantity * (float) $item->unit_cost;
        if ($notes) {
            $item->notes = $notes;
        }
        $item->save();

        return $item;
    }

    /**
     * إنهاء إدخال الكميات ونقل الجرد لمرحلة "مكتمل".
     */
    public function completeCount(InventoryCount $count): InventoryCount
    {
        if (! $count->isEditable()) {
            throw new InvalidArgumentException('الجرد في حالة لا تسمح بالإكمال');
        }

        $count->status = InventoryCountStatus::COMPLETED;
        $count->total_book_value    = $count->items()->sum('book_value');
        $count->total_counted_value = $count->items()->whereNotNull('counted_quantity')->sum('counted_value');
        $count->variance_value      = $count->total_counted_value - $count->total_book_value;
        $count->save();

        return $count;
    }

    /**
     * اعتماد الجرد وترحيل قيود التسوية وتحديث أرصدة المنتجات.
     */
    public function approveAndPost(InventoryCount $count, Team $team): InventoryCount
    {
        if (! $count->canBeApproved()) {
            throw new InvalidArgumentException('يجب إكمال الجرد قبل الاعتماد');
        }

        return DB::transaction(function () use ($count, $team) {
            $gainLines = [];
            $lossLines = [];
            $totalGain = 0.0;
            $totalLoss = 0.0;

            foreach ($count->items()->whereNotNull('counted_quantity')->get() as $item) {
                $variance = (float) $item->variance_quantity;
                if (abs($variance) < 0.001) {
                    continue; // لا فارق
                }

                $product = MerchantProduct::find($item->merchant_product_id);
                if (! $product) {
                    continue;
                }

                if ($variance > 0) {
                    // فائض جرد: إضافة للمخزون
                    $this->stockMovementService->record(
                        $team, $product,
                        StockMovementType::INVENTORY_GAIN,
                        $variance,
                        (float) $item->unit_cost,
                        $count,
                    );
                    $totalGain += (float) $item->variance_value;
                } else {
                    // عجز جرد: خصم من المخزون
                    $this->stockMovementService->record(
                        $team, $product,
                        StockMovementType::INVENTORY_LOSS,
                        abs($variance),
                        (float) $item->unit_cost,
                        $count,
                    );
                    $totalLoss += abs((float) $item->variance_value);
                }
            }

            // قيد التسوية المحاسبية
            $journalEntry = null;
            $journalLines = [];

            if ($totalGain > 0) {
                // فائض: مدين 1201 مخزون / دائن 4005 إيرادات متنوعة
                $journalLines[] = ['account_code' => '1201', 'debit_amount'  => $totalGain, 'description' => 'فائض جرد — '.$count->count_number];
                $journalLines[] = ['account_code' => '4005', 'credit_amount' => $totalGain, 'description' => 'إيراد فائض جرد — '.$count->count_number];
            }

            if ($totalLoss > 0) {
                // عجز: مدين 5001 تكلفة / دائن 1201 مخزون
                $journalLines[] = ['account_code' => '5001', 'debit_amount'  => $totalLoss, 'description' => 'عجز جرد — '.$count->count_number];
                $journalLines[] = ['account_code' => '1201', 'credit_amount' => $totalLoss, 'description' => 'خصم مخزون جرد — '.$count->count_number];
            }

            if (! empty($journalLines)) {
                $journalEntry = $this->accountingService->post(
                    $team, $journalLines,
                    'تسوية جرد سنوي — '.$count->count_number,
                    InventoryCount::class, $count->id,
                );
            }

            $count->update([
                'status'          => InventoryCountStatus::APPROVED,
                'journal_entry_id' => $journalEntry?->id,
                'approved_by'     => Auth::id(),
                'approved_at'     => now(),
                'total_book_value'    => $count->items()->sum('book_value'),
                'total_counted_value' => $count->items()->whereNotNull('counted_quantity')->sum('counted_value'),
                'variance_value'      => $totalGain - $totalLoss,
            ]);

            return $count;
        });
    }

    public function repostJournalEntry(InventoryCount $count, Team $team): \App\Models\JournalEntry
    {
        if ($count->status !== InventoryCountStatus::APPROVED) {
            throw new \InvalidArgumentException('لا يمكن إعادة ترحيل قيد لجرد غير معتمد.');
        }

        $count->load('journalEntry');
        if ($count->journalEntry && $count->journalEntry->status !== \App\Enums\JournalEntryStatus::VOID) {
            throw new \InvalidArgumentException('القيد الحالي نشط ومرحل بالفعل.');
        }

        return DB::transaction(function () use ($count, $team) {
            $totalGain = 0.0;
            $totalLoss = 0.0;

            foreach ($count->items()->whereNotNull('counted_quantity')->get() as $item) {
                $variance = (float) $item->variance_quantity;
                if (abs($variance) < 0.001) {
                    continue;
                }

                if ($variance > 0) {
                    $totalGain += (float) $item->variance_value;
                } else {
                    $totalLoss += abs((float) $item->variance_value);
                }
            }

            $journalLines = [];

            if ($totalGain > 0) {
                $journalLines[] = ['account_code' => '1201', 'debit_amount'  => $totalGain, 'description' => 'فائض جرد (إعادة ترحيل) — '.$count->count_number];
                $journalLines[] = ['account_code' => '4005', 'credit_amount' => $totalGain, 'description' => 'إيراد فائض جرد (إعادة ترحيل) — '.$count->count_number];
            }

            if ($totalLoss > 0) {
                $journalLines[] = ['account_code' => '5001', 'debit_amount'  => $totalLoss, 'description' => 'عجز جرد (إعادة ترحيل) — '.$count->count_number];
                $journalLines[] = ['account_code' => '1201', 'credit_amount' => $totalLoss, 'description' => 'خصم مخزون جرد (إعادة ترحيل) — '.$count->count_number];
            }

            if (empty($journalLines)) {
                throw new \InvalidArgumentException('لا توجد فروقات جرد لترحيلها.');
            }

            $journalEntry = $this->accountingService->post(
                $team, $journalLines,
                'تسوية جرد سنوي (إعادة ترحيل) — '.$count->count_number,
                InventoryCount::class, $count->id,
            );

            $count->update([
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $journalEntry;
        });
    }

    protected function generateCountNumber(Team $team): string
    {
        $last = InventoryCount::where('team_id', $team->id)->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->count_number, 4)) + 1 : 1;
        return 'INV-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
