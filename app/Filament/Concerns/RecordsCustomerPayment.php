<?php

namespace App\Filament\Concerns;

use App\Filament\Schemas\PaymentDetailsSchema;
use App\Models\MerchantCustomer;
use App\Services\PosSaleService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

trait RecordsCustomerPayment
{
    protected function makeRecordCustomerPaymentAction(): Action
    {
        return Action::make('recordPayment')
            ->label('تسجيل سداد')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->form([
                Placeholder::make('customer_balances')
                    ->label('حالة حساب العميل')
                    ->content(function (): string {
                        $customer = $this->getPaymentCustomer();
                        $parts = [];

                        if ($customer->hasDebt()) {
                            $parts[] = 'مديونية: '.number_format($customer->debtBalance(), 2).' ر.س';
                        }

                        if ($customer->hasPrepaidBalance()) {
                            $parts[] = 'رصيد فائض: '.number_format($customer->prepaidBalance(), 2).' ر.س';
                        }

                        if ($parts === []) {
                            return 'لا توجد مديونية أو رصيد فائض حالياً';
                        }

                        return implode(' | ', $parts);
                    }),
                TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->prefix('ر.س')
                    ->minValue(0.01)
                    ->live()
                    ->helperText('إذا تجاوز المبلغ المستحق تُسدّد الذمة ويُضاف الفائض للرصيد الفائض'),
                Placeholder::make('payment_split_preview')
                    ->label('توزيع المبلغ')
                    ->visible(fn (Get $get): bool => (float) ($get('amount') ?? 0) > 0)
                    ->content(function (Get $get): string {
                        $amount = (float) ($get('amount') ?? 0);
                        $customer = $this->getPaymentCustomer();
                        $applied = min($amount, $customer->debtBalance());
                        $surplus = $amount - $applied;

                        if ($applied > 0 && $surplus > 0) {
                            return 'يُسدّد '.number_format($applied, 2).' ر.س من المديونية ويُضاف '.number_format($surplus, 2).' ر.س للرصيد الفائض';
                        }

                        if ($applied > 0) {
                            return 'يُسدّد '.number_format($applied, 2).' ر.س من المديونية';
                        }

                        return 'يُضاف '.number_format($surplus, 2).' ر.س كرصيد فائض (دفعة مقدمة)';
                    }),
                PaymentDetailsSchema::methodSelect(),
                PaymentDetailsSchema::accountSelect(),
                PaymentDetailsSchema::accountPreview(),
                PaymentDetailsSchema::referenceInput('payment_method', 'reference_number'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(2),
            ])
            ->action(function (array $data): void {
                $customer = $this->getPaymentCustomer();

                try {
                    $payment = app(PosSaleService::class)->recordCustomerPayment(
                        Filament::getTenant(),
                        $customer,
                        (float) $data['amount'],
                        $data['payment_method'] ?? 'cash',
                        $data['merchant_payment_account_id'] ?? null,
                        $data['reference_number'] ?? null,
                        $data['notes'] ?? null,
                    );

                    $body = null;

                    if ((float) $payment->surplus_to_credit > 0 && (float) $payment->applied_to_balance > 0) {
                        $body = 'سُدّدت المديونية بـ '.number_format((float) $payment->applied_to_balance, 2)
                            .' ر.س وأُضيف '.number_format((float) $payment->surplus_to_credit, 2).' ر.س للرصيد الفائض';
                    } elseif ((float) $payment->surplus_to_credit > 0) {
                        $body = 'أُضيف '.number_format((float) $payment->surplus_to_credit, 2).' ر.س للرصيد الفائض';
                    }

                    Notification::make()
                        ->title('تم تسجيل السداد')
                        ->body($body)
                        ->success()
                        ->send();

                    $customer->refresh();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('خطأ في تسجيل السداد')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    abstract protected function getPaymentCustomer(): MerchantCustomer;
}
