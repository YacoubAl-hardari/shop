<?php

namespace App\Filament\Resources\UserMerchantOrders\Pages;

use Illuminate\Support\Facades\Auth;
use App\Services\OrderService;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserMerchantOrders\UserMerchantOrderResource;

class CreateUserMerchantOrder extends CreateRecord
{
    protected static string $resource = UserMerchantOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $orderService = app(OrderService::class);
        
        // Extract order items from the form data
        $orderItems = $data['order_items'] ?? [];
        unset($data['order_items']);

        // Get current user ID
        $data['user_id'] = Auth::user()->id;

        // Generate order number if not provided
        if (empty($data['order_number'])) {
            $data['order_number'] = $orderService->generateOrderNumber($data['user_id']);
        }

        // Calculate total price from order items if not provided
        if (empty($data['total_price']) && !empty($orderItems)) {
            $data['total_price'] = $orderService->calculateTotalPrice($orderItems);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $orderService = app(OrderService::class);
        
        // Get order items from form data
        $orderItems = $this->data['order_items'] ?? [];
        
        // Process order (create items, entry, and update statement)
        $orderService->processOrder($this->record, $orderItems);
    }
}
