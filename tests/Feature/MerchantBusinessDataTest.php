<?php

use App\Models\User;
use App\Models\Team;
use App\Models\Supplier;
use App\Models\Distributor;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantCustomer;
use App\Models\MerchantProduct;
use App\Models\Account;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\MerchantCustomerPayment;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MerchantCustomerStatementShare;
use App\Models\MerchantCustomerFinancialTransfer;
use App\Models\PosSaleReturn;
use App\Models\PosSaleReturnItem;
use App\Models\PosExchangeItem;
use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Models\FiscalYearClosing;
use App\Models\StockMovement;
use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Enums\JournalEntryStatus;
use App\Enums\SalePaymentType;
use App\Enums\StockMovementType;
use App\Enums\ReturnType;
use App\Enums\RefundMethod;
use App\Enums\InventoryCountStatus;
use App\Enums\CustomerFinancialTransferStatus;
use App\Enums\CustomerFinancialTransferPurpose;
use App\Services\TeamDataExportService;
use App\Services\TeamDataImportService;
use App\Services\TeamDataDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('can export, delete, and import all merchant business data successfully', function () {
    // 1. Setup
    $user = User::factory()->create([
        'email' => 'merchant@example.com',
    ]);
    
    $team = Team::create([
        'name' => 'Test Merchant',
        'slug' => 'test-merchant',
    ]);
    $team->members()->attach($user, ['role' => 'owner']);

    // Create supplier
    $supplier = Supplier::create([
        'team_id' => $team->id,
        'name' => 'Supplier A',
        'phone' => '12345',
        'email' => 'supa@example.com',
        'tax_number' => 'TAX111',
        'balance' => 1000.00,
        'is_active' => true,
    ]);

    // Create distributor
    $distributor = Distributor::create([
        'team_id' => $team->id,
        'supplier_id' => $supplier->id,
        'name' => 'Distributor A',
        'phone' => '67890',
        'contact_info' => 'contact info',
        'is_active' => true,
    ]);

    // Create payment account
    $payAccount = MerchantPaymentAccount::create([
        'team_id' => $team->id,
        'type' => 'cash',
        'name' => 'Cash Account',
        'account_number' => 'CASH-01',
        'is_active' => true,
        'is_default' => true,
    ]);

    // Create customer
    $customer = MerchantCustomer::create([
        'team_id' => $team->id,
        'name' => 'Customer A',
        'phone' => '99999',
        'email' => 'customer@example.com',
        'balance' => 500.00,
        'credit_balance' => 0.00,
        'is_active' => true,
    ]);

    // Create product
    $product = MerchantProduct::create([
        'team_id' => $team->id,
        'supplier_id' => $supplier->id,
        'distributor_id' => $distributor->id,
        'name' => 'Product A',
        'sku' => 'SKU-001',
        'barcode' => 'BAR-001',
        'price' => 100.00,
        'cost' => 60.00,
        'stock_quantity' => 50.00,
        'unit' => 'piece',
        'is_active' => true,
        'description' => 'Great product',
    ]);

    // Create chart of accounts (Account root)
    $accountRoot = Account::create([
        'team_id' => $team->id,
        'code' => '1000',
        'name' => 'Assets',
        'type' => AccountType::ASSET,
        'normal_balance' => NormalBalance::DEBIT,
        'is_system' => true,
        'is_active' => true,
        'description' => 'Assets Account',
    ]);

    $accountChild = Account::create([
        'team_id' => $team->id,
        'code' => '1001',
        'name' => 'Cash',
        'type' => AccountType::ASSET,
        'normal_balance' => NormalBalance::DEBIT,
        'is_system' => true,
        'is_active' => true,
        'description' => 'Cash Account',
    ], $accountRoot);

    // Create POS sale
    $sale = PosSale::create([
        'team_id' => $team->id,
        'sale_number' => 'SALE-001',
        'merchant_customer_id' => $customer->id,
        'total_amount' => 100.00,
        'paid_amount' => 100.00,
        'credit_amount' => 0.00,
        'customer_credit_applied' => 0.00,
        'payment_type' => SalePaymentType::CASH,
        'payment_method' => 'cash',
        'merchant_payment_account_id' => $payAccount->id,
        'payment_reference' => 'REF-CASH',
        'status' => 'completed',
        'notes' => 'cash sale',
        'sold_by' => $user->id,
    ]);

    $saleItem = PosSaleItem::create([
        'pos_sale_id' => $sale->id,
        'merchant_product_id' => $product->id,
        'product_name' => 'Product A',
        'quantity' => 1.00,
        'unit_price' => 100.00,
        'total' => 100.00,
    ]);

    // Create customer payment
    $payment = MerchantCustomerPayment::create([
        'team_id' => $team->id,
        'merchant_customer_id' => $customer->id,
        'merchant_payment_account_id' => $payAccount->id,
        'payment_method' => 'cash',
        'amount' => 50.00,
        'applied_to_balance' => 50.00,
        'surplus_to_credit' => 0.00,
        'reference_number' => 'PAY-01',
        'notes' => 'on account',
        'received_by' => $user->id,
    ]);

    // Create journal entry
    $entry = JournalEntry::create([
        'team_id' => $team->id,
        'entry_number' => 'JV-001',
        'entry_date' => now()->toDateString(),
        'description' => 'Pos sale journal entry',
        'status' => JournalEntryStatus::POSTED,
        'reference_type' => PosSale::class,
        'reference_id' => $sale->id,
        'created_by' => $user->id,
        'posted_at' => now(),
    ]);

    $line1 = JournalLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $accountChild->id,
        'debit_amount' => 100.00,
        'credit_amount' => 0.00,
        'description' => 'debit cash',
    ]);

    // Create statement share
    $share = MerchantCustomerStatementShare::create([
        'uuid' => (string) Str::uuid(),
        'team_id' => $team->id,
        'merchant_customer_id' => $customer->id,
        'user_id' => $user->id,
        'shared_by' => $user->id,
        'closed_by' => null,
        'is_active' => true,
        'shared_at' => now(),
    ]);

    // Create financial transfer
    $transfer = MerchantCustomerFinancialTransfer::create([
        'team_id' => $team->id,
        'merchant_customer_id' => $customer->id,
        'statement_share_id' => $share->id,
        'submitted_by' => $user->id,
        'merchant_payment_account_id' => $payAccount->id,
        'payment_method' => 'cash',
        'purpose' => CustomerFinancialTransferPurpose::SETTLEMENT,
        'amount' => 50.00,
        'reference_number' => 'TRANS-01',
        'notes' => 'statement payment transfer',
        'status' => CustomerFinancialTransferStatus::PENDING,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'rejection_reason' => null,
        'merchant_customer_payment_id' => $payment->id,
    ]);

    // Create POS sale return
    $saleReturn = PosSaleReturn::create([
        'team_id' => $team->id,
        'pos_sale_id' => $sale->id,
        'return_number' => 'RET-001',
        'return_type' => ReturnType::RETURN,
        'refund_method' => RefundMethod::CASH,
        'returned_amount' => 100.00,
        'exchange_amount' => 0.00,
        'price_difference' => 0.00,
        'refunded_to_customer' => 100.00,
        'receivable_reduction_amount' => 0.00,
        'charged_to_customer' => 0.00,
        'credit_note_amount' => 0.00,
        'status' => 'completed',
        'notes' => 'returned product',
        'processed_by' => $user->id,
    ]);

    $returnItem = PosSaleReturnItem::create([
        'pos_sale_return_id' => $saleReturn->id,
        'pos_sale_item_id' => $saleItem->id,
        'merchant_product_id' => $product->id,
        'product_name' => 'Product A',
        'quantity_returned' => 1.00,
        'unit_price' => 100.00,
        'total_price' => 100.00,
        'unit_cost' => 60.00,
        'return_reason' => 'defective',
        'item_condition' => 'resellable',
    ]);

    // Create inventory count
    $invCount = InventoryCount::create([
        'team_id' => $team->id,
        'count_number' => 'COUNT-01',
        'count_date' => now()->toDateString(),
        'fiscal_year' => now()->year,
        'status' => InventoryCountStatus::DRAFT,
        'total_book_value' => 3000.00,
        'total_counted_value' => 3000.00,
        'variance_value' => 0.00,
        'journal_entry_id' => $entry->id,
        'notes' => 'yearly stocktake',
        'created_by' => $user->id,
    ]);

    $invCountItem = InventoryCountItem::create([
        'inventory_count_id' => $invCount->id,
        'merchant_product_id' => $product->id,
        'product_name' => 'Product A',
        'unit' => 'piece',
        'book_quantity' => 50.00,
        'counted_quantity' => 50.00,
        'variance_quantity' => 0.00,
        'unit_cost' => 60.00,
        'book_value' => 3000.00,
        'counted_value' => 3000.00,
        'variance_value' => 0.00,
        'notes' => 'matching stock',
    ]);

    // Create fiscal year closing
    $closing = FiscalYearClosing::create([
        'team_id' => $team->id,
        'fiscal_year' => now()->year,
        'closing_date' => now()->toDateString(),
        'status' => 'draft',
        'total_revenue' => 10000.00,
        'total_expense' => 7000.00,
        'net_income' => 3000.00,
        'retained_earnings_before' => 5000.00,
        'retained_earnings_after' => 8000.00,
        'journal_entry_id' => $entry->id,
        'notes' => 'closing fiscal year',
        'closed_by' => $user->id,
    ]);

    // Create stock movement
    $movement = StockMovement::create([
        'team_id' => $team->id,
        'merchant_product_id' => $product->id,
        'movement_type' => StockMovementType::SALE,
        'direction' => 'out',
        'quantity' => 1.00,
        'unit_cost' => 60.00,
        'total_cost' => 60.00,
        'quantity_before' => 50.00,
        'quantity_after' => 49.00,
        'reference_type' => PosSale::class,
        'reference_id' => $sale->id,
        'journal_entry_id' => $entry->id,
        'notes' => 'sale stock movement',
        'created_by' => $user->id,
    ]);

    // 2. Export
    $exportService = app(TeamDataExportService::class);
    $exportedData = $exportService->exportTeamData($team);

    // Verify all keys exist in exported JSON
    expect($exportedData)->toBeArray();
    expect($exportedData)->toHaveKeys([
        'export_date', 'export_version', 'export_type', 'team_id', 'team',
        'suppliers', 'distributors', 'merchant_payment_accounts', 'merchant_customers',
        'merchant_products', 'accounts', 'pos_sales', 'merchant_customer_payments',
        'journal_entries', 'merchant_customer_statement_shares',
        'merchant_customer_financial_transfers', 'pos_sale_returns',
        'inventory_counts', 'fiscal_year_closings', 'stock_movements', 'signature'
    ]);

    // Verify some values
    expect($exportedData['merchant_customer_statement_shares'])->toHaveCount(1);
    expect($exportedData['merchant_customer_financial_transfers'])->toHaveCount(1);
    expect($exportedData['pos_sale_returns'])->toHaveCount(1);
    expect($exportedData['inventory_counts'])->toHaveCount(1);
    expect($exportedData['fiscal_year_closings'])->toHaveCount(1);
    expect($exportedData['stock_movements'])->toHaveCount(1);

    // Verify excel sheets generated without errors
    $excelSheets = $exportService->toExcelSheets($team);
    expect($excelSheets)->toBeArray();
    
    $sheetTitles = collect($excelSheets)->pluck('title')->all();
    expect($sheetTitles)->toContain('التحويلات المالية');
    expect($sheetTitles)->toContain('المرتجعات والاستبدال');
    expect($sheetTitles)->toContain('تفاصيل المرتجع والاستبدال');
    expect($sheetTitles)->toContain('حركات المخزون');
    expect($sheetTitles)->toContain('الجرد السنوي');
    expect($sheetTitles)->toContain('تفاصيل الجرد السنوي');
    expect($sheetTitles)->toContain('الإغلاق السنوي');

    // 3. Delete / Purge
    $deletionService = app(TeamDataDeletionService::class);
    $deleted = $deletionService->deleteTeamBusinessData($team);
    expect($deleted)->toBeTrue();

    // Verify DB tables are empty
    expect(Supplier::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(Distributor::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantPaymentAccount::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantCustomer::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantProduct::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(Account::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(PosSale::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantCustomerPayment::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(JournalEntry::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantCustomerStatementShare::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(MerchantCustomerFinancialTransfer::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(PosSaleReturn::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(InventoryCount::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(FiscalYearClosing::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);
    expect(StockMovement::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(0);

    // 4. Import
    $importService = app(TeamDataImportService::class);
    $imported = $importService->importTeamData($team, $user, $exportedData);
    expect($imported)->toBeTrue();

    // Verify DB tables are populated again
    expect(Supplier::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(Distributor::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantPaymentAccount::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantCustomer::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantProduct::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(Account::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(2); // root + child
    expect(PosSale::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantCustomerPayment::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(JournalEntry::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantCustomerStatementShare::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(MerchantCustomerFinancialTransfer::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(PosSaleReturn::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(InventoryCount::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(FiscalYearClosing::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
    expect(StockMovement::withoutGlobalScopes()->where('team_id', $team->id)->count())->toBe(1);
});
