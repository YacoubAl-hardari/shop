<?php

namespace App\Filament\Resources\UserMerchantPaymentTransactions\Pages;

use Illuminate\Support\Facades\Auth;
use App\Services\PaymentTransactionService;
use App\Enums\PaymentTransactionStatus;
use App\Filament\Resources\UserMerchantPaymentTransactions\UserMerchantPaymentTransactionResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateUserMerchantPaymentTransaction extends CreateRecord
{
    protected static string $resource = UserMerchantPaymentTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        $data['status'] = PaymentTransactionStatus::COMPLETED;

        return $data;
    }

    protected function beforeCreate(): void
    {
        $paymentService = app(PaymentTransactionService::class);
        
        $merchantId = $this->data['user_merchant_id'];
        $paymentAmount = $this->data['amount'];

        // Validate payment
        $validation = $paymentService->validatePayment($merchantId, $paymentAmount);

        if (!$validation['valid']) {
            Notification::make()
                ->title('خطأ')
                ->body($validation['error'])
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }
    }

    protected function afterCreate(): void
    {
        $paymentService = app(PaymentTransactionService::class);
        
        // Process payment (create entry and update statement)
        $paymentService->processPayment($this->record);
    }
}
