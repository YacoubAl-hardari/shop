<?php

namespace App\Services;

use App\Enums\RefundMethod;
use App\Enums\SalePaymentType;
use App\Models\PosSale;
use App\Models\PosSaleReturn;
use Illuminate\Support\Collection;

class PosSaleReturnSettlementService
{
    /**
     * @return array<string, string>
     */
    public function allowedRefundMethods(PosSale $sale): array
    {
        return match ($sale->payment_type) {
            SalePaymentType::CREDIT => [
                RefundMethod::REDUCE_RECEIVABLE->value => 'خصم من كشف حساب العميل',
            ],
            SalePaymentType::PARTIAL => $this->partialSaleRefundMethods($sale),
            default => [
                RefundMethod::CASH->value        => 'نقد للعميل',
                RefundMethod::CREDIT_NOTE->value => 'رصيد دائن للعميل',
            ],
        };
    }

    public function defaultRefundMethod(PosSale $sale): RefundMethod
    {
        return match ($sale->payment_type) {
            SalePaymentType::CREDIT  => RefundMethod::REDUCE_RECEIVABLE,
            SalePaymentType::PARTIAL => RefundMethod::SPLIT_SETTLEMENT,
            default                  => RefundMethod::CASH,
        };
    }

    public function isRefundMethodAllowed(PosSale $sale, RefundMethod $method): bool
    {
        return array_key_exists($method->value, $this->allowedRefundMethods($sale));
    }

    /**
     * @return array{receivable: float, cash: float}
     */
    public function allocateSettlement(PosSale $sale, float $returnedAmount, RefundMethod $method): array
    {
        return match ($method) {
            RefundMethod::REDUCE_RECEIVABLE => [
                'receivable' => $returnedAmount,
                'cash'       => 0.0,
            ],
            RefundMethod::CASH => [
                'receivable' => 0.0,
                'cash'       => $returnedAmount,
            ],
            RefundMethod::SPLIT_SETTLEMENT => $this->allocatePartialReturn($sale, $returnedAmount),
            RefundMethod::CREDIT_NOTE => [
                'receivable' => 0.0,
                'cash'       => 0.0,
            ],
            default => [
                'receivable' => 0.0,
                'cash'       => 0.0,
            ],
        };
    }

    public function validate(PosSale $sale, float $returnedAmount, RefundMethod $method): void
    {
        if ($returnedAmount <= 0) {
            throw new \InvalidArgumentException('يجب أن تكون قيمة المُرجَع أكبر من صفر.');
        }

        if (! $this->isRefundMethodAllowed($sale, $method)) {
            throw new \InvalidArgumentException($this->disallowedMethodMessage($sale, $method));
        }

        $remainingMerchandise = $this->remainingMerchandiseValue($sale);

        if ($remainingMerchandise <= 0) {
            throw new \InvalidArgumentException($this->fullyReturnedMessage($sale));
        }

        if ($returnedAmount > $remainingMerchandise + 0.009) {
            throw new \InvalidArgumentException(
                'قيمة المُرجَع ('.number_format($returnedAmount, 2).' ر.س) تتجاوز المتبقي القابل للإرجاع من الفاتورة ('
                .number_format($remainingMerchandise, 2).' ر.س).'
            );
        }

        $maxSettlement = $this->maxReturnableSettlementValue($sale);

        if ($returnedAmount > $maxSettlement + 0.009) {
            throw new \InvalidArgumentException($this->settlementBlockedMessage($sale, $returnedAmount, $maxSettlement));
        }

        $allocation = $this->allocateSettlement($sale, $returnedAmount, $method);

        if ($method === RefundMethod::CREDIT_NOTE) {
            $this->validateCreditNote($sale, $returnedAmount);

            return;
        }

        if ($allocation['cash'] > $this->remainingCashSettlable($sale) + 0.009) {
            throw new \InvalidArgumentException(
                'لا يمكن استرداد '.number_format($allocation['cash'], 2).' ر.س نقداً — المتبقي المسدَّد نقداً/بطاقة في هذه الفاتورة هو '
                .number_format($this->remainingCashSettlable($sale), 2).' ر.س فقط.'
            );
        }

        if ($allocation['receivable'] > 0) {
            if (! $sale->merchant_customer_id) {
                throw new \InvalidArgumentException('يجب أن تكون الفاتورة مرتبطة بعميل لخصم المبلغ من كشف الحساب.');
            }

            $remainingReceivable = $this->remainingReceivableSettlable($sale);

            if ($allocation['receivable'] > $remainingReceivable + 0.009) {
                throw new \InvalidArgumentException(
                    'لا يمكن خصم '.number_format($allocation['receivable'], 2).' ر.س من الذمة — المتبقي الآجل في هذه الفاتورة هو '
                    .number_format($remainingReceivable, 2).' ر.س فقط.'
                );
            }

            $customerBalance = (float) $sale->merchantCustomer?->balance;

            if ($allocation['receivable'] > $customerBalance + 0.009) {
                throw new \InvalidArgumentException(
                    'لا يمكن خصم '.number_format($allocation['receivable'], 2).' ر.س من كشف الحساب — مديونية العميل الحالية '
                    .number_format($customerBalance, 2).' ر.س فقط. '
                    .'إذا سدّد العميل مسبقاً، استخدم «نقد للعميل» أو «تسوية تلقائية» حسب نوع الفاتورة.'
                );
            }
        }
    }

    public function settlementPreview(PosSale $sale, float $returnedAmount, RefundMethod $method): string
    {
        if ($returnedAmount <= 0) {
            return '—';
        }

        try {
            $this->validate($sale, $returnedAmount, $method);
        } catch (\InvalidArgumentException $e) {
            return '⚠ '.$e->getMessage();
        }

        $allocation = $this->allocateSettlement($sale, $returnedAmount, $method);
        $parts = [];

        if ($method === RefundMethod::CREDIT_NOTE) {
            if ($sale->payment_type === SalePaymentType::PARTIAL) {
                return 'يُضاف '.number_format($returnedAmount, 2).' ر.س لرصيد العميل الفائض (من الجزء النقدي فقط).';
            }

            return 'يُضاف '.number_format($returnedAmount, 2).' ر.س لرصيد العميل الفائض بدلاً من الاسترداد النقدي.';
        }

        if ($allocation['receivable'] > 0) {
            $parts[] = 'خصم من الذمة: '.number_format($allocation['receivable'], 2).' ر.س';
        }

        if ($allocation['cash'] > 0) {
            $parts[] = 'استرداد نقدي/بطاقة: '.number_format($allocation['cash'], 2).' ر.س';
        }

        return $parts === [] ? '—' : implode(' | ', $parts);
    }

    public function maxReturnableSettlementValue(PosSale $sale): float
    {
        $merchandise = $this->remainingMerchandiseValue($sale);

        return match ($sale->payment_type) {
            SalePaymentType::CREDIT => min($merchandise, $this->remainingReceivableSettlable($sale)),
            SalePaymentType::CASH => min($merchandise, $this->remainingCashSettlable($sale)),
            default => min(
                $merchandise,
                $this->remainingCashSettlable($sale) + $this->remainingReceivableSettlable($sale),
            ),
        };
    }

    public function remainingMerchandiseValue(PosSale $sale): float
    {
        return max(0, (float) $sale->total_amount - $this->priorReturnedMerchandiseValue($sale));
    }

    public function priorReturnedMerchandiseValue(PosSale $sale): float
    {
        return (float) $this->completedReturns($sale)->sum('returned_amount');
    }

    public function remainingCashSettlable(PosSale $sale): float
    {
        if ($sale->payment_type === SalePaymentType::CASH) {
            return max(0, (float) $sale->paid_amount - $this->priorReturnedMerchandiseValue($sale));
        }

        return max(0, (float) $sale->paid_amount - $this->priorCashRefunded($sale));
    }

    public function remainingReceivableSettlable(PosSale $sale): float
    {
        if ($sale->payment_type === SalePaymentType::CREDIT) {
            return max(0, (float) $sale->credit_amount - $this->priorReturnedMerchandiseValue($sale));
        }

        return max(0, (float) $sale->credit_amount - $this->priorReceivableReduced($sale));
    }

    public function priorCashRefunded(PosSale $sale): float
    {
        return $this->completedReturns($sale)->sum(function (PosSaleReturn $return): float {
            return match ($return->refund_method) {
                RefundMethod::CASH              => (float) $return->returned_amount,
                RefundMethod::SPLIT_SETTLEMENT  => (float) $return->refunded_to_customer,
                RefundMethod::CREDIT_NOTE       => (float) $return->credit_note_amount,
                default                         => 0.0,
            };
        });
    }

    public function priorReceivableReduced(PosSale $sale): float
    {
        return $this->completedReturns($sale)->sum(function (PosSaleReturn $return): float {
            return match ($return->refund_method) {
                RefundMethod::REDUCE_RECEIVABLE => (float) $return->returned_amount,
                RefundMethod::SPLIT_SETTLEMENT  => (float) $return->receivable_reduction_amount,
                default                         => 0.0,
            };
        });
    }

    /**
     * @return array{receivable: float, cash: float}
     */
    protected function allocatePartialReturn(PosSale $sale, float $returnedAmount): array
    {
        $remainingCash = $this->remainingCashSettlable($sale);
        $remainingCredit = $this->remainingReceivableSettlable($sale);
        $totalRemaining = $remainingCash + $remainingCredit;

        if ($returnedAmount > $totalRemaining + 0.009) {
            throw new \InvalidArgumentException(
                'قيمة المُرجَع تتجاوز المتبقي القابل للتسوية من الفاتورة.'
            );
        }

        $netTotal = (float) $sale->total_amount - (float) $sale->customer_credit_applied;

        if ($netTotal <= 0) {
            return ['receivable' => 0.0, 'cash' => $returnedAmount];
        }

        $creditRatio = (float) $sale->credit_amount / $netTotal;
        $creditPart = round($returnedAmount * $creditRatio, 2);
        $cashPart = round($returnedAmount - $creditPart, 2);

        $creditPart = min($creditPart, $remainingCredit);
        $cashPart = min($cashPart, $remainingCash);

        $allocated = $creditPart + $cashPart;
        $unallocated = round($returnedAmount - $allocated, 2);

        if ($unallocated > 0) {
            $extraCredit = min($unallocated, $remainingCredit - $creditPart);
            $creditPart += $extraCredit;
            $unallocated -= $extraCredit;
        }

        if ($unallocated > 0) {
            $cashPart += min($unallocated, $remainingCash - $cashPart);
        }

        $customerBalance = (float) $sale->merchantCustomer?->balance;

        if ($creditPart > $customerBalance + 0.009) {
            $excess = round($creditPart - $customerBalance, 2);
            $creditPart = max(0, $customerBalance);
            $cashPart = min($remainingCash, round($cashPart + $excess, 2));
        }

        if (abs(($creditPart + $cashPart) - $returnedAmount) > 0.02) {
            throw new \InvalidArgumentException(
                'تعذّر توزيع قيمة المُرجَع على الذمة والنقد بشكل متوافق مع الفاتورة ومديونية العميل.'
            );
        }

        return [
            'receivable' => $creditPart,
            'cash'       => $cashPart,
        ];
    }

    protected function validateCreditNote(PosSale $sale, float $returnedAmount): void
    {
        if (! $sale->merchant_customer_id) {
            throw new \InvalidArgumentException('يجب تحديد أو ربط عميل لتسجيل الرصيد الدائن.');
        }

        if ($sale->payment_type === SalePaymentType::CREDIT) {
            throw new \InvalidArgumentException(
                'الفاتورة آجلة بالكامل — يجب خصم المبلغ من ذمة العميل في كشف الحساب، ولا يمكن تحويله لرصيد دائن.'
            );
        }

        $maxCreditNote = $this->remainingCashSettlable($sale);

        if ($sale->payment_type === SalePaymentType::PARTIAL && $maxCreditNote <= 0) {
            throw new \InvalidArgumentException(
                'لا يوجد جزء نقدي متبقي في هذه الفاتورة — استخدم «تسوية تلقائية» لخصم الجزء الآجل من الذمة.'
            );
        }

        if ($returnedAmount > $maxCreditNote + 0.009) {
            throw new \InvalidArgumentException(
                'رصيد دائن لا يمكن أن يتجاوز الجزء المسدَّد نقداً/بطاقة في الفاتورة ('
                .number_format($maxCreditNote, 2).' ر.س). استخدم «تسوية تلقائية» إذا كان المرتجع يشمل جزءاً آجلاً.'
            );
        }
    }

    /**
     * @return array<string, string>
     */
    protected function partialSaleRefundMethods(PosSale $sale): array
    {
        $methods = [
            RefundMethod::SPLIT_SETTLEMENT->value => 'تسوية تلقائية (ذمة + نقد)',
        ];

        if ($this->remainingCashSettlable($sale) > 0) {
            $methods[RefundMethod::CREDIT_NOTE->value] = 'رصيد دائن (الجزء النقدي فقط)';
        }

        return $methods;
    }

    public function fullyReturnedMessage(PosSale $sale): string
    {
        $returned = $this->priorReturnedMerchandiseValue($sale);
        $numbers = $this->completedReturns($sale)
            ->pluck('return_number')
            ->filter()
            ->implode('، ');

        $message = 'تم إرجاع قيمة هذه الفاتورة بالكامل ('.number_format($returned, 2).' ر.س من أصل '
            .number_format((float) $sale->total_amount, 2).' ر.س) — لا يمكن إنشاء مرتجع جديد.';

        if ($numbers !== '') {
            $message .= ' المرتجعات السابقة: '.$numbers.'.';
        }

        return $message;
    }

    protected function settlementBlockedMessage(PosSale $sale, float $requested, float $maxSettlement): string
    {
        $parts = [
            'تعذّرت تسوية '.number_format($requested, 2).' ر.س — الحد المتاح للتسوية حسب نوع الدفع هو '
            .number_format($maxSettlement, 2).' ر.س فقط.',
        ];

        if ($sale->payment_type === SalePaymentType::CREDIT) {
            $parts[] = 'للفواتير الآجلة استخدم «خصم من كشف حساب العميل».';
        }

        if ($sale->merchantCustomer && (float) $sale->merchantCustomer->balance < $requested
            && $sale->payment_type === SalePaymentType::CREDIT) {
            $parts[] = 'مديونية العميل الحالية '.number_format((float) $sale->merchantCustomer->balance, 2)
                .' ر.س — قد يكون العميل سدّد جزءاً من الذمة خارج هذا المرتجع.';
        }

        $misSettled = $this->completedReturns($sale)->first(
            fn (PosSaleReturn $return) => $sale->payment_type === SalePaymentType::CREDIT
                && in_array($return->refund_method, [RefundMethod::CASH, RefundMethod::CREDIT_NOTE], true)
        );

        if ($misSettled) {
            $parts[] = 'تنبيه: يوجد مرتجع سابق ('.$misSettled->return_number.') بُوّئ بطريقة غير مناسبة للفاتورة الآجلة — يُنصح بتصحيحه محاسبياً.';
        }

        return implode(' ', $parts);
    }

    protected function disallowedMethodMessage(PosSale $sale, RefundMethod $method): string
    {
        return match ($sale->payment_type) {
            SalePaymentType::CREDIT => match ($method) {
                RefundMethod::CASH => 'هذه الفاتورة آجلة بالكامل — لا يمكن استرداد نقد دون خصم من الذمة.',
                RefundMethod::CREDIT_NOTE => 'الفاتورة آجلة — لا يمكن إضافة رصيد دائن. استخدم «خصم من كشف حساب العميل».',
                RefundMethod::SPLIT_SETTLEMENT => 'التسوية التلقائية متاحة للفواتير الجزئية فقط.',
                default => 'طريقة التسوية المختارة غير متاحة لهذه الفاتورة.',
            },
            SalePaymentType::PARTIAL => match ($method) {
                RefundMethod::CASH => 'استخدم «تسوية تلقائية» لتوزيع المبلغ بين الذمة والنقد حسب الفاتورة.',
                RefundMethod::REDUCE_RECEIVABLE => 'لا يمكن خصم الذمة بالكامل — الفاتورة دفع جزئي. استخدم «تسوية تلقائية».',
                default => 'طريقة التسوية المختارة غير متاحة لهذه الفاتورة.',
            },
            default => match ($method) {
                RefundMethod::REDUCE_RECEIVABLE, RefundMethod::SPLIT_SETTLEMENT => 'هذه الفاتورة نقدية — لا يوجد مبلغ آجل لخصمه من كشف الحساب.',
                default => 'طريقة التسوية المختارة غير متاحة لهذه الفاتورة.',
            },
        };
    }

    /** @return Collection<int, PosSaleReturn> */
    protected function completedReturns(PosSale $sale): Collection
    {
        if ($sale->relationLoaded('returns')) {
            return $sale->returns->where('status', 'completed');
        }

        return $sale->returns()->where('status', 'completed')->get();
    }
}
