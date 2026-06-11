<?php

namespace App\Services;

use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use App\Enums\StockMovementType;
use App\Models\MerchantCustomer;
use App\Models\MerchantProduct;
use App\Models\PosExchangeItem;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSaleReturn;
use App\Models\PosSaleReturnItem;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosReturnService
{
    public function __construct(
        protected AccountingService $accountingService,
        protected StockMovementService $stockMovementService,
        protected PosSaleReturnSettlementService $settlementService,
    ) {}

    /**
     * معالجة الإرجاع البسيط (بدون استبدال).
     *
     * @param  array<int, array{
     *   pos_sale_item_id: int,
     *   merchant_product_id: ?int,
     *   product_name: string,
     *   quantity_returned: float,
     *   unit_price: float,
     *   unit_cost: float,
     *   return_reason: ?string,
     *   item_condition: string,
     * }>  $returnItems
     */
    public function processReturn(
        Team $team,
        PosSale $sale,
        array $returnItems,
        RefundMethod $refundMethod,
        ?string $notes = null,
        ?int $merchantCustomerId = null,
    ): PosSaleReturn {
        return DB::transaction(function () use ($team, $sale, $returnItems, $refundMethod, $notes, $merchantCustomerId) {
            if ($refundMethod === RefundMethod::CREDIT_NOTE && ! $sale->merchant_customer_id && ! $merchantCustomerId) {
                throw new \InvalidArgumentException('يجب تحديد أو إنشاء عميل لتسجيل الرصيد الدائن باسمه.');
            }

            if ($merchantCustomerId && ! $sale->merchant_customer_id) {
                $sale->update(['merchant_customer_id' => $merchantCustomerId]);
                $sale->refresh();
            }

            $sale->loadMissing('merchantCustomer', 'returns');

            $saleLineIds = collect($returnItems)->pluck('pos_sale_item_id')->filter();

            if ($saleLineIds->unique()->count() !== $saleLineIds->count()) {
                throw new \InvalidArgumentException('لا يمكن تكرار نفس الصنف من الفاتورة أكثر من مرة في المرتجع الواحد.');
            }

            // التحقق من أن الكميات المرتجعة لا تتجاوز الكميات المباعة في الفاتورة الأصلية (مع مراعاة المرتجعات السابقة)
            foreach ($returnItems as $item) {
                if (! empty($item['pos_sale_item_id'])) {
                    $saleItem = \App\Models\PosSaleItem::find($item['pos_sale_item_id']);
                    if ($saleItem) {
                        $originalQty = (float) $saleItem->quantity;
                        $alreadyReturned = PosSaleReturnItem::where('pos_sale_item_id', $saleItem->id)
                            ->whereHas('saleReturn', fn ($q) => $q->where('status', 'completed'))
                            ->sum('quantity_returned');
                        $maxReturnable = $originalQty - $alreadyReturned;
                        $requestedQty = (float) $item['quantity_returned'];

                        if ($requestedQty > $maxReturnable) {
                            throw new \InvalidArgumentException("الكمية المرتجعة للمنتج ({$saleItem->product_name}) هي " . number_format($requestedQty, 2) . "، ولكن المتبقي المتاح للإرجاع من الفاتورة هو " . number_format($maxReturnable, 2) . " فقط.");
                        }
                    }
                }
            }

            $returnedAmount = collect($returnItems)->sum(
                fn ($i) => (float) $i['quantity_returned'] * (float) $i['unit_price']
            );

            $this->settlementService->validate($sale, $returnedAmount, $refundMethod);
            $allocation = $this->settlementService->allocateSettlement($sale, $returnedAmount, $refundMethod);

            $saleReturn = PosSaleReturn::create([
                'team_id'                      => $team->id,
                'pos_sale_id'                  => $sale->id,
                'return_number'                => $this->generateReturnNumber($team),
                'return_type'                  => ReturnType::RETURN,
                'refund_method'                => $refundMethod,
                'returned_amount'              => $returnedAmount,
                'exchange_amount'              => 0,
                'price_difference'             => 0,
                'refunded_to_customer'         => $allocation['cash'],
                'receivable_reduction_amount'  => $allocation['receivable'],
                'charged_to_customer'          => 0,
                'credit_note_amount'           => $refundMethod === RefundMethod::CREDIT_NOTE ? $returnedAmount : 0,
                'status'               => 'completed',
                'notes'                => $notes,
                'processed_by'         => Auth::id(),
            ]);

            foreach ($returnItems as $item) {
                $saleItem = null;
                if (! empty($item['pos_sale_item_id'])) {
                    $saleItem = PosSaleItem::with('merchantProduct')->find($item['pos_sale_item_id']);
                }

                $merchantProductId = $saleItem?->merchant_product_id ?? $item['merchant_product_id'] ?? null;
                $productName = $saleItem?->product_name ?? $item['product_name'] ?? '';
                $unitPrice = $saleItem ? (float) $saleItem->unit_price : (float) ($item['unit_price'] ?? 0);
                $unitCost = ($saleItem && $saleItem->merchantProduct) ? (float) $saleItem->merchantProduct->cost : (float) ($item['unit_cost'] ?? 0);

                PosSaleReturnItem::create([
                    'pos_sale_return_id' => $saleReturn->id,
                    'pos_sale_item_id'   => $item['pos_sale_item_id'] ?? null,
                    'merchant_product_id' => $merchantProductId,
                    'product_name'       => $productName,
                    'quantity_returned'  => $item['quantity_returned'],
                    'unit_price'         => $unitPrice,
                    'total_price'        => $item['quantity_returned'] * $unitPrice,
                    'unit_cost'          => $unitCost,
                    'return_reason'      => $item['return_reason'] ?? null,
                    'item_condition'     => $item['item_condition'] ?? 'resellable',
                ]);

                // إرجاع المخزون وتسجيل حركة التلف إن كان الصنف غير صالح لإعادة البيع
                if (! empty($merchantProductId)) {
                    $product = MerchantProduct::find($merchantProductId);
                    if ($product) {
                        if (($item['item_condition'] ?? 'resellable') === 'resellable') {
                            $this->stockMovementService->recordSaleReturn(
                                $team, $product,
                                (float) $item['quantity_returned'],
                                (float) $unitCost,
                                $saleReturn,
                            );
                        } else {
                            // 1. حركة إرجاع (تزيد المخزون مؤقتاً لتسجيل العملية)
                            $this->stockMovementService->recordSaleReturn(
                                $team, $product,
                                (float) $item['quantity_returned'],
                                (float) $unitCost,
                                $saleReturn,
                            );
                            // 2. حركة إتلاف فورية (تخصم من المخزون) لضمان دقة الرصيد والتدقيق
                            $this->stockMovementService->record(
                                team: $team,
                                product: $product,
                                type: StockMovementType::WRITE_OFF,
                                quantity: (float) $item['quantity_returned'],
                                unitCost: (float) $unitCost,
                                reference: $saleReturn,
                                notes: 'تلف عند الإرجاع'
                            );
                        }
                    }
                }
            }

            // ترحيل القيود المحاسبية
            $this->postReturnJournalEntry($team, $saleReturn, $sale, $returnItems);

            // رصيد دائن للعميل إن اختار credit_note
            if ($refundMethod === RefundMethod::CREDIT_NOTE && $sale->merchantCustomer) {
                $sale->merchantCustomer->increment('credit_balance', $returnedAmount);
            }

            if ($allocation['receivable'] > 0 && $sale->merchantCustomer) {
                $sale->merchantCustomer->decrement('balance', $allocation['receivable']);
            }

            return $saleReturn->load('returnItems', 'exchangeItems');
        });
    }

    /**
     * معالجة الاستبدال (إرجاع صنف + تسليم صنف بديل).
     *
     * @param  array<int, array{...}>  $returnItems   الأصناف المُرجَعة
     * @param  array<int, array{merchant_product_id: ?int, product_name: string, quantity: float, unit_price: float, unit_cost: float}>  $exchangeItems  الأصناف البديلة
     */
    public function processExchange(
        Team $team,
        PosSale $sale,
        array $returnItems,
        array $exchangeItems,
        RefundMethod $refundMethod,       // طريقة ردّ الفارق إن كان للعميل
        ?string $notes = null,
        ?int $merchantCustomerId = null,
    ): PosSaleReturn {
        return DB::transaction(function () use ($team, $sale, $returnItems, $exchangeItems, $refundMethod, $notes, $merchantCustomerId) {
            if ($refundMethod === RefundMethod::CREDIT_NOTE && ! $sale->merchant_customer_id && ! $merchantCustomerId) {
                throw new \InvalidArgumentException('يجب تحديد أو إنشاء عميل لتسجيل الرصيد الدائن باسمه.');
            }

            if ($merchantCustomerId && ! $sale->merchant_customer_id) {
                $sale->update(['merchant_customer_id' => $merchantCustomerId]);
                $sale->refresh();
            }
            // التحقق من توافر مخزون الأصناف البديلة
            foreach ($exchangeItems as $item) {
                if (! empty($item['merchant_product_id'])) {
                    $product = MerchantProduct::find($item['merchant_product_id']);
                    if ($product) {
                        $requestedQty = (float) $item['quantity'];
                        $availableQty = (float) $product->stock_quantity;
                        if ($requestedQty > $availableQty) {
                            throw new \InvalidArgumentException("الكمية البديلة المطلوبة للمنتج ({$product->name}) هي " . number_format($requestedQty, 2) . "، ولكن المتاح في المخزن هو " . number_format($availableQty, 2) . " فقط.");
                        }
                    }
                }
            }

            // التحقق من أن الكميات المرتجعة لا تتجاوز الكميات المباعة في الفاتورة الأصلية (مع مراعاة المرتجعات السابقة)
            foreach ($returnItems as $item) {
                if (! empty($item['pos_sale_item_id'])) {
                    $saleItem = PosSaleItem::find($item['pos_sale_item_id']);
                    if ($saleItem) {
                        $originalQty = (float) $saleItem->quantity;
                        $alreadyReturned = PosSaleReturnItem::where('pos_sale_item_id', $saleItem->id)
                            ->whereHas('saleReturn', fn ($q) => $q->where('status', 'completed'))
                            ->sum('quantity_returned');
                        $maxReturnable = $originalQty - $alreadyReturned;
                        $requestedQty = (float) $item['quantity_returned'];

                        if ($requestedQty > $maxReturnable) {
                            throw new \InvalidArgumentException("الكمية المرتجعة للمنتج ({$saleItem->product_name}) هي " . number_format($requestedQty, 2) . "، ولكن المتبقي المتاح للإرجاع من الفاتورة هو " . number_format($maxReturnable, 2) . " فقط.");
                        }
                    }
                }
            }

            $returnedAmount = collect($returnItems)->sum(
                fn ($i) => (float) $i['quantity_returned'] * (float) $i['unit_price']
            );
            $exchangeAmount = collect($exchangeItems)->sum(
                fn ($i) => (float) $i['quantity'] * (float) $i['unit_price']
            );

            // الفارق: موجب = العميل يدفع إضافي، سالب = نُعطيه فرقاً
            $priceDifference      = $exchangeAmount - $returnedAmount;
            $refundedToCustomer   = 0.0;
            $chargedToCustomer    = 0.0;
            $creditNoteAmount     = 0.0;

            if ($priceDifference < 0) {
                // البضاعة البديلة أرخص → نُعطي العميل الفرق
                $surplus = abs($priceDifference);
                if ($refundMethod === RefundMethod::CASH) {
                    $refundedToCustomer = $surplus;
                } elseif ($refundMethod === RefundMethod::CREDIT_NOTE) {
                    $creditNoteAmount = $surplus;
                }
            } elseif ($priceDifference > 0) {
                // البضاعة البديلة أغلى → العميل يدفع الفرق
                $chargedToCustomer = $priceDifference;
            }

            $saleReturn = PosSaleReturn::create([
                'team_id'              => $team->id,
                'pos_sale_id'          => $sale->id,
                'return_number'        => $this->generateReturnNumber($team),
                'return_type'          => ReturnType::EXCHANGE,
                'refund_method'        => $refundMethod,
                'returned_amount'      => $returnedAmount,
                'exchange_amount'      => $exchangeAmount,
                'price_difference'     => $priceDifference,
                'refunded_to_customer' => $refundedToCustomer,
                'charged_to_customer'  => $chargedToCustomer,
                'credit_note_amount'   => $creditNoteAmount,
                'status'               => 'completed',
                'notes'                => $notes,
                'processed_by'         => Auth::id(),
            ]);

            // تسجيل الأصناف المُرجَعة
            foreach ($returnItems as $item) {
                $saleItem = null;
                if (! empty($item['pos_sale_item_id'])) {
                    $saleItem = PosSaleItem::with('merchantProduct')->find($item['pos_sale_item_id']);
                }

                $merchantProductId = $saleItem?->merchant_product_id ?? $item['merchant_product_id'] ?? null;
                $productName = $saleItem?->product_name ?? $item['product_name'] ?? '';
                $unitPrice = $saleItem ? (float) $saleItem->unit_price : (float) ($item['unit_price'] ?? 0);
                $unitCost = ($saleItem && $saleItem->merchantProduct) ? (float) $saleItem->merchantProduct->cost : (float) ($item['unit_cost'] ?? 0);

                PosSaleReturnItem::create([
                    'pos_sale_return_id'  => $saleReturn->id,
                    'pos_sale_item_id'    => $item['pos_sale_item_id'] ?? null,
                    'merchant_product_id' => $merchantProductId,
                    'product_name'        => $productName,
                    'quantity_returned'   => $item['quantity_returned'],
                    'unit_price'          => $unitPrice,
                    'total_price'         => $item['quantity_returned'] * $unitPrice,
                    'unit_cost'           => $unitCost,
                    'return_reason'       => $item['return_reason'] ?? null,
                    'item_condition'      => $item['item_condition'] ?? 'resellable',
                ]);

                // إرجاع المخزون (تسجيل حركة الاستلام ثم الإتلاف في حال التلف)
                if (! empty($merchantProductId)) {
                    $product = MerchantProduct::find($merchantProductId);
                    if ($product) {
                        if (($item['item_condition'] ?? 'resellable') === 'resellable') {
                            $this->stockMovementService->record(
                                $team, $product,
                                StockMovementType::EXCHANGE_IN,
                                (float) $item['quantity_returned'],
                                (float) $unitCost,
                                $saleReturn,
                            );
                        } else {
                            // 1. حركة استلام (تزيد المخزون)
                            $this->stockMovementService->record(
                                $team, $product,
                                StockMovementType::EXCHANGE_IN,
                                (float) $item['quantity_returned'],
                                (float) $unitCost,
                                $saleReturn,
                            );
                            // 2. حركة إتلاف فورية (تخصم المخزون)
                            $this->stockMovementService->record(
                                team: $team,
                                product: $product,
                                type: StockMovementType::WRITE_OFF,
                                quantity: (float) $item['quantity_returned'],
                                unitCost: (float) $unitCost,
                                reference: $saleReturn,
                                notes: 'تلف عند الاستبدال'
                            );
                        }
                    }
                }
            }

            // تسجيل الأصناف البديلة
            foreach ($exchangeItems as $item) {
                PosExchangeItem::create([
                    'pos_sale_return_id'  => $saleReturn->id,
                    'merchant_product_id' => $item['merchant_product_id'] ?? null,
                    'product_name'        => $item['product_name'],
                    'quantity'            => $item['quantity'],
                    'unit_price'          => $item['unit_price'],
                    'total_price'         => $item['quantity'] * $item['unit_price'],
                    'unit_cost'           => $item['unit_cost'] ?? 0,
                ]);

                // صرف المخزون للصنف البديل
                if (! empty($item['merchant_product_id'])) {
                    $product = MerchantProduct::find($item['merchant_product_id']);
                    if ($product) {
                        $this->stockMovementService->record(
                            $team, $product,
                            StockMovementType::EXCHANGE_OUT,
                            (float) $item['quantity'],
                            (float) ($item['unit_cost'] ?? $product->cost),
                            $saleReturn,
                        );
                    }
                }
            }

            // القيود المحاسبية للاستبدال
            $this->postExchangeJournalEntry($team, $saleReturn, $sale);

            // رصيد دائن للعميل إن كان الفارق لصالحه
            if ($creditNoteAmount > 0 && $sale->merchantCustomer) {
                $sale->merchantCustomer->increment('credit_balance', $creditNoteAmount);
            }

            return $saleReturn->load('returnItems', 'exchangeItems');
        });
    }

    // ─── القيود المحاسبية — إرجاع ─────────────────────────────────────────────

    protected function postReturnJournalEntry(Team $team, PosSaleReturn $saleReturn, PosSale $sale, array $returnItems): void
    {
        $returnedAmount = (float) $saleReturn->returned_amount;
        $totalReturnCost = collect($returnItems)->sum(
            fn ($i) => (float) ($i['unit_cost'] ?? 0) * (float) $i['quantity_returned']
        );

        $lines = [];

        // عكس الإيراد: مدين 4003 / دائن [طريقة الدفع أو 2101]
        $lines[] = [
            'account_code' => '4003',
            'debit_amount' => $returnedAmount,
            'description'  => 'عكس إيراد — مرتجع '.$saleReturn->return_number,
        ];

        $cashRefund = (float) $saleReturn->refunded_to_customer;
        $receivableReduction = (float) $saleReturn->receivable_reduction_amount;

        if ($cashRefund > 0) {
            $payMethod = $sale->payment_method ?? 'cash';
            $lines[] = [
                'account_code' => $this->debitAccountCodeForMethod($payMethod),
                'credit_amount' => $cashRefund,
                'description'   => 'استرداد للعميل — مرتجع '.$saleReturn->return_number,
            ];
        }

        if ($saleReturn->refund_method === RefundMethod::CREDIT_NOTE) {
            $lines[] = [
                'account_code' => '2101',
                'credit_amount' => $returnedAmount,
                'description'   => 'رصيد دائن للعميل — مرتجع '.$saleReturn->return_number,
                'subledger_type' => $sale->merchant_customer_id ? \App\Models\MerchantCustomer::class : null,
                'subledger_id'   => $sale->merchant_customer_id,
            ];
        } elseif ($receivableReduction > 0) {
            $lines[] = [
                'account_code' => '1101',
                'credit_amount' => $receivableReduction,
                'description'   => 'تخفيض ذمم — مرتجع '.$saleReturn->return_number,
                'subledger_type' => $sale->merchant_customer_id ? \App\Models\MerchantCustomer::class : null,
                'subledger_id'   => $sale->merchant_customer_id,
            ];
        }

        $this->accountingService->post(
            $team, $lines,
            'مرتجع مبيعات '.$saleReturn->return_number,
            PosSaleReturn::class, $saleReturn->id,
        );

        // عكس COGS: مدين 1201 / دائن 5001 (للأصناف القابلة لإعادة البيع)
        $resellableCost = collect($returnItems)
            ->filter(fn ($i) => ($i['item_condition'] ?? 'resellable') === 'resellable')
            ->sum(fn ($i) => (float) ($i['unit_cost'] ?? 0) * (float) $i['quantity_returned']);

        if ($resellableCost > 0) {
            $this->accountingService->post(
                $team,
                [
                    ['account_code' => '1201', 'debit_amount'  => $resellableCost, 'description' => 'إرجاع مخزون سليم — مرتجع '.$saleReturn->return_number],
                    ['account_code' => '5001', 'credit_amount' => $resellableCost, 'description' => 'عكس تكلفة سليم — مرتجع '.$saleReturn->return_number],
                ],
                'إرجاع مخزون — '.$saleReturn->return_number,
                PosSaleReturn::class, $saleReturn->id,
            );
        }

        // إقفال تكلفة التالف كخسارة: مدين 1202 (فوارق وعجز المخزون) / دائن 5001 (تكلفة المبيعات)
        $damagedCost = collect($returnItems)
            ->filter(fn ($i) => ($i['item_condition'] ?? 'resellable') !== 'resellable')
            ->sum(fn ($i) => (float) ($i['unit_cost'] ?? 0) * (float) $i['quantity_returned']);

        if ($damagedCost > 0) {
            $this->accountingService->post(
                $team,
                [
                    ['account_code' => '1202', 'debit_amount'  => $damagedCost, 'description' => 'خسائر تلف بضاعة مرتجعة معيبة — مرتجع '.$saleReturn->return_number],
                    ['account_code' => '5001', 'credit_amount' => $damagedCost, 'description' => 'عكس تكلفة مبيعات التالف — مرتجع '.$saleReturn->return_number],
                ],
                'تلف بضاعة مرتجعة — '.$saleReturn->return_number,
                PosSaleReturn::class, $saleReturn->id,
            );
        }
    }

    // ─── القيود المحاسبية — استبدال ───────────────────────────────────────────

    protected function postExchangeJournalEntry(Team $team, PosSaleReturn $saleReturn, PosSale $sale): void
    {
        $returnedAmount  = (float) $saleReturn->returned_amount;
        $exchangeAmount  = (float) $saleReturn->exchange_amount;
        $priceDifference = (float) $saleReturn->price_difference;

        // --- قيد الإرجاع (عكس الإيراد الأصلي) ---
        $returnLines = [
            ['account_code' => '4003', 'debit_amount' => $returnedAmount, 'description' => 'عكس إيراد مُبدَّل — '.$saleReturn->return_number],
        ];

        if ($priceDifference > 0) {
            // عميل يدفع الفرق (البديل أغلى): مدين صندوق/ذمم
            $returnLines[] = [
                'account_code' => $this->debitAccountCodeForMethod($sale->payment_method),
                'debit_amount' => $priceDifference,
                'description'  => 'فرق سعر — استبدال '.$saleReturn->return_number,
            ];
        }

        // الإيراد الجديد (قيمة الصنف البديل)
        $returnLines[] = [
            'account_code'  => '4003',
            'credit_amount' => $exchangeAmount,
            'description'   => 'إيراد بضاعة الاستبدال — '.$saleReturn->return_number,
        ];

        // دائن الصندوق إذا كان الفرق لصالح العميل
        if ($priceDifference < 0) {
            $surplus = abs($priceDifference);
            if ($saleReturn->refund_method === RefundMethod::CASH) {
                $returnLines[] = [
                    'account_code'  => $this->debitAccountCodeForMethod($sale->payment_method),
                    'credit_amount' => $surplus,
                    'description'   => 'استرداد فرق نقدي — استبدال '.$saleReturn->return_number,
                ];
            } elseif ($saleReturn->refund_method === RefundMethod::CREDIT_NOTE) {
                $returnLines[] = [
                    'account_code'  => '2101',
                    'credit_amount' => $surplus,
                    'description'   => 'رصيد فائض للعميل — استبدال '.$saleReturn->return_number,
                    'subledger_type' => $sale->merchant_customer_id ? MerchantCustomer::class : null,
                    'subledger_id'   => $sale->merchant_customer_id,
                ];
            }
        }

        // نضبط القيد ليكون متوازناً: نضيف سطر توازن للصندوق
        $this->accountingService->post(
            $team, $returnLines,
            'استبدال '.$saleReturn->return_number,
            PosSaleReturn::class, $saleReturn->id,
        );

        // --- قيد COGS للاستبدال ---
        // عكس COGS المرجّع السليم والتالف
        $resellableReturnCost = $saleReturn->returnItems
            ->filter(fn ($i) => $i->item_condition === 'resellable')
            ->sum(fn ($i) => (float) $i->unit_cost * (float) $i->quantity_returned);

        $damagedReturnCost = $saleReturn->returnItems
            ->filter(fn ($i) => $i->item_condition !== 'resellable')
            ->sum(fn ($i) => (float) $i->unit_cost * (float) $i->quantity_returned);

        // COGS الجديد للبديل
        $exchangeCost = $saleReturn->exchangeItems->sum(fn ($i) => (float) $i->unit_cost * (float) $i->quantity);

        $cogsLines = [];
        if ($resellableReturnCost > 0) {
            $cogsLines[] = ['account_code' => '1201', 'debit_amount'  => $resellableReturnCost, 'description' => 'إرجاع مخزون مُبدَّل سليم — '.$saleReturn->return_number];
            $cogsLines[] = ['account_code' => '5001', 'credit_amount' => $resellableReturnCost, 'description' => 'عكس تكلفة مُبدَّلة سليمة — '.$saleReturn->return_number];
        }
        if ($damagedReturnCost > 0) {
            $cogsLines[] = ['account_code' => '1202', 'debit_amount'  => $damagedReturnCost, 'description' => 'خسائر تلف بضاعة مُبدَّلة — '.$saleReturn->return_number];
            $cogsLines[] = ['account_code' => '5001', 'credit_amount' => $damagedReturnCost, 'description' => 'عكس تكلفة مُبدَّلة تالفة — '.$saleReturn->return_number];
        }
        if ($exchangeCost > 0) {
            $cogsLines[] = ['account_code' => '5001', 'debit_amount'  => $exchangeCost, 'description' => 'تكلفة بضاعة الاستبدال — '.$saleReturn->return_number];
            $cogsLines[] = ['account_code' => '1201', 'credit_amount' => $exchangeCost, 'description' => 'صرف مخزون الاستبدال — '.$saleReturn->return_number];
        }

        if (! empty($cogsLines)) {
            $this->accountingService->post(
                $team, $cogsLines,
                'تكلفة استبدال — '.$saleReturn->return_number,
                PosSaleReturn::class, $saleReturn->id,
            );
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function debitAccountCodeForMethod(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'bank_transfer' => '1002',
            'card'          => '1003',
            default         => '1001',
        };
    }

    protected function generateReturnNumber(Team $team): string
    {
        $last = PosSaleReturn::where('team_id', $team->id)->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->return_number, 4)) + 1 : 1;
        return 'RTN-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
