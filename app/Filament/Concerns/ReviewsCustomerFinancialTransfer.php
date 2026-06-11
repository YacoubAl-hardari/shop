<?php

namespace App\Filament\Concerns;

use App\Models\MerchantCustomerFinancialTransfer;
use App\Services\CustomerFinancialTransferService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait ReviewsCustomerFinancialTransfer
{
    protected function makeApproveTransferAction(): Action
    {
        return Action::make('approveTransfer')
            ->label('تأكيد الاستلام')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('تأكيد استلام المبلغ')
            ->modalDescription('بتأكيد الاستلام سيتم تسجيل المبلغ في كشف حساب العميل والقيود المحاسبية بشكل نهائي. تأكد من استلام المبلغ فعلياً.')
            ->modalSubmitActionLabel('نعم، تم الاستلام')
            ->visible(fn (): bool => $this->getReviewTransferRecord()?->isPending() ?? false)
            ->action(function (): void {
                $record = $this->getReviewTransferRecord();
                $reviewer = Auth::user();

                if (! $record || ! $reviewer) {
                    return;
                }

                try {
                    app(CustomerFinancialTransferService::class)->approve($record, $reviewer);

                    Notification::make()
                        ->title('تم تأكيد الاستلام')
                        ->body('تم تسجيل السداد في كشف حساب العميل والنظام المحاسبي.')
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('خطأ في التأكيد')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function makeRejectTransferAction(): Action
    {
        return Action::make('rejectTransfer')
            ->label('رفض العملية')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (): bool => $this->getReviewTransferRecord()?->isPending() ?? false)
            ->form([
                Textarea::make('rejection_reason')
                    ->label('سبب الرفض')
                    ->required()
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                $record = $this->getReviewTransferRecord();
                $reviewer = Auth::user();

                if (! $record || ! $reviewer) {
                    return;
                }

                try {
                    app(CustomerFinancialTransferService::class)->reject(
                        $record,
                        $reviewer,
                        $data['rejection_reason'],
                    );

                    Notification::make()
                        ->title('تم رفض العملية')
                        ->body('لم يُسجّل أي مبلغ في النظام المحاسبي.')
                        ->warning()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('خطأ في الرفض')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function getReviewTransferRecord(): ?MerchantCustomerFinancialTransfer
    {
        if (property_exists($this, 'record') && $this->record instanceof MerchantCustomerFinancialTransfer) {
            return $this->record;
        }

        return null;
    }
}
