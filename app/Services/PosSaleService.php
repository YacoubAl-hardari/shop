<?php

namespace App\Services;

use App\Enums\SalePaymentType;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosSaleService
{
    public function __construct(
        protected AccountingService $accountingService,
    ) {}

    /**
     * @param  array<int, array{merchant_product_id?: int|null, product_name: string, quantity: float, unit_price: float}>  $items
     */
    public function createSale(
        Team $team,
        array $items,
        SalePaymentType $paymentType,
        float $paidAmount = 0,
        ?MerchantCustomer $customer = null,
        ?string $paymentMethod = null,
        ?string $notes = null,
        ?int $merchantPaymentAccountId = null,
        ?string $paymentReference = null,
        float $customerCreditApplied = 0,
    ): PosSale {
        return DB::transaction(function () use ($team, $items, $paymentType, $paidAmount, $customer, $paymentMethod, $notes, $merchantPaymentAccountId, $paymentReference, $customerCreditApplied) {
            if (in_array($paymentType, [SalePaymentType::CREDIT, SalePaymentType::PARTIAL], true) && ! $customer) {
                throw new \InvalidArgumentException('يجب اختيار عميل مسجّل للبيع الآجل أو الجزئي');
            }

            $totalAmount = collect($items)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

            if ($customerCreditApplied > 0) {
                if (! $customer) {
                    throw new \InvalidArgumentException('يجب اختيار عميل لاستخدام الرصيد الفائض');
                }

                if ($customerCreditApplied > (float) $customer->credit_balance) {
                    throw new \InvalidArgumentException('الرصيد الفائض للعميل غير كافٍ');
                }

                if ($customerCreditApplied > $totalAmount) {
                    throw new \InvalidArgumentException('لا يمكن خصم رصيد فائض أكبر من إجمالي الفاتورة');
                }

                $customer->decrement('credit_balance', $customerCreditApplied);
            }

            $remainingTotal = $totalAmount - $customerCreditApplied;

            $creditAmount = match ($paymentType) {
                SalePaymentType::CASH => 0,
                SalePaymentType::CREDIT => $remainingTotal,
                SalePaymentType::PARTIAL => max(0, $remainingTotal - $paidAmount),
            };

            $actualPaid = match ($paymentType) {
                SalePaymentType::CASH => $remainingTotal,
                SalePaymentType::CREDIT => 0,
                SalePaymentType::PARTIAL => $paidAmount,
            };

            $sale = PosSale::create([
                'team_id' => $team->id,
                'sale_number' => $this->generateSaleNumber($team),
                'merchant_customer_id' => $customer?->id,
                'total_amount' => $totalAmount,
                'paid_amount' => $actualPaid,
                'credit_amount' => $creditAmount,
                'customer_credit_applied' => $customerCreditApplied,
                'payment_type' => $paymentType,
                'payment_method' => $paymentMethod,
                'merchant_payment_account_id' => $merchantPaymentAccountId,
                'payment_reference' => $paymentReference,
                'status' => 'completed',
                'notes' => $notes,
                'sold_by' => Auth::id(),
            ]);

            foreach ($items as $item) {
                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'merchant_product_id' => $item['merchant_product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                if (! empty($item['merchant_product_id'])) {
                    MerchantProduct::where('id', $item['merchant_product_id'])
                        ->decrement('stock_quantity', $item['quantity']);
                }
            }

            $this->postSaleJournalEntry($team, $sale, $customer);

            return $sale->load('items');
        });
    }

    public function recordCustomerPayment(
        Team $team,
        MerchantCustomer $customer,
        float $amount,
        string $paymentMethod = 'cash',
        ?int $merchantPaymentAccountId = null,
        ?string $referenceNumber = null,
        ?string $notes = null,
    ): MerchantCustomerPayment {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('مبلغ السداد يجب أن يكون أكبر من صفر');
        }

        return DB::transaction(function () use ($team, $customer, $amount, $paymentMethod, $merchantPaymentAccountId, $referenceNumber, $notes) {
            $debt = (float) $customer->balance;
            $appliedToBalance = min($amount, $debt);
            $surplusToCredit = $amount - $appliedToBalance;

            $payment = MerchantCustomerPayment::create([
                'team_id' => $team->id,
                'merchant_customer_id' => $customer->id,
                'merchant_payment_account_id' => $merchantPaymentAccountId,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'applied_to_balance' => $appliedToBalance,
                'surplus_to_credit' => $surplusToCredit,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
                'received_by' => Auth::id(),
            ]);

            $description = 'تحصيل من العميل';
            if ($referenceNumber) {
                $description .= ' — مرجع: '.$referenceNumber;
            }

            $lines = [
                [
                    'account_code' => $this->debitAccountCodeForMethod($paymentMethod),
                    'debit_amount' => $amount,
                    'description' => $description,
                ],
            ];

            if ($appliedToBalance > 0) {
                $lines[] = [
                    'account_code' => '1101',
                    'credit_amount' => $appliedToBalance,
                    'description' => 'سداد ذمم العميل',
                    'subledger_type' => MerchantCustomer::class,
                    'subledger_id' => $customer->id,
                ];
            }

            if ($surplusToCredit > 0) {
                $lines[] = [
                    'account_code' => '2101',
                    'credit_amount' => $surplusToCredit,
                    'description' => 'رصيد فائض للعميل (دفعة مقدمة)',
                    'subledger_type' => MerchantCustomer::class,
                    'subledger_id' => $customer->id,
                ];
            }

            $this->accountingService->post(
                $team,
                $lines,
                'سداد عميل: '.$customer->name,
                MerchantCustomerPayment::class,
                $payment->id,
            );

            if ($appliedToBalance > 0) {
                $customer->decrement('balance', $appliedToBalance);
            }

            if ($surplusToCredit > 0) {
                $customer->increment('credit_balance', $surplusToCredit);
            }

            return $payment;
        });
    }

    protected function postSaleJournalEntry(Team $team, PosSale $sale, ?MerchantCustomer $customer): void
    {
        $lines = [];
        $customerCreditApplied = (float) $sale->customer_credit_applied;

        if ($customerCreditApplied > 0 && $customer) {
            $lines[] = [
                'account_code' => '2101',
                'debit_amount' => $customerCreditApplied,
                'description' => 'استخدام رصيد عميل مسبق',
                'subledger_type' => MerchantCustomer::class,
                'subledger_id' => $customer->id,
            ];
        }

        $revenueLine = [
            'account_code' => '4003',
            'credit_amount' => (float) $sale->total_amount,
            'description' => 'إيرادات مبيعات',
        ];

        if ($sale->payment_type === SalePaymentType::CASH) {
            if ($sale->paid_amount > 0) {
                $lines[] = [
                    'account_code' => $this->debitAccountCodeForMethod($sale->payment_method),
                    'debit_amount' => (float) $sale->paid_amount,
                    'description' => $this->paymentDescription($sale),
                ];
            }
            $lines[] = $revenueLine;
        } elseif ($sale->payment_type === SalePaymentType::CREDIT) {
            if ($sale->credit_amount > 0) {
                $lines[] = [
                    'account_code' => '1101',
                    'debit_amount' => (float) $sale->credit_amount,
                    'description' => 'ذمم مدينة',
                    'subledger_type' => $customer ? MerchantCustomer::class : null,
                    'subledger_id' => $customer?->id,
                ];

                if ($customer) {
                    $customer->increment('balance', (float) $sale->credit_amount);
                }
            }
            $lines[] = $revenueLine;
        } else {
            if ($sale->paid_amount > 0) {
                $lines[] = [
                    'account_code' => $this->debitAccountCodeForMethod($sale->payment_method),
                    'debit_amount' => (float) $sale->paid_amount,
                    'description' => $this->paymentDescription($sale, partial: true),
                ];
            }
            if ($sale->credit_amount > 0) {
                $lines[] = [
                    'account_code' => '1101',
                    'debit_amount' => (float) $sale->credit_amount,
                    'description' => 'ذمم مدينة جزئية',
                    'subledger_type' => $customer ? MerchantCustomer::class : null,
                    'subledger_id' => $customer?->id,
                ];

                if ($customer) {
                    $customer->increment('balance', (float) $sale->credit_amount);
                }
            }
            $lines[] = $revenueLine;
        }

        $this->accountingService->post(
            $team,
            $lines,
            'بيع رقم '.$sale->sale_number,
            PosSale::class,
            $sale->id,
        );
    }

    protected function debitAccountCodeForMethod(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'bank_transfer' => '1002',
            'card' => '1003',
            default => '1001',
        };
    }

    protected function paymentDescription(PosSale $sale, bool $partial = false): string
    {
        $label = match ($sale->payment_method) {
            'bank_transfer' => $partial ? 'تحويل بنكي جزئي' : 'تحويل بنكي',
            'card' => $partial ? 'بطاقة جزئية' : 'بطاقة',
            default => $partial ? 'نقدية جزئية' : 'نقدية',
        };

        if ($sale->payment_reference) {
            $label .= ' — مرجع: '.$sale->payment_reference;
        }

        return $label;
    }

    protected function generateSaleNumber(Team $team): string
    {
        $lastSale = PosSale::where('team_id', $team->id)->orderByDesc('id')->first();
        $nextNumber = $lastSale ? ((int) $lastSale->sale_number) + 1 : 1;

        return str_pad((string) $nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
