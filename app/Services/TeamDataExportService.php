<?php

namespace App\Services;

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
            'merchant_customer_statement_shares' => $this->exportStatementShares($team),
            'merchant_customer_financial_transfers' => $this->exportFinancialTransfers($team),
            'pos_sale_returns' => $this->exportSaleReturns($team),
            'inventory_counts' => $this->exportInventoryCounts($team),
            'fiscal_year_closings' => $this->exportFiscalYearClosings($team),
            'stock_movements' => $this->exportStockMovements($team),
        ];

        $data['signature'] = $this->generateSignature($data);

        return $data;
    }

    public function toFinancialExcelSheets(Team $team): array
    {
        $data = $this->exportTeamData($team);
        unset($data['signature']);

        return [
            ['title' => 'العملاء', 'headings' => ['الاسم', 'الهاتف', 'البريد', 'المديونية', 'الرصيد الفائض', 'نشط'], 'rows' => collect($data['merchant_customers'])->map(fn ($r) => [
                $r['name'], $r['phone'], $r['email'], $r['balance'], $r['credit_balance'], $r['is_active'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'المبيعات', 'headings' => ['رقم البيع', 'العميل', 'الإجمالي', 'المدفوع', 'الآجل', 'رصيد مخصوم', 'نوع الدفع', 'طريقة الدفع', 'الحالة', 'التاريخ'], 'rows' => collect($data['pos_sales'])->map(fn ($r) => [
                $r['sale_number'], $r['customer_name'], $r['total_amount'], $r['paid_amount'], $r['credit_amount'], $r['customer_credit_applied'], $r['payment_type'], $r['payment_method'], $r['status'], $r['created_at'],
            ])],
            ['title' => 'تفاصيل المبيعات', 'headings' => ['رقم البيع', 'المنتج', 'الكمية', 'السعر', 'الإجمالي'], 'rows' => collect($data['pos_sales'])->flatMap(fn ($sale) => collect($sale['items'])->map(fn ($item) => [
                $sale['sale_number'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['total'],
            ]))],
            ['title' => 'القيود اليومية', 'headings' => ['رقم القيد', 'التاريخ', 'الوصف', 'الحالة', 'مرجع', 'تاريخ الترحيل'], 'rows' => collect($data['journal_entries'])->map(fn ($r) => [
                $r['entry_number'], $r['entry_date'], $r['description'], $r['status'], $r['reference_key'], $r['posted_at'],
            ])],
            ['title' => 'بنود القيود', 'headings' => ['رقم القيد', 'رمز الحساب', 'مدين', 'دائن', 'الوصف', 'العميل الفرعي'], 'rows' => collect($data['journal_entries'])->flatMap(fn ($entry) => collect($entry['lines'])->map(fn ($line) => [
                $entry['entry_number'], $line['account_code'], $line['debit_amount'], $line['credit_amount'], $line['description'], $line['subledger_key'],
            ]))],
            ['title' => 'سدادات العملاء', 'headings' => ['العميل', 'المبلغ', 'سُدّد من المديونية', 'أُضيف للرصيد', 'طريقة الدفع', 'المرجع', 'التاريخ'], 'rows' => collect($data['merchant_customer_payments'])->map(fn ($r) => [
                $r['customer_name'], $r['amount'], $r['applied_to_balance'], $r['surplus_to_credit'], $r['payment_method'], $r['reference_number'], $r['created_at'],
            ])],
            ['title' => 'شجرة الحسابات', 'headings' => ['الرمز', 'الاسم', 'النوع', 'الرصيد الطبيعي', 'الحساب الأب', 'نشط', 'نظامي'], 'rows' => collect($data['accounts'])->map(fn ($r) => [
                $r['code'], $r['name'], $r['type'], $r['normal_balance'], $r['parent_code'], $r['is_active'] ? 'نعم' : 'لا', $r['is_system'] ? 'نعم' : 'لا',
            ])],
            ['title' => 'التحويلات المالية', 'headings' => ['العميل', 'رابط الكشف', 'مقدم بواسطة', 'الحساب المالي للتاجر', 'طريقة الدفع', 'الهدف', 'المبلغ', 'الرقم المرجعي', 'ملاحظات', 'الحالة', 'تمت المراجعة بواسطة', 'تاريخ المراجعة', 'سبب الرفض'], 'rows' => collect($data['merchant_customer_financial_transfers'])->map(fn ($r) => [
                $r['customer_name'], $r['statement_share_uuid'], $r['submitted_by_email'], $r['payment_account_name'], $r['payment_method'], $r['purpose'], $r['amount'], $r['reference_number'], $r['notes'], $r['status'], $r['reviewed_by_email'], $r['reviewed_at'], $r['rejection_reason']
            ])],
            ['title' => 'المرتجعات والاستبدال', 'headings' => ['رقم الفاتورة الأصلية', 'رقم المرتجع', 'نوع العملية', 'طريقة الاسترداد', 'المبلغ المرتجع', 'مبلغ الاستبدال', 'فرق السعر', 'المسترد للعميل', 'المطلوب من العميل', 'رصيد العمولة/السند الدائن', 'الحالة', 'ملاحظات', 'تاريخ المعالجة'], 'rows' => collect($data['pos_sale_returns'])->map(fn ($r) => [
                $r['sale_number'], $r['return_number'], $r['return_type'], $r['refund_method'], $r['returned_amount'], $r['exchange_amount'], $r['price_difference'], $r['refunded_to_customer'], $r['charged_to_customer'], $r['credit_note_amount'], $r['status'], $r['notes'], $r['created_at']
            ])],
            ['title' => 'تفاصيل المرتجع والاستبدال', 'headings' => ['رقم المرتجع', 'المنتج', 'الكمية المرجعة/المستبدلة', 'سعر الوحدة', 'السعر الإجمالي', 'التكلفة وقت العملية', 'السبب', 'حالة الصنف'], 'rows' => collect($data['pos_sale_returns'])->flatMap(fn ($ret) => collect($ret['items'])->map(fn ($item) => [
                $ret['return_number'], $item['product_name'], $item['quantity_returned'], $item['unit_price'], $item['total_price'], $item['unit_cost'], $item['return_reason'], $item['item_condition']
            ])->concat(collect($ret['exchange_items'])->map(fn ($item) => [
                $ret['return_number'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['total_price'], $item['unit_cost'], 'استبدال (مضاف للمخزون)', ''
            ])))],
            ['title' => 'الإغلاق السنوي', 'headings' => ['السنة المالية', 'تاريخ الإغلاق', 'الحالة', 'إجمالي الإيرادات', 'إجمالي المصاريف', 'صافي الدخل', 'الأرباح المحتجزة قبل', 'الأرباح المحتجزة بعد', 'رقم قيد اليومية', 'ملاحظات', 'أغلق بواسطة', 'تاريخ الترحيل'], 'rows' => collect($data['fiscal_year_closings'])->map(fn ($r) => [
                $r['fiscal_year'], $r['closing_date'], $r['status'], $r['total_revenue'], $r['total_expense'], $r['net_income'], $r['retained_earnings_before'], $r['retained_earnings_after'], $r['journal_entry_number'], $r['notes'], $r['closed_by_email'], $r['posted_at']
            ])],
        ];
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
            ['title' => 'التحويلات المالية', 'headings' => ['العميل', 'رابط الكشف', 'مقدم بواسطة', 'الحساب المالي للتاجر', 'طريقة الدفع', 'الهدف', 'المبلغ', 'الرقم المرجعي', 'ملاحظات', 'الحالة', 'تمت المراجعة بواسطة', 'تاريخ المراجعة', 'سبب الرفض'], 'rows' => collect($data['merchant_customer_financial_transfers'])->map(fn ($r) => [
                $r['customer_name'], $r['statement_share_uuid'], $r['submitted_by_email'], $r['payment_account_name'], $r['payment_method'], $r['purpose'], $r['amount'], $r['reference_number'], $r['notes'], $r['status'], $r['reviewed_by_email'], $r['reviewed_at'], $r['rejection_reason']
            ])],
            ['title' => 'المرتجعات والاستبدال', 'headings' => ['رقم الفاتورة الأصلية', 'رقم المرتجع', 'نوع العملية', 'طريقة الاسترداد', 'المبلغ المرتجع', 'مبلغ الاستبدال', 'فرق السعر', 'المسترد للعميل', 'المطلوب من العميل', 'رصيد العمولة/السند الدائن', 'الحالة', 'ملاحظات', 'تاريخ المعالجة'], 'rows' => collect($data['pos_sale_returns'])->map(fn ($r) => [
                $r['sale_number'], $r['return_number'], $r['return_type'], $r['refund_method'], $r['returned_amount'], $r['exchange_amount'], $r['price_difference'], $r['refunded_to_customer'], $r['charged_to_customer'], $r['credit_note_amount'], $r['status'], $r['notes'], $r['created_at']
            ])],
            ['title' => 'تفاصيل المرتجع والاستبدال', 'headings' => ['رقم المرتجع', 'المنتج', 'الكمية المرجعة/المستبدلة', 'سعر الوحدة', 'السعر الإجمالي', 'التكلفة وقت العملية', 'السبب', 'حالة الصنف'], 'rows' => collect($data['pos_sale_returns'])->flatMap(fn ($ret) => collect($ret['items'])->map(fn ($item) => [
                $ret['return_number'], $item['product_name'], $item['quantity_returned'], $item['unit_price'], $item['total_price'], $item['unit_cost'], $item['return_reason'], $item['item_condition']
            ])->concat(collect($ret['exchange_items'])->map(fn ($item) => [
                $ret['return_number'], $item['product_name'], $item['quantity'], $item['unit_price'], $item['total_price'], $item['unit_cost'], 'استبدال (مضاف للمخزون)', ''
            ])))],
            ['title' => 'حركات المخزون', 'headings' => ['المنتج', 'نوع الحركة', 'الاتجاه', 'الكمية', 'تكلفة الوحدة', 'التكلفة الإجمالية', 'الكمية قبل', 'الكمية بعد', 'رقم المرجع', 'رقم قيد اليومية', 'ملاحظات', 'تاريخ الحركة'], 'rows' => collect($data['stock_movements'])->map(fn ($r) => [
                $r['product_name'], $r['movement_type'], $r['direction'], $r['quantity'], $r['unit_cost'], $r['total_cost'], $r['quantity_before'], $r['quantity_after'], $r['reference_key'], $r['journal_entry_number'], $r['notes'], $r['created_at']
            ])],
            ['title' => 'الجرد السنوي', 'headings' => ['رقم الجرد', 'تاريخ الجرد', 'السنة المالية', 'الحالة', 'إجمالي القيمة الدفترية', 'إجمالي القيمة الفعلية', 'قيمة الفارق', 'رقم قيد اليومية', 'ملاحظات', 'تم الجرد بواسطة', 'تم الاعتماد بواسطة', 'تاريخ الاعتماد'], 'rows' => collect($data['inventory_counts'])->map(fn ($r) => [
                $r['count_number'], $r['count_date'], $r['fiscal_year'], $r['status'], $r['total_book_value'], $r['total_counted_value'], $r['variance_value'], $r['journal_entry_number'], $r['notes'], $r['created_by_email'], $r['approved_by_email'], $r['approved_at']
            ])],
            ['title' => 'تفاصيل الجرد السنوي', 'headings' => ['رقم الجرد', 'المنتج', 'الكمية الدفترية', 'الكمية الفعلية', 'الفارق في الكمية', 'تكلفة الوحدة', 'القيمة الدفترية', 'القيمة الفعلية', 'القيمة الفارق', 'ملاحظات'], 'rows' => collect($data['inventory_counts'])->flatMap(fn ($cnt) => collect($cnt['items'])->map(fn ($item) => [
                $cnt['count_number'], $item['product_name'], $item['book_quantity'], $item['counted_quantity'], $item['variance_quantity'], $item['unit_cost'], $item['book_value'], $item['counted_value'], $item['variance_value'], $item['notes']
            ]))],
            ['title' => 'الإغلاق السنوي', 'headings' => ['السنة المالية', 'تاريخ الإغلاق', 'الحالة', 'إجمالي الإيرادات', 'إجمالي المصاريف', 'صافي الدخل', 'الأرباح المحتجزة قبل', 'الأرباح المحتجزة بعد', 'رقم قيد اليومية', 'ملاحظات', 'أغلق بواسطة', 'تاريخ الترحيل'], 'rows' => collect($data['fiscal_year_closings'])->map(fn ($r) => [
                $r['fiscal_year'], $r['closing_date'], $r['status'], $r['total_revenue'], $r['total_expense'], $r['net_income'], $r['retained_earnings_before'], $r['retained_earnings_after'], $r['journal_entry_number'], $r['notes'], $r['closed_by_email'], $r['posted_at']
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

    protected function exportStatementShares(Team $team): array
    {
        return MerchantCustomerStatementShare::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['merchantCustomer', 'user', 'sharedByUser', 'closedByUser'])
            ->get()
            ->map(fn (MerchantCustomerStatementShare $s) => [
                'uuid' => $s->uuid,
                'customer_name' => $s->merchantCustomer?->name,
                'customer_phone' => $s->merchantCustomer?->phone,
                'user_email' => $s->user?->email,
                'shared_by_email' => $s->sharedByUser?->email,
                'closed_by_email' => $s->closedByUser?->email,
                'is_active' => $s->is_active,
                'shared_at' => $s->shared_at?->toISOString(),
                'closed_at' => $s->closed_at?->toISOString(),
                'created_at' => $s->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportFinancialTransfers(Team $team): array
    {
        return MerchantCustomerFinancialTransfer::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['merchantCustomer', 'statementShare', 'submitter', 'paymentAccount', 'reviewer', 'merchantCustomerPayment'])
            ->get()
            ->map(function (MerchantCustomerFinancialTransfer $t) {
                $paymentExportKey = null;
                if ($t->merchantCustomerPayment) {
                    $paymentExportKey = 'customer_payment:' . $this->customerKey(
                        $t->merchantCustomerPayment->merchantCustomer?->name,
                        $t->merchantCustomerPayment->merchantCustomer?->phone
                    ) . ':' . (float)$t->merchantCustomerPayment->amount . ':' . $t->merchantCustomerPayment->created_at?->timestamp;
                }

                return [
                    'customer_name' => $t->merchantCustomer?->name,
                    'customer_phone' => $t->merchantCustomer?->phone,
                    'statement_share_uuid' => $t->statementShare?->uuid,
                    'submitted_by_email' => $t->submitter?->email,
                    'payment_account_name' => $t->paymentAccount?->name,
                    'payment_method' => $t->payment_method,
                    'purpose' => $t->purpose?->value ?? $t->purpose,
                    'amount' => (float) $t->amount,
                    'reference_number' => $t->reference_number,
                    'notes' => $t->notes,
                    'status' => $t->status?->value ?? $t->status,
                    'reviewed_by_email' => $t->reviewer?->email,
                    'reviewed_at' => $t->reviewed_at?->toISOString(),
                    'rejection_reason' => $t->rejection_reason,
                    'merchant_customer_payment_key' => $paymentExportKey,
                    'created_at' => $t->created_at?->toISOString(),
                ];
            })
            ->values()
            ->all();
    }

    protected function exportSaleReturns(Team $team): array
    {
        return PosSaleReturn::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['originalSale', 'processor', 'returnItems.product', 'exchangeItems.product'])
            ->get()
            ->map(fn (PosSaleReturn $r) => [
                'sale_number' => $r->originalSale?->sale_number,
                'return_number' => $r->return_number,
                'return_type' => $r->return_type?->value ?? $r->return_type,
                'refund_method' => $r->refund_method?->value ?? $r->refund_method,
                'returned_amount' => (float) $r->returned_amount,
                'exchange_amount' => (float) $r->exchange_amount,
                'price_difference' => (float) $r->price_difference,
                'refunded_to_customer' => (float) $r->refunded_to_customer,
                'receivable_reduction_amount' => (float) $r->receivable_reduction_amount,
                'charged_to_customer' => (float) $r->charged_to_customer,
                'credit_note_amount' => (float) $r->credit_note_amount,
                'status' => $r->status,
                'notes' => $r->notes,
                'processed_by_email' => $r->processor?->email,
                'created_at' => $r->created_at?->toISOString(),
                'items' => $r->returnItems->map(fn (PosSaleReturnItem $item) => [
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product?->sku,
                    'product_barcode' => $item->product?->barcode,
                    'quantity_returned' => (float) $item->quantity_returned,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                    'unit_cost' => (float) $item->unit_cost,
                    'return_reason' => $item->return_reason,
                    'item_condition' => $item->item_condition,
                ])->values()->all(),
                'exchange_items' => $r->exchangeItems->map(fn (PosExchangeItem $item) => [
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product?->sku,
                    'product_barcode' => $item->product?->barcode,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                    'unit_cost' => (float) $item->unit_cost,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    protected function exportInventoryCounts(Team $team): array
    {
        return InventoryCount::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['journalEntry', 'creator', 'approver', 'items.product'])
            ->get()
            ->map(fn (InventoryCount $c) => [
                'count_number' => $c->count_number,
                'count_date' => $c->count_date?->toDateString(),
                'fiscal_year' => $c->fiscal_year,
                'status' => $c->status?->value ?? $c->status,
                'total_book_value' => (float) $c->total_book_value,
                'total_counted_value' => (float) $c->total_counted_value,
                'variance_value' => (float) $c->variance_value,
                'journal_entry_number' => $c->journalEntry?->entry_number,
                'notes' => $c->notes,
                'created_by_email' => $c->creator?->email,
                'approved_by_email' => $c->approver?->email,
                'approved_at' => $c->approved_at?->toISOString(),
                'created_at' => $c->created_at?->toISOString(),
                'items' => $c->items->map(fn (InventoryCountItem $item) => [
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product?->sku,
                    'product_barcode' => $item->product?->barcode,
                    'unit' => $item->unit,
                    'book_quantity' => (float) $item->book_quantity,
                    'counted_quantity' => $item->counted_quantity !== null ? (float) $item->counted_quantity : null,
                    'variance_quantity' => (float) $item->variance_quantity,
                    'unit_cost' => (float) $item->unit_cost,
                    'book_value' => (float) $item->book_value,
                    'counted_value' => (float) $item->counted_value,
                    'variance_value' => (float) $item->variance_value,
                    'notes' => $item->notes,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    protected function exportFiscalYearClosings(Team $team): array
    {
        return FiscalYearClosing::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['journalEntry', 'closedBy'])
            ->get()
            ->map(fn (FiscalYearClosing $c) => [
                'fiscal_year' => $c->fiscal_year,
                'closing_date' => $c->closing_date?->toDateString(),
                'status' => $c->status,
                'total_revenue' => (float) $c->total_revenue,
                'total_expense' => (float) $c->total_expense,
                'net_income' => (float) $c->net_income,
                'retained_earnings_before' => (float) $c->retained_earnings_before,
                'retained_earnings_after' => (float) $c->retained_earnings_after,
                'journal_entry_number' => $c->journalEntry?->entry_number,
                'notes' => $c->notes,
                'closed_by_email' => $c->closedBy?->email,
                'posted_at' => $c->posted_at?->toISOString(),
                'created_at' => $c->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function exportStockMovements(Team $team): array
    {
        $saleKeys = PosSale::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('sale_number', 'id');

        $returnKeys = PosSaleReturn::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('return_number', 'id');

        $countKeys = InventoryCount::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('count_number', 'id');

        return StockMovement::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->with(['product', 'journalEntry', 'creator'])
            ->get()
            ->map(fn (StockMovement $m) => [
                'product_name' => $m->product?->name,
                'product_sku' => $m->product?->sku,
                'product_barcode' => $m->product?->barcode,
                'movement_type' => $m->movement_type?->value ?? $m->movement_type,
                'direction' => $m->direction,
                'quantity' => (float) $m->quantity,
                'unit_cost' => (float) $m->unit_cost,
                'total_cost' => (float) $m->total_cost,
                'quantity_before' => (float) $m->quantity_before,
                'quantity_after' => (float) $m->quantity_after,
                'reference_key' => $this->resolveStockMovementReferenceKey($m, $saleKeys, $returnKeys, $countKeys),
                'journal_entry_number' => $m->journalEntry?->entry_number,
                'notes' => $m->notes,
                'created_by_email' => $m->creator?->email,
                'created_at' => $m->created_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    protected function resolveStockMovementReferenceKey(StockMovement $movement, Collection $saleKeys, Collection $returnKeys, Collection $countKeys): ?string
    {
        if (! $movement->reference_type || ! $movement->reference_id) {
            return null;
        }

        return match ($movement->reference_type) {
            PosSale::class => 'pos_sale:'.($saleKeys[$movement->reference_id] ?? $movement->reference_id),
            PosSaleReturn::class => 'pos_sale_return:'.($returnKeys[$movement->reference_id] ?? $movement->reference_id),
            InventoryCount::class => 'inventory_count:'.($countKeys[$movement->reference_id] ?? $movement->reference_id),
            default => class_basename($movement->reference_type).':'.$movement->reference_id,
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
