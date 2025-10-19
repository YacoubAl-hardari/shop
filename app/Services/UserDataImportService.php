<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserMerchant;
use App\Models\UserMerchantWallet;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchantOrderItem;
use App\Models\UserMerchantAccountStatement;
use App\Models\UserMerchantAccountEntry;
use App\Models\UserMerchantPaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserDataImportService
{
    /**
     * Import user data from JSON array with validation and signature check
     */
    public function importUserData(User $user, array $data): bool
    {
        DB::beginTransaction();

        try {
            Log::info("Starting import process for user: {$user->id}");

            // Validate data structure
            $this->validateDataStructure($data);

            // Verify signature to prevent tampering
            if (!$this->verifySignature($data)) {
                throw new \Exception('البيانات تالفة أو تم التلاعب بها. التوقيع الرقمي غير صحيح.');
            }

            // Additional integrity checks
            $this->validateDataIntegrity($data);

            // Update user basic info (optional)
            $user->update([
                'address' => $data['user']['address'] ?? $user->address,
                'phone' => $data['user']['phone'] ?? $user->phone,
            ]);

            // Import merchants and their related data
            if (isset($data['merchants']) && is_array($data['merchants'])) {
                foreach ($data['merchants'] as $merchantData) {
                    $this->importMerchant($user, $merchantData);
                }
            }

            DB::commit();
            Log::info("Successfully imported data for user: {$user->id}");
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to import data for user: {$user->id}. Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate basic data structure
     */
    protected function validateDataStructure(array $data): void
    {
        $requiredFields = ['export_date', 'export_version', 'user', 'merchants', 'signature'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("البيانات غير كاملة. الحقل المطلوب مفقود: {$field}");
            }
        }

        // Validate user data
        if (!isset($data['user']['name']) || !isset($data['user']['email'])) {
            throw new \Exception('بيانات المستخدم غير كاملة');
        }
    }

    /**
     * Verify data signature
     */
    protected function verifySignature(array $data): bool
    {
        $signature = $data['signature'] ?? '';
        unset($data['signature']);
        
        $dataString = json_encode($data, JSON_UNESCAPED_UNICODE);
        $expectedSignature = hash_hmac('sha256', $dataString, config('app.key'));
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Validate data integrity (check for orphaned records)
     */
    protected function validateDataIntegrity(array $data): void
    {
        if (!is_array($data['merchants'])) {
            throw new \Exception('بيانات التجار يجب أن تكون مصفوفة');
        }

        foreach ($data['merchants'] as $merchantData) {
            // Validate that orders have items if orders exist
            if (isset($merchantData['orders']) && is_array($merchantData['orders'])) {
                foreach ($merchantData['orders'] as $order) {
                    if (!isset($order['items']) || !is_array($order['items'])) {
                        throw new \Exception('كل طلب يجب أن يحتوي على عناصر. البيانات غير صحيحة.');
                    }
                    
                    // Validate order has required fields
                    if (!isset($order['order_number']) || !isset($order['total_price'])) {
                        throw new \Exception('بيانات الطلب غير كاملة');
                    }
                }
            }

            // Validate payment transactions reference wallets
            if (isset($merchantData['payment_transactions']) && is_array($merchantData['payment_transactions'])) {
                foreach ($merchantData['payment_transactions'] as $transaction) {
                    if (isset($transaction['wallet_account_name']) && 
                        (!isset($merchantData['wallets']) || empty($merchantData['wallets']))) {
                        throw new \Exception('معاملات الدفع تشير إلى محافظ غير موجودة');
                    }
                }
            }
        }
    }

    /**
     * Import a single merchant with all related data
     */
    protected function importMerchant(User $user, array $merchantData): void
    {
        // Create merchant
        $merchant = UserMerchant::create([
            'user_id' => $user->id,
            'name' => $merchantData['name'],
            'email' => $merchantData['email'] ?? null,
            'phone' => $merchantData['phone'] ?? null,
            'information' => $merchantData['information'] ?? null,
            'is_active' => $merchantData['is_active'] ?? true,
            'balance' => $merchantData['balance'] ?? 0,
            'created_at' => isset($merchantData['created_at']) ? Carbon::parse($merchantData['created_at']) : now(),
            'updated_at' => isset($merchantData['updated_at']) ? Carbon::parse($merchantData['updated_at']) : now(),
        ]);

        Log::info("Created merchant: {$merchant->id}");

        // Import related data in correct order
        $this->importWallets($merchant, $merchantData['wallets'] ?? []);
        $this->importProducts($merchant, $merchantData['products'] ?? []);
        $this->importOrders($merchant, $merchantData['orders'] ?? []);
        $this->importAccountStatements($merchant, $user, $merchantData['account_statements'] ?? []);
        $this->importAccountEntries($merchant, $user, $merchantData['account_entries'] ?? []);
        $this->importPaymentTransactions($merchant, $user, $merchantData['payment_transactions'] ?? []);
    }

    /**
     * Import wallets
     */
    protected function importWallets(UserMerchant $merchant, array $wallets): void
    {
        foreach ($wallets as $walletData) {
            UserMerchantWallet::create([
                'user_merchant_id' => $merchant->id,
                'account_name' => $walletData['account_name'] ?? null,
                'bank_account_number' => $walletData['bank_account_number'] ?? null,
                'bank_name' => $walletData['bank_name'] ?? null,
                'is_active' => $walletData['is_active'] ?? true,
                'created_at' => isset($walletData['created_at']) ? Carbon::parse($walletData['created_at']) : now(),
                'updated_at' => isset($walletData['updated_at']) ? Carbon::parse($walletData['updated_at']) : now(),
            ]);
        }
    }

    /**
     * Import products
     */
    protected function importProducts(UserMerchant $merchant, array $products): void
    {
        foreach ($products as $productData) {
            UserMerchantProduct::create([
                'user_merchant_id' => $merchant->id,
                'name' => $productData['name'],
                'price' => $productData['price'] ?? 0,
                'barcode' => $productData['barcode'] ?? null,
                'description' => $productData['description'] ?? null,
                'image' => $productData['image'] ?? null,
                'brand' => $productData['brand'] ?? null,
                'is_active' => $productData['is_active'] ?? true,
                'created_at' => isset($productData['created_at']) ? Carbon::parse($productData['created_at']) : now(),
                'updated_at' => isset($productData['updated_at']) ? Carbon::parse($productData['updated_at']) : now(),
            ]);
        }
    }

    /**
     * Import orders with items
     */
    protected function importOrders(UserMerchant $merchant, array $orders): void
    {
        foreach ($orders as $orderData) {
            $order = UserMerchantOrder::create([
                'user_merchant_id' => $merchant->id,
                'user_id' => $merchant->user_id,
                'order_number' => $orderData['order_number'],
                'note' => $orderData['note'] ?? null,
                'total_price' => $orderData['total_price'] ?? 0,
                'created_at' => isset($orderData['created_at']) ? Carbon::parse($orderData['created_at']) : now(),
                'updated_at' => isset($orderData['updated_at']) ? Carbon::parse($orderData['updated_at']) : now(),
            ]);

            // Import order items
            if (isset($orderData['items']) && is_array($orderData['items'])) {
                foreach ($orderData['items'] as $itemData) {
                    // Try to find the product by barcode or name
                    $product = null;
                    if (isset($itemData['product_barcode'])) {
                        $product = $merchant->products()->where('barcode', $itemData['product_barcode'])->first();
                    }
                    if (!$product && isset($itemData['product_name'])) {
                        $product = $merchant->products()->where('name', $itemData['product_name'])->first();
                    }

                    UserMerchantOrderItem::create([
                        'user_merchant_order_id' => $order->id,
                        'user_merchant_product_id' => $product?->id,
                        'unit' => $itemData['unit'] ?? null,
                        'quantity' => $itemData['quantity'] ?? 0,
                        'price' => $itemData['price'] ?? 0,
                        'total_price' => $itemData['total_price'] ?? 0,
                        'created_at' => isset($itemData['created_at']) ? Carbon::parse($itemData['created_at']) : now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Import account statements
     */
    protected function importAccountStatements(UserMerchant $merchant, User $user, array $statements): void
    {
        foreach ($statements as $statementData) {
            UserMerchantAccountStatement::create([
                'user_id' => $user->id,
                'user_merchant_id' => $merchant->id,
                'debit_amount' => $statementData['debit_amount'] ?? 0,
                'credit_amount' => $statementData['credit_amount'] ?? 0,
                'balance' => $statementData['balance'] ?? 0,
                'transaction_type' => $statementData['transaction_type'] ?? null,
                'reference_type' => $statementData['reference_type'] ?? null,
                'reference_id' => $statementData['reference_id'] ?? null,
                'description' => $statementData['description'] ?? null,
                'transaction_date' => isset($statementData['transaction_date']) ? Carbon::parse($statementData['transaction_date']) : now(),
                'created_at' => isset($statementData['created_at']) ? Carbon::parse($statementData['created_at']) : now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Import account entries
     */
    protected function importAccountEntries(UserMerchant $merchant, User $user, array $entries): void
    {
        foreach ($entries as $entryData) {
            UserMerchantAccountEntry::create([
                'user_id' => $user->id,
                'user_merchant_id' => $merchant->id,
                'entry_number' => $entryData['entry_number'] ?? null,
                'entry_type' => $entryData['entry_type'] ?? null,
                'amount' => $entryData['amount'] ?? 0,
                'debit_amount' => $entryData['debit_amount'] ?? 0,
                'credit_amount' => $entryData['credit_amount'] ?? 0,
                'description' => $entryData['description'] ?? null,
                'reference_type' => $entryData['reference_type'] ?? null,
                'reference_id' => $entryData['reference_id'] ?? null,
                'balance_after' => $entryData['balance_after'] ?? 0,
                'entry_date' => isset($entryData['entry_date']) ? Carbon::parse($entryData['entry_date']) : now(),
                'created_by' => $user->id,
                'created_at' => isset($entryData['created_at']) ? Carbon::parse($entryData['created_at']) : now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Import payment transactions
     */
    protected function importPaymentTransactions(UserMerchant $merchant, User $user, array $transactions): void
    {
        foreach ($transactions as $transactionData) {
            // Try to find matching wallet
            $wallet = null;
            if (isset($transactionData['wallet_account_name'])) {
                $wallet = $merchant->wallets()
                    ->where('account_name', $transactionData['wallet_account_name'])
                    ->first();
            }

            UserMerchantPaymentTransaction::create([
                'user_id' => $user->id,
                'user_merchant_id' => $merchant->id,
                'user_merchant_wallet_id' => $wallet?->id,
                'transaction_number' => $transactionData['transaction_number'] ?? null,
                'amount' => $transactionData['amount'] ?? 0,
                'payment_method' => $transactionData['payment_method'] ?? null,
                'status' => $transactionData['status'] ?? null,
                'notes' => $transactionData['notes'] ?? null,
                'reference_number' => $transactionData['reference_number'] ?? null,
                'payment_date' => isset($transactionData['payment_date']) ? Carbon::parse($transactionData['payment_date']) : now(),
                'created_at' => isset($transactionData['created_at']) ? Carbon::parse($transactionData['created_at']) : now(),
                'updated_at' => now(),
            ]);
        }
    }
}

