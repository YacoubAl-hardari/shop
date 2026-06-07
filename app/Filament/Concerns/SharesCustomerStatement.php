<?php

namespace App\Filament\Concerns;

use App\Models\MerchantCustomer;
use App\Services\CustomerStatementShareService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

trait SharesCustomerStatement
{
    protected function makeShareStatementAction(): Action
    {
        return Action::make('shareStatement')
            ->label('مشاركة كشف الحساب')
            ->icon('heroicon-o-share')
            ->color('success')
            ->visible(fn (): bool => $this->getShareCustomer()->isLinkedToUser()
                && ! $this->getShareCustomer()->isStatementShared())
            ->requiresConfirmation()
            ->modalHeading('مشاركة كشف الحساب')
            ->modalDescription(fn (): string => 'سيتم مشاركة كشف حساب العميل «'.$this->getShareCustomer()->name.'» مع المستخدم «'.$this->getShareCustomer()->user?->name.'» وسيصله إشعار فوراً.')
            ->modalSubmitActionLabel('نعم، شارك كشف الحساب')
            ->action(function (): void {
                try {
                    $customer = $this->getShareCustomer()->fresh();

                    app(CustomerStatementShareService::class)->share(
                        $customer,
                        Auth::user(),
                    );

                    Notification::make()
                        ->title('تمت مشاركة كشف الحساب')
                        ->success()
                        ->send();

                    $this->getShareCustomer()->refresh();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('تعذّرت المشاركة')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected function makeCloseStatementShareAction(): Action
    {
        return Action::make('closeStatementShare')
            ->label('إيقاف المشاركة')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (): bool => $this->getShareCustomer()->isStatementShared())
            ->requiresConfirmation()
            ->modalHeading('إيقاف مشاركة كشف الحساب')
            ->modalDescription('لن يتمكن العميل من الاطلاع على كشف الحساب بعد الآن وسيصله إشعار بإغلاق المشاركة.')
            ->modalSubmitActionLabel('نعم، أوقف المشاركة')
            ->action(function (): void {
                try {
                    app(CustomerStatementShareService::class)->closeActiveShare(
                        $this->getShareCustomer(),
                        Auth::user(),
                    );

                    Notification::make()
                        ->title('تم إيقاف المشاركة')
                        ->success()
                        ->send();

                    $this->getShareCustomer()->refresh();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('تعذّر إيقاف المشاركة')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    abstract protected function getShareCustomer(): MerchantCustomer;
}
