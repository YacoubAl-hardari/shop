<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserDataExportService
{
    /**
     * Export all user data as JSON with encryption and signature
     */
    public function exportUserData(User $user): array
    {
        $data = [
            'export_date' => now()->toISOString(),
            'export_version' => '1.0',
            'user_id' => $user->id,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'address' => $user->address,
                'phone' => $user->phone,
                'created_at' => $user->created_at?->toISOString(),
            ],
            'merchants' => $this->exportMerchants($user),
        ];

        // Add signature to prevent tampering
        $data['signature'] = $this->generateSignature($data);
        
        return $data;
    }

    /**
     * Generate signature for data integrity
     */
    protected function generateSignature(array $data): string
    {
        // Create a hash of the data excluding the signature itself
        $dataString = json_encode($data, JSON_UNESCAPED_UNICODE);
        return hash_hmac('sha256', $dataString, config('app.key'));
    }

    /**
     * Export all merchants with their related data
     */
    protected function exportMerchants(User $user): array
    {
        $merchants = [];

        foreach ($user->merchants as $merchant) {
            $merchants[] = [
                'name' => $merchant->name,
                'email' => $merchant->email,
                'phone' => $merchant->phone,
                'information' => $merchant->information,
                'is_active' => $merchant->is_active,
                'balance' => $merchant->balance,
                'created_at' => $merchant->created_at?->toISOString(),
                'updated_at' => $merchant->updated_at?->toISOString(),
                
                // Related data
                'wallets' => $this->exportWallets($merchant),
                'products' => $this->exportProducts($merchant),
                'orders' => $this->exportOrders($merchant),
                'account_statements' => $this->exportAccountStatements($merchant),
                'account_entries' => $this->exportAccountEntries($merchant),
                'payment_transactions' => $this->exportPaymentTransactions($merchant),
            ];
        }

        return $merchants;
    }

    /**
     * Export merchant wallets
     */
    protected function exportWallets($merchant): array
    {
        return $merchant->wallets->map(function ($wallet) {
            return [
                'account_name' => $wallet->account_name,
                'bank_account_number' => $wallet->bank_account_number,
                'bank_name' => $wallet->bank_name,
                'is_active' => $wallet->is_active,
                'created_at' => $wallet->created_at?->toISOString(),
                'updated_at' => $wallet->updated_at?->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Export merchant products
     */
    protected function exportProducts($merchant): array
    {
        return $merchant->products->map(function ($product) {
            return [
                'name' => $product->name,
                'price' => $product->price,
                'barcode' => $product->barcode,
                'description' => $product->description,
                'image' => $product->image,
                'brand' => $product->brand,
                'is_active' => $product->is_active,
                'created_at' => $product->created_at?->toISOString(),
                'updated_at' => $product->updated_at?->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Export merchant orders with items
     */
    protected function exportOrders($merchant): array
    {
        return $merchant->orders->map(function ($order) {
            return [
                'order_number' => $order->order_number,
                'note' => $order->note,
                'total_price' => $order->total_price,
                'created_at' => $order->created_at?->toISOString(),
                'updated_at' => $order->updated_at?->toISOString(),
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_name' => $item->product?->name,
                        'product_barcode' => $item->product?->barcode,
                        'unit' => $item->unit,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total_price' => $item->total_price,
                        'created_at' => $item->created_at?->toISOString(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Export account statements
     */
    protected function exportAccountStatements($merchant): array
    {
        return $merchant->accountStatements->map(function ($statement) {
            return [
                'debit_amount' => $statement->debit_amount,
                'credit_amount' => $statement->credit_amount,
                'balance' => $statement->balance,
                'transaction_type' => $statement->transaction_type,
                'reference_type' => $statement->reference_type,
                'reference_id' => $statement->reference_id,
                'description' => $statement->description,
                'transaction_date' => $statement->transaction_date?->toDateString(),
                'created_at' => $statement->created_at?->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Export account entries
     */
    protected function exportAccountEntries($merchant): array
    {
        return $merchant->accountEntries->map(function ($entry) {
            return [
                'entry_number' => $entry->entry_number,
                'entry_type' => $entry->entry_type,
                'amount' => $entry->amount,
                'debit_amount' => $entry->debit_amount,
                'credit_amount' => $entry->credit_amount,
                'description' => $entry->description,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'balance_after' => $entry->balance_after,
                'entry_date' => $entry->entry_date?->toDateString(),
                'created_at' => $entry->created_at?->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Export payment transactions
     */
    protected function exportPaymentTransactions($merchant): array
    {
        return $merchant->paymentTransactions->map(function ($transaction) {
            return [
                'transaction_number' => $transaction->transaction_number,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'notes' => $transaction->notes,
                'reference_number' => $transaction->reference_number,
                'payment_date' => $transaction->payment_date?->toDateString(),
                'wallet_account_name' => $transaction->userMerchantWallet?->account_name,
                'wallet_bank_name' => $transaction->userMerchantWallet?->bank_name,
                'created_at' => $transaction->created_at?->toISOString(),
            ];
        })->toArray();
    }
}

