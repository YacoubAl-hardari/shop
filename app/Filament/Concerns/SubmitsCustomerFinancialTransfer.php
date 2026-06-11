<?php

namespace App\Filament\Concerns;

use App\Enums\CustomerFinancialTransferPurpose;
use App\Filament\Schemas\PaymentDetailsSchema;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerFinancialTransfer;
use App\Models\MerchantCustomerStatementShare;
use App\Services\CustomerFinancialTransferService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

trait SubmitsCustomerFinancialTransfer
{
    protected function makeSubmitPaymentAction(): Action
    {
        return $this->makeFinancialTransferAction()
            ->visible(fn (): bool => $this->canSubmitFinancialTransfer());
    }

    protected function makeCancelPendingTransferAction(): Action
    {
        return Action::make('cancelPendingTransfer')
            ->label('إلغاء الطلب المعلّق')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (): bool => $this->getPendingTransfer() !== null)
            ->action(function (): void {
                $transfer = $this->getPendingTransfer();
                $user = Auth::user();

                if (! $transfer || ! $user) {
                    return;
                }

                try {
                    app(CustomerFinancialTransferService::class)->cancel($transfer, $user);

                    Notification::make()
                        ->title('تم إلغاء الطلب')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('خطأ في الإلغاء')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function canSubmitFinancialTransfer(): bool
    {
        return $this->getTransferShare() !== null
            && $this->getTransferTeamId() !== null
            && $this->getPendingTransfer() === null;
    }

    protected function getPendingTransfer(): ?MerchantCustomerFinancialTransfer
    {
        $share = $this->getTransferShare();

        if ($share === null) {
            return null;
        }

        return MerchantCustomerFinancialTransfer::acrossTeams()
            ->where('statement_share_id', $share->id)
            ->pending()
            ->first();
    }

    protected function makeFinancialTransferAction(): Action
    {
        $teamId = $this->getTransferTeamId();

        return Action::make('submitPayment')
            ->label('سداد')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->form([
                Placeholder::make('customer_balances')
                    ->label('حالة حسابك')
                    ->content(function (): string {
                        $customer = $this->getTransferCustomer();

                        if (! $customer) {
                            return '—';
                        }

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
                    ->label('توزيع المبلغ المتوقع')
                    ->visible(fn (Get $get): bool => (float) ($get('amount') ?? 0) > 0)
                    ->content(function (Get $get): string {
                        $amount = (float) ($get('amount') ?? 0);
                        $customer = $this->getTransferCustomer();

                        if (! $customer) {
                            return '—';
                        }

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
                PaymentDetailsSchema::accountSelectForTeam($teamId),
                PaymentDetailsSchema::accountPreviewForTeam($teamId),
                PaymentDetailsSchema::referenceInput('payment_method', 'reference_number'),
                Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(2),
                Placeholder::make('pending_notice')
                    ->label('')
                    ->content('سيتم إرسال طلب السداد للتاجر للمراجعة. لن يُسجّل المبلغ في كشف حسابك حتى يؤكد التاجر استلامه.'),
            ])
            ->action(function (array $data): void {
                $share = $this->getTransferShare();
                $user = Auth::user();

                if (! $share || ! $user) {
                    return;
                }

                try {
                    app(CustomerFinancialTransferService::class)->submit(
                        $share,
                        $user,
                        (float) $data['amount'],
                        $data['payment_method'] ?? 'cash',
                        $data['merchant_payment_account_id'] ?? null,
                        $data['reference_number'] ?? null,
                        $data['notes'] ?? null,
                        CustomerFinancialTransferPurpose::SETTLEMENT,
                    );

                    Notification::make()
                        ->title('تم إرسال طلب السداد')
                        ->body('بانتظار تأكيد التاجر لاستلام المبلغ.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('خطأ في إرسال الطلب')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    abstract protected function getTransferShare(): ?MerchantCustomerStatementShare;

    abstract protected function getTransferCustomer(): ?MerchantCustomer;

    abstract protected function getTransferTeamId(): ?int;
}
