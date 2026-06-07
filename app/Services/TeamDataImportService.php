<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\JournalEntryStatus;
use App\Enums\MerchantPaymentAccountType;
use App\Enums\NormalBalance;
use App\Enums\SalePaymentType;
use App\Models\Account;
use App\Models\Distributor;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use App\Models\PosSaleItem;
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
            $this->importJournalEntries($team, $user, $data['journal_entries'] ?? [], $accountMap, $customerMap, $saleMap, $paymentMap);

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
     */
    protected function importJournalEntries(
        Team $team,
        User $user,
        array $entries,
        array $accountMap,
        array $customerMap,
        array $saleMap,
        array $paymentMap,
    ): void {
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
        }
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
}
