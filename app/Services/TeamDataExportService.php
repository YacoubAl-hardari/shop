<?php

namespace App\Services;

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
use Illuminate\Support\Collection;

class TeamDataExportService
{
    public function exportTeamData(Team $team): array
    {
        $data = [
            'export_date' => now()->toISOString(),
            'export_version' => '2.0',
            'export_type' => 'merchant_business',
            'team_id' => $team->id,
            'team' => [
                'name' => $team->name,
                'slug' => $team->slug,
                'description' => $team->description,
                'domain' => $team->domain,
                'is_active' => $team->is_active,
                'created_at' => $team->created_at?->toISOString(),
            ],
            'suppliers' => $this->exportSuppliers($team),
            'distributors' => $this->exportDistributors($team),
            'merchant_payment_accounts' => $this->exportPaymentAccounts($team),
            'merchant_customers' => $this->exportCustomers($team),
            'merchant_products' => $this->exportProducts($team),
            'accounts' => $this->exportAccounts($team),
            'pos_sales' => $this->exportPosSales($team),
            'merchant_customer_payments' => $this->exportCustomerPayments($team),
            'journal_entries' => $this->exportJournalEntries($team),
        ];

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    public function toExcelSheets(Team $team): array
    {
        $data = $this->exportTeamData($team);
        unset($data['signature']);

        return [
            ['title' => 'معلومات الفرع', 'headings' => ['الاسم', 'الرمز', 'الوصف', 'النطاق', 'نشط', 'تاريخ الإنشاء'], 'rows' => collect([[
                $data['team']['name'],
                $data['team']['slug'],
                $data['team']['description'],
                $data['team']['domain'],
                ($data['team']['is_active'] ?? true) ? 'نعم' : 'لا',
                $data['team']['created_at'],
            ]])],
            ['title' => 'الموردون', 'headings' => ['الاسم', 'الهاتف', 'البريد', 'الرقم الضريبي', 'الرصيد', 'نشط'], 'rows' => collect($data['suppliers'])->map(fn ($r) => [
                $r['name'], $r['phone'], $r['email'], $r['tax_number'], $r['balance'], $r['is_active'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'الموزعون', 'headings' => ['المورد', 'الاسم', 'الهاتف', 'معلومات التواصل', 'نشط'], 'rows' => collect($data['distributors'])->map(fn ($r) => [
                $r['supplier_name'], $r['name'], $r['phone'], $r['contact_info'], $r['is_active'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'المنتجات', 'headings' => ['الاسم', 'SKU', 'الباركود', 'السعر', 'التكلفة', 'المخزون', 'الوحدة', 'المورد', 'الموزع', 'نشط'], 'rows' => collect($data['merchant_products'])->map(fn ($r) => [
                $r['name'], $r['sku'], $r['barcode'], $r['price'], $r['cost'], $r['stock_quantity'], $r['unit'], $r['supplier_name'], $r['distributor_name'], $r['is_active'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'العملاء', 'headings' => ['الاسم', 'الهاتف', 'البريد', 'المديونية', 'الرصيد الفائض', 'نشط'], 'rows' => collect($data['merchant_customers'])->map(fn ($r) => [
                $r['name'], $r['phone'], $r['email'], $r['balance'], $r['credit_balance'], $r['is_active'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'وسائل الدفع', 'headings' => ['النوع', 'الاسم', 'رقم الحساب', 'نشط', 'افتراضي'], 'rows' => collect($data['merchant_payment_accounts'])->map(fn ($r) => [
                $r['type'], $r['name'], $r['account_number'], $r['is_active'] ? 'نعم' : 'لا', $r['is_default'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'المبيعات', 'headings' => ['رقم البيع', 'العميل', 'الإجمالي', 'المدفوع', 'الآجل', 'رصيد مخصوم', 'نوع الدفع', 'طريقة الدفع', 'الحالة', 'التاريخ'], 'rows' => collect($data['pos_sales'])->map(fn ($r) => [
                $r['sale_number'], $r['customer_name'], $r['total_amount'], $r['paid_amount'], $r['credit_amount'], $r['customer_credit_applied'], $r['payment_type'], $r['payment_method'], $r['status'], $r['created_at'],
            ])],
            ['title' => 'تفاصيل المبيعات', 'headings' => ['رقم البيع', 'المنتج', 'الكمية', 'السعر', 'الإجمالي'], 'rows' => collect($data['pos_sales'])->flatMap(fn ($sale) => collect($sale['items'])->map(fn ($item) => [
                $sale['sale_number'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['total'],
            ]))],
            ['title' => 'شجرة الحسابات', 'headings' => ['الرمز', 'الاسم', 'النوع', 'الرصيد الطبيعي', 'الحساب الأب', 'نشط', 'نظامي'], 'rows' => collect($data['accounts'])->map(fn ($r) => [
                $r['code'], $r['name'], $r['type'], $r['normal_balance'], $r['parent_code'], $r['is_active'] ? 'نعم' : 'لا', $r['is_system'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'القيود اليومية', 'headings' => ['رقم القيد', 'التاريخ', 'الوصف', 'الحالة', 'مرجع', 'تاريخ الترحيل'], 'rows' => collect($data['journal_entries'])->map(fn ($r) => [
                $r['entry_number'], $r['entry_date'], $r['description'], $r['status'], $r['reference_key'], $r['posted_at'],
            ])],
            ['title' => 'بنود القيود', 'headings' => ['رقم القيد', 'رمز الحساب', 'مدين', 'دائن', 'الوصف', 'العميل الفرعي'], 'rows' => collect($data['journal_entries'])->flatMap(fn ($entry) => collect($entry['lines'])->map(fn ($line) => [
                $entry['entry_number'], $line['account_code'], $line['debit_amount'], $line['credit_amount'], $line['description'], $line['subledger_key'],
            ]))],
            ['title' => 'سدادات العملاء', 'headings' => ['العميل', 'المبلغ', 'سُدّد من المديونية', 'أُضيف للرصيد', 'طريقة الدفع', 'المرجع', 'التاريخ'], 'rows' => collect($data['merchant_customer_payments'])->map(fn ($r) => [
                $r['customer_name'], $r['amount'], $r['applied_to_balance'], $r['surplus_to_credit'], $r['payment_method'], $r['reference_number'], $r['created_at'],
            ])],
        ];
    }

    protected function exportSuppliers(Team $team): array
    {
        return Supplier::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->get()
            ->map(fn (Supplier $s) => [
                'name' => $s->name,
                'phone' => $s->phone,
                'email' => $s->email,
                'tax_number' => $s->tax_number,
                'balance' => (float) $s->balance,
                'is_active' => $s->is_active,
                'created_at' => $s->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportDistributors(Team $team): array
    {
        return Distributor::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with('supplier')
            ->get()
            ->map(fn (Distributor $d) => [
                'supplier_name' => $d->supplier?->name,
                'name' => $d->name,
                'phone' => $d->phone,
                'contact_info' => $d->contact_info,
                'is_active' => $d->is_active,
                'created_at' => $d->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportPaymentAccounts(Team $team): array
    {
        return MerchantPaymentAccount::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->get()
            ->map(fn (MerchantPaymentAccount $a) => [
                'type' => $a->type?->value ?? $a->type,
                'name' => $a->name,
                'account_number' => $a->account_number,
                'is_active' => $a->is_active,
                'is_default' => $a->is_default,
                'created_at' => $a->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportCustomers(Team $team): array
    {
        return MerchantCustomer::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->get()
            ->map(fn (MerchantCustomer $c) => [
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'balance' => (float) $c->balance,
                'credit_balance' => (float) $c->credit_balance,
                'is_active' => $c->is_active,
                'created_at' => $c->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportProducts(Team $team): array
    {
        return MerchantProduct::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['supplier', 'distributor'])
            ->get()
            ->map(fn (MerchantProduct $p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'price' => (float) $p->price,
                'cost' => (float) $p->cost,
                'stock_quantity' => (float) $p->stock_quantity,
                'unit' => $p->unit,
                'supplier_name' => $p->supplier?->name,
                'distributor_name' => $p->distributor?->name,
                'is_active' => $p->is_active,
                'description' => $p->description,
                'created_at' => $p->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportAccounts(Team $team): array
    {
        $accounts = Account::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with('parent')
            ->orderBy('_lft')
            ->get();

        return $accounts->map(fn (Account $a) => [
            'code' => $a->code,
            'name' => $a->name,
            'type' => $a->type?->value ?? $a->type,
            'normal_balance' => $a->normal_balance?->value ?? $a->normal_balance,
            'parent_code' => $a->parent?->code,
            'is_system' => $a->is_system,
            'is_active' => $a->is_active,
            'description' => $a->description,
        ])->values()->all();
    }

    protected function exportPosSales(Team $team): array
    {
        return PosSale::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['items.merchantProduct', 'merchantCustomer', 'paymentAccount', 'seller'])
            ->get()
            ->map(fn (PosSale $sale) => [
                'sale_number' => $sale->sale_number,
                'customer_name' => $sale->merchantCustomer?->name,
                'customer_phone' => $sale->merchantCustomer?->phone,
                'total_amount' => (float) $sale->total_amount,
                'paid_amount' => (float) $sale->paid_amount,
                'credit_amount' => (float) $sale->credit_amount,
                'customer_credit_applied' => (float) $sale->customer_credit_applied,
                'payment_type' => $sale->payment_type?->value ?? $sale->payment_type,
                'payment_method' => $sale->payment_method,
                'payment_account_name' => $sale->paymentAccount?->name,
                'payment_reference' => $sale->payment_reference,
                'status' => $sale->status,
                'notes' => $sale->notes,
                'sold_by_email' => $sale->seller?->email,
                'created_at' => $sale->created_at?->toISOString(),
                'items' => $sale->items->map(fn (PosSaleItem $item) => [
                    'product_name' => $item->product_name,
                    'product_sku' => $item->merchantProduct?->sku,
                    'product_barcode' => $item->merchantProduct?->barcode,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    protected function exportCustomerPayments(Team $team): array
    {
        return MerchantCustomerPayment::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['merchantCustomer', 'paymentAccount', 'receiver'])
            ->get()
            ->map(fn (MerchantCustomerPayment $p) => [
                'customer_name' => $p->merchantCustomer?->name,
                'customer_phone' => $p->merchantCustomer?->phone,
                'payment_account_name' => $p->paymentAccount?->name,
                'payment_method' => $p->payment_method,
                'amount' => (float) $p->amount,
                'applied_to_balance' => (float) $p->applied_to_balance,
                'surplus_to_credit' => (float) $p->surplus_to_credit,
                'reference_number' => $p->reference_number,
                'notes' => $p->notes,
                'received_by_email' => $p->receiver?->email,
                'created_at' => $p->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportJournalEntries(Team $team): array
    {
        $customerKeys = MerchantCustomer::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->get()
            ->mapWithKeys(fn (MerchantCustomer $c) => [
                $c->id => $this->customerKey($c->name, $c->phone),
            ]);

        $saleKeys = PosSale::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('sale_number', 'id');

        $paymentKeys = MerchantCustomerPayment::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with('merchantCustomer')
            ->get()
            ->mapWithKeys(fn (MerchantCustomerPayment $p) => [
                $p->id => 'customer_payment:'.$this->customerKey(
                    $p->merchantCustomer?->name,
                    $p->merchantCustomer?->phone,
                ).':'.(float) $p->amount.':'.$p->created_at?->timestamp,
            ]);

        $accountCodes = Account::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('code', 'id');

        return JournalEntry::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with('lines.account')
            ->get()
            ->map(function (JournalEntry $entry) use ($customerKeys, $saleKeys, $paymentKeys, $accountCodes) {
                return [
                    'entry_number' => $entry->entry_number,
                    'entry_date' => $entry->entry_date?->toDateString(),
                    'description' => $entry->description,
                    'status' => $entry->status?->value ?? $entry->status,
                    'reference_type' => $entry->reference_type,
                    'reference_key' => $this->resolveReferenceKey($entry, $saleKeys, $paymentKeys),
                    'posted_at' => $entry->posted_at?->toISOString(),
                    'created_by_email' => $entry->creator?->email,
                    'lines' => $entry->lines->map(function (JournalLine $line) use ($accountCodes, $customerKeys) {
                        return [
                            'account_code' => $accountCodes[$line->account_id] ?? $line->account?->code,
                            'debit_amount' => (float) $line->debit_amount,
                            'credit_amount' => (float) $line->credit_amount,
                            'description' => $line->description,
                            'subledger_type' => $line->subledger_type,
                            'subledger_key' => $line->subledger_id && $line->subledger_type === MerchantCustomer::class
                                ? ($customerKeys[$line->subledger_id] ?? null)
                                : null,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    protected function resolveReferenceKey(JournalEntry $entry, Collection $saleKeys, Collection $paymentKeys): ?string
    {
        if (! $entry->reference_type || ! $entry->reference_id) {
            return null;
        }

        return match ($entry->reference_type) {
            PosSale::class => 'pos_sale:'.($saleKeys[$entry->reference_id] ?? $entry->reference_id),
            MerchantCustomerPayment::class => 'customer_payment:'.($paymentKeys[$entry->reference_id] ?? $entry->reference_id),
            default => class_basename($entry->reference_type).':'.$entry->reference_id,
        };
    }

    protected function customerKey(?string $name, ?string $phone): string
    {
        return 'customer:'.($phone ?: $name);
    }

    protected function generateSignature(array $data): string
    {
        $dataString = json_encode($data, JSON_UNESCAPED_UNICODE);

        return hash_hmac('sha256', $dataString, config('app.key'));
    }
}
