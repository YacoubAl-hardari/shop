<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\CustomerFinancialTransferPurpose;
use App\Enums\CustomerFinancialTransferStatus;
use App\Enums\InventoryCountStatus;
use App\Enums\JournalEntryStatus;
use App\Enums\MerchantPaymentAccountType;
use App\Enums\NormalBalance;
use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use App\Enums\SalePaymentType;
use App\Enums\StockMovementType;
use App\Models\Account;
use App\Models\Distributor;
use App\Models\FiscalYearClosing;
use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerFinancialTransfer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantCustomerStatementShare;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\PosExchangeItem;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSaleReturn;
use App\Models\PosSaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamDataImportService
{
    public function __construct(
        protected TeamDataDeletionService $deletionService,
    ) {}

    public function importTeamData(Team $team, User $user, array $data): bool
    {
        DB::beginTransaction();

        try {
            Log::info("Starting import for team: {$team->id}");

            $this->validateDataStructure($data);
            $this->verifySignature($data);
            $this->validateTeamMatch($team, $data);

            $this->deletionService->purgeTeamBusinessData($team);

            $supplierMap = $this->importSuppliers($team, $data['suppliers'] ?? []);
            $distributorMap = $this->importDistributors($team, $data['distributors'] ?? [], $supplierMap);
            $paymentAccountMap = $this->importPaymentAccounts($team, $data['merchant_payment_accounts'] ?? []);
            $customerMap = $this->importCustomers($team, $data['merchant_customers'] ?? []);
            $productMap = $this->importProducts($team, $data['merchant_products'] ?? [], $supplierMap, $distributorMap);
            $accountMap = $this->importAccounts($team, $data['accounts'] ?? []);
            $saleMap = $this->importPosSales($team, $user, $data['pos_sales'] ?? [], $customerMap, $paymentAccountMap, $productMap);
            $paymentMap = $this->importCustomerPayments($team, $user, $data['merchant_customer_payments'] ?? [], $customerMap, $paymentAccountMap);
            $entryMap = $this->importJournalEntries($team, $user, $data['journal_entries'] ?? [], $accountMap, $customerMap, $saleMap, $paymentMap);

            $shareMap = $this->importStatementShares($team, $data['merchant_customer_statement_shares'] ?? [], $customerMap);
            $this->importFinancialTransfers($team, $data['merchant_customer_financial_transfers'] ?? [], $customerMap, $shareMap, $paymentAccountMap, $paymentMap);
            $returnMap = $this->importSaleReturns($team, $data['pos_sale_returns'] ?? [], $saleMap, $productMap);
            $inventoryCountMap = $this->importInventoryCounts($team, $data['inventory_counts'] ?? [], $entryMap, $productMap);
            $this->importFiscalYearClosings($team, $data['fiscal_year_closings'] ?? [], $entryMap);
            $this->importStockMovements($team, $data['stock_movements'] ?? [], $productMap, $entryMap, $saleMap, $returnMap, $inventoryCountMap);

            DB::commit();
            Log::info("Successfully imported data for team: {$team->id}");

            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to import data for team: {$team->id}. Error: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function validateDataStructure(array $data): void
    {
        $required = ['export_date', 'export_version', 'export_type', 'team', 'signature'];

        foreach ($required as $field) {
            if (! isset($data[$field])) {
                throw new \InvalidArgumentException("البيانات غير كاملة. الحقل المطلوب مفقود: {$field}");
            }
        }

        if (($data['export_type'] ?? null) !== 'merchant_business') {
            throw new \InvalidArgumentException('نوع الملف غير مدعوم. استخدم ملف تصدير بيانات التاجر (JSON).');
        }
    }

    protected function verifySignature(array $data): void
    {
        $signature = $data['signature'] ?? '';
        unset($data['signature']);

        $expected = hash_hmac('sha256', json_encode($data, JSON_UNESCAPED_UNICODE), config('app.key'));

        if (! hash_equals($expected, $signature)) {
            throw new \InvalidArgumentException('البيانات تالفة أو تم التلاعب بها. التوقيع الرقمي غير صحيح.');
        }
    }

    protected function validateTeamMatch(Team $team, array $data): void
    {
        $exportedSlug = $data['team']['slug'] ?? null;

        if ($exportedSlug && $exportedSlug !== $team->slug) {
            throw new \InvalidArgumentException('هذا الملف يخص فرعاً آخر. يمكن الاسترجاع فقط لنفس الفرع.');
        }
    }

    /**
     * @return array<string, int>
     */
    protected function importSuppliers(Team $team, array $suppliers): array
    {
        $map = [];

        foreach ($suppliers as $row) {
            $supplier = Supplier::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'name' => $row['name'],
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'tax_number' => $row['tax_number'] ?? null,
                'balance' => $row['balance'] ?? 0,
                'is_active' => $row['is_active'] ?? true,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$row['name']] = $supplier->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $supplierMap
     * @return array<string, int>
     */
    protected function importDistributors(Team $team, array $distributors, array $supplierMap): array
    {
        $map = [];

        foreach ($distributors as $row) {
            $key = ($row['supplier_name'] ?? '').'::'.($row['name'] ?? '');
            $distributor = Distributor::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'supplier_id' => $supplierMap[$row['supplier_name'] ?? ''] ?? null,
                'name' => $row['name'],
                'phone' => $row['phone'] ?? null,
                'contact_info' => $row['contact_info'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$key] = $distributor->id;
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    protected function importPaymentAccounts(Team $team, array $accounts): array
    {
        $map = [];

        foreach ($accounts as $row) {
            $account = MerchantPaymentAccount::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'type' => MerchantPaymentAccountType::tryFrom($row['type'] ?? '') ?? $row['type'],
                'name' => $row['name'],
                'account_number' => $row['account_number'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'is_default' => $row['is_default'] ?? false,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$row['name']] = $account->id;
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    protected function importCustomers(Team $team, array $customers): array
    {
        $map = [];

        foreach ($customers as $row) {
            $customer = MerchantCustomer::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'name' => $row['name'],
                'phone' => $row['phone'] ?? null,
                'email' => $row['email'] ?? null,
                'balance' => $row['balance'] ?? 0,
                'credit_balance' => $row['credit_balance'] ?? 0,
                'is_active' => $row['is_active'] ?? true,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$this->customerKey($row['name'], $row['phone'] ?? null)] = $customer->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $supplierMap
     * @param  array<string, int>  $distributorMap
     * @return array<string, int>
     */
    protected function importProducts(Team $team, array $products, array $supplierMap, array $distributorMap): array
    {
        $map = [];

        foreach ($products as $row) {
            $distributorKey = ($row['supplier_name'] ?? '').'::'.($row['distributor_name'] ?? '');

            $product = MerchantProduct::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'supplier_id' => $supplierMap[$row['supplier_name'] ?? ''] ?? null,
                'distributor_id' => $distributorMap[$distributorKey] ?? null,
                'name' => $row['name'],
                'sku' => $row['sku'] ?? null,
                'barcode' => $row['barcode'] ?? null,
                'price' => $row['price'] ?? 0,
                'cost' => $row['cost'] ?? 0,
                'stock_quantity' => $row['stock_quantity'] ?? 0,
                'unit' => $row['unit'] ?? null,
                'is_active' => $row['is_active'] ?? true,
                'description' => $row['description'] ?? null,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$this->productKey($row)] = $product->id;
        }

        return $map;
    }

    /**
     * @return array<string, int>
     */
    protected function importAccounts(Team $team, array $accounts): array
    {
        $map = [];
        $pending = collect($accounts);

        while ($pending->isNotEmpty()) {
            $importedThisRound = false;

            foreach ($pending as $index => $row) {
                $parentCode = $row['parent_code'] ?? null;

                if ($parentCode && ! isset($map[$parentCode])) {
                    continue;
                }

                $parent = $parentCode
                    ? Account::withoutGlobalScopes()->find($map[$parentCode])
                    : null;

                $account = Account::create([
                    'team_id' => $team->id,
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'type' => AccountType::tryFrom($row['type'] ?? '') ?? $row['type'],
                    'normal_balance' => NormalBalance::tryFrom($row['normal_balance'] ?? '') ?? $row['normal_balance'],
                    'is_system' => $row['is_system'] ?? true,
                    'is_active' => $row['is_active'] ?? true,
                    'description' => $row['description'] ?? null,
                ], $parent);

                $map[$row['code']] = $account->id;
                $pending->forget($index);
                $importedThisRound = true;
            }

            if (! $importedThisRound) {
                throw new \InvalidArgumentException('تعذّر استيراد شجرة الحسابات. تحقق من رموز الحسابات الأب.');
            }
        }

        Account::withoutGlobalScopes()->where('team_id', $team->id)->fixTree();

        return $map;
    }

    /**
     * @param  array<string, int>  $customerMap
     * @param  array<string, int>  $paymentAccountMap
     * @param  array<string, int>  $productMap
     * @return array<string, int>
     */
    protected function importPosSales(
        Team $team,
        User $user,
        array $sales,
        array $customerMap,
        array $paymentAccountMap,
        array $productMap,
    ): array {
        $map = [];

        foreach ($sales as $row) {
            $customerId = isset($row['customer_name'])
                ? ($customerMap[$this->customerKey($row['customer_name'], $row['customer_phone'] ?? null)] ?? null)
                : null;

            $sale = PosSale::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'sale_number' => $row['sale_number'],
                'merchant_customer_id' => $customerId,
                'total_amount' => $row['total_amount'] ?? 0,
                'paid_amount' => $row['paid_amount'] ?? 0,
                'credit_amount' => $row['credit_amount'] ?? 0,
                'customer_credit_applied' => $row['customer_credit_applied'] ?? 0,
                'payment_type' => SalePaymentType::tryFrom($row['payment_type'] ?? '') ?? $row['payment_type'],
                'payment_method' => $row['payment_method'] ?? 'cash',
                'merchant_payment_account_id' => $paymentAccountMap[$row['payment_account_name'] ?? ''] ?? null,
                'payment_reference' => $row['payment_reference'] ?? null,
                'status' => $row['status'] ?? 'completed',
                'notes' => $row['notes'] ?? null,
                'sold_by' => $user->id,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            foreach ($row['items'] ?? [] as $item) {
                PosSaleItem::create([
                    'pos_sale_id' => $sale->id,
                    'merchant_product_id' => $productMap[$this->productKeyFromItem($item)] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'] ?? 0,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total' => $item['total'] ?? 0,
                ]);
            }

            $map[$row['sale_number']] = $sale->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $customerMap
     * @param  array<string, int>  $paymentAccountMap
     * @return array<string, int>
     */
    protected function importCustomerPayments(
        Team $team,
        User $user,
        array $payments,
        array $customerMap,
        array $paymentAccountMap,
    ): array {
        $map = [];

        foreach ($payments as $row) {
            $customerId = $customerMap[$this->customerKey($row['customer_name'] ?? '', $row['customer_phone'] ?? null)] ?? null;

            $payment = MerchantCustomerPayment::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'merchant_customer_id' => $customerId,
                'merchant_payment_account_id' => $paymentAccountMap[$row['payment_account_name'] ?? ''] ?? null,
                'payment_method' => $row['payment_method'] ?? 'cash',
                'amount' => $row['amount'] ?? 0,
                'applied_to_balance' => $row['applied_to_balance'] ?? 0,
                'surplus_to_credit' => $row['surplus_to_credit'] ?? 0,
                'reference_number' => $row['reference_number'] ?? null,
                'notes' => $row['notes'] ?? null,
                'received_by' => $user->id,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$this->paymentExportKey(
                $row['customer_name'] ?? '',
                $row['customer_phone'] ?? null,
                (float) ($row['amount'] ?? 0),
                isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            )] = $payment->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $accountMap
     * @param  array<string, int>  $customerMap
     * @param  array<string, int>  $saleMap
     * @param  array<string, int>  $paymentMap
     * @return array<string, int>
     */
    protected function importJournalEntries(
        Team $team,
        User $user,
        array $entries,
        array $accountMap,
        array $customerMap,
        array $saleMap,
        array $paymentMap,
    ): array {
        $map = [];

        foreach ($entries as $row) {
            $reference = $this->resolveReferenceId($row['reference_key'] ?? null, $saleMap, $paymentMap);

            $entry = JournalEntry::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'entry_number' => $row['entry_number'],
                'entry_date' => isset($row['entry_date']) ? Carbon::parse($row['entry_date']) : now(),
                'description' => $row['description'] ?? null,
                'status' => JournalEntryStatus::tryFrom($row['status'] ?? '') ?? JournalEntryStatus::POSTED,
                'reference_type' => $reference['type'],
                'reference_id' => $reference['id'],
                'created_by' => $user->id,
                'posted_at' => isset($row['posted_at']) ? Carbon::parse($row['posted_at']) : now(),
            ]);

            foreach ($row['lines'] ?? [] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $accountMap[$line['account_code'] ?? ''] ?? null,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null,
                    'subledger_type' => ! empty($line['subledger_key']) ? MerchantCustomer::class : null,
                    'subledger_id' => ! empty($line['subledger_key'])
                        ? ($customerMap[$line['subledger_key']] ?? null)
                        : null,
                ]);
            }

            $map[$row['entry_number']] = $entry->id;
        }

        return $map;
    }

    /**
     * @param  array<string, int>  $saleMap
     * @param  array<string, int>  $paymentMap
     * @return array{type: ?string, id: ?int}
     */
    protected function resolveReferenceId(?string $referenceKey, array $saleMap, array $paymentMap): array
    {
        if (! $referenceKey) {
            return ['type' => null, 'id' => null];
        }

        if (str_starts_with($referenceKey, 'pos_sale:')) {
            $saleNumber = str_replace('pos_sale:', '', $referenceKey);

            return ['type' => PosSale::class, 'id' => $saleMap[$saleNumber] ?? null];
        }

        if (str_starts_with($referenceKey, 'customer_payment:')) {
            return ['type' => MerchantCustomerPayment::class, 'id' => $paymentMap[$referenceKey] ?? null];
        }

        if (str_starts_with($referenceKey, 'payment:')) {
            return ['type' => MerchantCustomerPayment::class, 'id' => $paymentMap[$referenceKey] ?? null];
        }

        return ['type' => null, 'id' => null];
    }

    protected function customerKey(?string $name, ?string $phone): string
    {
        return 'customer:'.($phone ?: $name);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function productKey(array $row): string
    {
        return $row['sku'] ?: ($row['barcode'] ?: $row['name']);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function productKeyFromItem(array $item): string
    {
        return $item['product_sku'] ?: ($item['product_barcode'] ?: $item['product_name']);
    }

    protected function paymentExportKey(?string $name, ?string $phone, float $amount, Carbon $createdAt): string
    {
        return 'customer_payment:'.$this->customerKey($name, $phone).':'.$amount.':'.$createdAt->timestamp;
    }

    protected function importStatementShares(Team $team, array $shares, array $customerMap): array
    {
        $map = [];

        foreach ($shares as $row) {
            $customerKey = $this->customerKey($row['customer_name'] ?? '', $row['customer_phone'] ?? null);
            $customerId = $customerMap[$customerKey] ?? null;

            if (!$customerId) {
                continue;
            }

            $userId = $this->resolveUserByEmail($row['user_email'] ?? null);
            $sharedBy = $this->resolveUserByEmail($row['shared_by_email'] ?? null);
            $closedBy = $this->resolveUserByEmail($row['closed_by_email'] ?? null);

            $share = MerchantCustomerStatementShare::create([
                'uuid' => $row['uuid'],
                'team_id' => $team->id,
                'merchant_customer_id' => $customerId,
                'user_id' => $userId,
                'shared_by' => $sharedBy,
                'closed_by' => $closedBy,
                'is_active' => $row['is_active'] ?? true,
                'shared_at' => isset($row['shared_at']) ? Carbon::parse($row['shared_at']) : null,
                'closed_at' => isset($row['closed_at']) ? Carbon::parse($row['closed_at']) : null,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            $map[$row['uuid']] = $share->id;
        }

        return $map;
    }

    protected function importFinancialTransfers(
        Team $team,
        array $transfers,
        array $customerMap,
        array $shareMap,
        array $paymentAccountMap,
        array $paymentMap
    ): void {
        foreach ($transfers as $row) {
            $customerKey = $this->customerKey($row['customer_name'] ?? '', $row['customer_phone'] ?? null);
            $customerId = $customerMap[$customerKey] ?? null;

            if (!$customerId) {
                continue;
            }

            $shareId = isset($row['statement_share_uuid']) ? ($shareMap[$row['statement_share_uuid']] ?? null) : null;
            $submittedBy = $this->resolveUserByEmail($row['submitted_by_email'] ?? null) ?? User::first()?->id; // fallback
            $paymentAccountId = isset($row['payment_account_name']) ? ($paymentAccountMap[$row['payment_account_name']] ?? null) : null;
            $reviewedBy = $this->resolveUserByEmail($row['reviewed_by_email'] ?? null);

            $paymentId = null;
            if (!empty($row['merchant_customer_payment_key'])) {
                $paymentId = $paymentMap[$row['merchant_customer_payment_key']] ?? null;
            }

            MerchantCustomerFinancialTransfer::create([
                'team_id' => $team->id,
                'merchant_customer_id' => $customerId,
                'statement_share_id' => $shareId,
                'submitted_by' => $submittedBy,
                'merchant_payment_account_id' => $paymentAccountId,
                'payment_method' => $row['payment_method'] ?? 'cash',
                'purpose' => CustomerFinancialTransferPurpose::tryFrom($row['purpose'] ?? '') ?? $row['purpose'],
                'amount' => $row['amount'] ?? 0,
                'reference_number' => $row['reference_number'] ?? null,
                'notes' => $row['notes'] ?? null,
                'status' => CustomerFinancialTransferStatus::tryFrom($row['status'] ?? '') ?? $row['status'],
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => isset($row['reviewed_at']) ? Carbon::parse($row['reviewed_at']) : null,
                'rejection_reason' => $row['rejection_reason'] ?? null,
                'merchant_customer_payment_id' => $paymentId,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);
        }
    }

    protected function importSaleReturns(Team $team, array $returns, array $saleMap, array $productMap): array
    {
        $map = [];

        foreach ($returns as $row) {
            $saleId = isset($row['sale_number']) ? ($saleMap[$row['sale_number']] ?? null) : null;

            if (!$saleId) {
                continue;
            }

            $processedBy = $this->resolveUserByEmail($row['processed_by_email'] ?? null);

            $return = PosSaleReturn::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'pos_sale_id' => $saleId,
                'return_number' => $row['return_number'],
                'return_type' => ReturnType::tryFrom($row['return_type'] ?? '') ?? $row['return_type'],
                'refund_method' => RefundMethod::tryFrom($row['refund_method'] ?? '') ?? $row['refund_method'],
                'returned_amount' => $row['returned_amount'] ?? 0,
                'exchange_amount' => $row['exchange_amount'] ?? 0,
                'price_difference' => $row['price_difference'] ?? 0,
                'refunded_to_customer' => $row['refunded_to_customer'] ?? 0,
                'receivable_reduction_amount' => $row['receivable_reduction_amount'] ?? 0,
                'charged_to_customer' => $row['charged_to_customer'] ?? 0,
                'credit_note_amount' => $row['credit_note_amount'] ?? 0,
                'status' => $row['status'] ?? 'completed',
                'notes' => $row['notes'] ?? null,
                'processed_by' => $processedBy,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            foreach ($row['items'] ?? [] as $itemRow) {
                $productKey = $this->productKeyFromItem($itemRow);
                $productId = $productMap[$productKey] ?? null;

                // Resolve pos_sale_item_id under the original sale:
                $posSaleItem = PosSaleItem::where('pos_sale_id', $saleId)
                    ->whereHas('merchantProduct', function ($q) use ($itemRow) {
                        $q->where('sku', $itemRow['product_sku'])
                          ->orWhere('barcode', $itemRow['product_barcode'])
                          ->orWhere('name', $itemRow['product_name']);
                    })->first();

                if (!$posSaleItem) {
                    $posSaleItem = PosSaleItem::where('pos_sale_id', $saleId)
                        ->where('product_name', $itemRow['product_name'])
                        ->first();
                }

                PosSaleReturnItem::create([
                    'pos_sale_return_id' => $return->id,
                    'pos_sale_item_id' => $posSaleItem?->id,
                    'merchant_product_id' => $productId,
                    'product_name' => $itemRow['product_name'],
                    'quantity_returned' => $itemRow['quantity_returned'] ?? 0,
                    'unit_price' => $itemRow['unit_price'] ?? 0,
                    'total_price' => $itemRow['total_price'] ?? 0,
                    'unit_cost' => $itemRow['unit_cost'] ?? 0,
                    'return_reason' => $itemRow['return_reason'] ?? null,
                    'item_condition' => $itemRow['item_condition'] ?? null,
                ]);
            }

            foreach ($row['exchange_items'] ?? [] as $exRow) {
                $productKey = $this->productKeyFromItem([
                    'product_sku' => $exRow['product_sku'],
                    'product_barcode' => $exRow['product_barcode'],
                    'product_name' => $exRow['product_name'],
                ]);
                $productId = $productMap[$productKey] ?? null;

                PosExchangeItem::create([
                    'pos_sale_return_id' => $return->id,
                    'merchant_product_id' => $productId,
                    'product_name' => $exRow['product_name'],
                    'quantity' => $exRow['quantity'] ?? 0,
                    'unit_price' => $exRow['unit_price'] ?? 0,
                    'total_price' => $exRow['total_price'] ?? 0,
                    'unit_cost' => $exRow['unit_cost'] ?? 0,
                ]);
            }

            $map[$row['return_number']] = $return->id;
        }

        return $map;
    }

    protected function importInventoryCounts(Team $team, array $counts, array $entryMap, array $productMap): array
    {
        $map = [];

        foreach ($counts as $row) {
            $createdBy = $this->resolveUserByEmail($row['created_by_email'] ?? null);
            $approvedBy = $this->resolveUserByEmail($row['approved_by_email'] ?? null);
            $journalEntryId = isset($row['journal_entry_number']) ? ($entryMap[$row['journal_entry_number']] ?? null) : null;

            $count = InventoryCount::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'count_number' => $row['count_number'],
                'count_date' => isset($row['count_date']) ? Carbon::parse($row['count_date']) : now(),
                'fiscal_year' => $row['fiscal_year'] ?? now()->year,
                'status' => InventoryCountStatus::tryFrom($row['status'] ?? '') ?? $row['status'],
                'total_book_value' => $row['total_book_value'] ?? 0,
                'total_counted_value' => $row['total_counted_value'] ?? 0,
                'variance_value' => $row['variance_value'] ?? 0,
                'journal_entry_id' => $journalEntryId,
                'notes' => $row['notes'] ?? null,
                'created_by' => $createdBy,
                'approved_by' => $approvedBy,
                'approved_at' => isset($row['approved_at']) ? Carbon::parse($row['approved_at']) : null,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);

            foreach ($row['items'] ?? [] as $itemRow) {
                $productKey = $this->productKeyFromItem([
                    'product_sku' => $itemRow['product_sku'],
                    'product_barcode' => $itemRow['product_barcode'],
                    'product_name' => $itemRow['product_name'],
                ]);
                $productId = $productMap[$productKey] ?? null;

                if (!$productId) {
                    continue;
                }

                InventoryCountItem::create([
                    'inventory_count_id' => $count->id,
                    'merchant_product_id' => $productId,
                    'product_name' => $itemRow['product_name'],
                    'unit' => $itemRow['unit'] ?? null,
                    'book_quantity' => $itemRow['book_quantity'] ?? 0,
                    'counted_quantity' => $itemRow['counted_quantity'],
                    'variance_quantity' => $itemRow['variance_quantity'] ?? 0,
                    'unit_cost' => $itemRow['unit_cost'] ?? 0,
                    'book_value' => $itemRow['book_value'] ?? 0,
                    'counted_value' => $itemRow['counted_value'] ?? 0,
                    'variance_value' => $itemRow['variance_value'] ?? 0,
                    'notes' => $itemRow['notes'] ?? null,
                ]);
            }

            $map[$row['count_number']] = $count->id;
        }

        return $map;
    }

    protected function importFiscalYearClosings(Team $team, array $closings, array $entryMap): void
    {
        foreach ($closings as $row) {
            $closedBy = $this->resolveUserByEmail($row['closed_by_email'] ?? null);
            $journalEntryId = isset($row['journal_entry_number']) ? ($entryMap[$row['journal_entry_number']] ?? null) : null;

            FiscalYearClosing::create([
                'team_id' => $team->id,
                'fiscal_year' => $row['fiscal_year'],
                'closing_date' => isset($row['closing_date']) ? Carbon::parse($row['closing_date']) : now(),
                'status' => $row['status'] ?? 'draft',
                'total_revenue' => $row['total_revenue'] ?? 0,
                'total_expense' => $row['total_expense'] ?? 0,
                'net_income' => $row['net_income'] ?? 0,
                'retained_earnings_before' => $row['retained_earnings_before'] ?? 0,
                'retained_earnings_after' => $row['retained_earnings_after'] ?? 0,
                'journal_entry_id' => $journalEntryId,
                'notes' => $row['notes'] ?? null,
                'closed_by' => $closedBy,
                'posted_at' => isset($row['posted_at']) ? Carbon::parse($row['posted_at']) : null,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);
        }
    }

    protected function importStockMovements(
        Team $team,
        array $movements,
        array $productMap,
        array $entryMap,
        array $saleMap,
        array $returnMap,
        array $inventoryCountMap
    ): void {
        foreach ($movements as $row) {
            $productKey = $this->productKeyFromItem([
                'product_sku' => $row['product_sku'],
                'product_barcode' => $row['product_barcode'],
                'product_name' => $row['product_name'],
            ]);
            $productId = $productMap[$productKey] ?? null;

            if (!$productId) {
                continue;
            }

            $journalEntryId = isset($row['journal_entry_number']) ? ($entryMap[$row['journal_entry_number']] ?? null) : null;
            $createdBy = $this->resolveUserByEmail($row['created_by_email'] ?? null);

            $reference = $this->resolveStockMovementReference(
                $row['reference_key'] ?? '',
                $saleMap,
                $returnMap,
                $inventoryCountMap
            );

            StockMovement::withoutGlobalScopes()->create([
                'team_id' => $team->id,
                'merchant_product_id' => $productId,
                'movement_type' => StockMovementType::tryFrom($row['movement_type'] ?? '') ?? $row['movement_type'],
                'direction' => $row['direction'],
                'quantity' => $row['quantity'] ?? 0,
                'unit_cost' => $row['unit_cost'] ?? 0,
                'total_cost' => $row['total_cost'] ?? 0,
                'quantity_before' => $row['quantity_before'] ?? 0,
                'quantity_after' => $row['quantity_after'] ?? 0,
                'reference_type' => $reference['type'],
                'reference_id' => $reference['id'],
                'journal_entry_id' => $journalEntryId,
                'notes' => $row['notes'] ?? null,
                'created_by' => $createdBy,
                'created_at' => isset($row['created_at']) ? Carbon::parse($row['created_at']) : now(),
            ]);
        }
    }

    protected function resolveStockMovementReference(
        string $refKey,
        array $saleMap,
        array $returnMap,
        array $inventoryCountMap
    ): array {
        if (str_starts_with($refKey, 'pos_sale:')) {
            $saleNumber = substr($refKey, strlen('pos_sale:'));
            return ['type' => PosSale::class, 'id' => $saleMap[$saleNumber] ?? null];
        }
        if (str_starts_with($refKey, 'pos_sale_return:')) {
            $returnNumber = substr($refKey, strlen('pos_sale_return:'));
            return ['type' => PosSaleReturn::class, 'id' => $returnMap[$returnNumber] ?? null];
        }
        if (str_starts_with($refKey, 'inventory_count:')) {
            $countNumber = substr($refKey, strlen('inventory_count:'));
            return ['type' => InventoryCount::class, 'id' => $inventoryCountMap[$countNumber] ?? null];
        }
        return ['type' => null, 'id' => null];
    }

    protected function resolveUserByEmail(?string $email): ?int
    {
        if (!$email) {
            return null;
        }
        return User::where('email', $email)->first()?->id;
    }
}
