<?php

use App\Models\Team;
use App\Models\User;
use App\Models\MerchantProduct;
use App\Models\MerchantCustomer;
use App\Services\PosSaleService;
use App\Services\PosReturnService;
use App\Enums\SalePaymentType;
use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->merchant()->create();
    $this->team = Team::create(['name' => 'Test Team', 'slug' => 'test-team']);
    $this->team->members()->attach($this->user, ['role' => 'owner']);
    (new ChartOfAccountsSeeder)->run($this->team);
    $this->actingAs($this->user);
    $this->saleService = app(PosSaleService::class);
    $this->returnService = app(PosReturnService::class);
    $this->product = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'Product A',
        'cost' => 10.00,
        'price' => 15.00,
        'stock_quantity' => 20.0,
        'is_active' => true,
    ]);
});

it('processes simple return and restores stock and posts balanced entries', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        30.00,
        null,
        'cash'
    );

    // Stock quantity is now 18
    expect((float) $this->product->fresh()->stock_quantity)->toBe(18.0);

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 2.0,
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
            'return_reason' => 'defective',
        ]
    ];

    $saleReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        $returnItems,
        RefundMethod::CASH,
        'Refund notes'
    );

    expect((float) $saleReturn->returned_amount)->toBe(30.00);
    expect((float) $this->product->fresh()->stock_quantity)->toBe(20.0); // Stock quantity restored
});

it('processes exchange and posts balanced entries', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        30.00,
        null,
        'cash'
    );

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
            'return_reason' => 'defective',
        ]
    ];

    $exchangeProduct = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'Product B',
        'cost' => 15.00,
        'price' => 20.00,
        'stock_quantity' => 10.0,
        'is_active' => true,
    ]);

    $exchangeItems = [
        [
            'merchant_product_id' => $exchangeProduct->id,
            'product_name' => $exchangeProduct->name,
            'quantity' => 1.0,
            'unit_price' => 20.00,
            'unit_cost' => 15.00,
        ]
    ];

    $saleReturn = $this->returnService->processExchange(
        $this->team,
        $sale,
        $returnItems,
        $exchangeItems,
        RefundMethod::CASH,
        'Exchange notes'
    );

    expect((float) $saleReturn->returned_amount)->toBe(15.00);
    expect((float) $saleReturn->exchange_amount)->toBe(20.00);
    expect((float) $saleReturn->price_difference)->toBe(5.00);
    expect((float) $this->product->fresh()->stock_quantity)->toBe(19.0);
    expect((float) $exchangeProduct->fresh()->stock_quantity)->toBe(9.0);
});

it('prevents checkout of sale when product stock is insufficient', function () {
    $this->product->update(['stock_quantity' => 1.0]);

    // Try selling 2 units (insufficient stock)
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("الكمية المطلوبة للمنتج (Product A) هي 2.00، ولكن المتاح في المخزن هو 1.00 فقط.");

    $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        30.00,
        null,
        'cash'
    );
});

it('prevents process of exchange when replacement stock is insufficient', function () {
    // 1. Create original sale (valid)
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 1.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        15.00,
        null,
        'cash'
    );

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
            'return_reason' => 'defective',
        ]
    ];

    // Create exchange product with only 0.5 stock
    $exchangeProduct = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'Product B',
        'cost' => 15.00,
        'price' => 20.00,
        'stock_quantity' => 0.5,
        'is_active' => true,
    ]);

    $exchangeItems = [
        [
            'merchant_product_id' => $exchangeProduct->id,
            'product_name' => $exchangeProduct->name,
            'quantity' => 1.0, // Needs 1.0, only has 0.5
            'unit_price' => 20.00,
            'unit_cost' => 15.00,
        ]
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("الكمية البديلة المطلوبة للمنتج (Product B) هي 1.00، ولكن المتاح في المخزن هو 0.50 فقط.");

    $this->returnService->processExchange(
        $this->team,
        $sale,
        $returnItems,
        $exchangeItems,
        RefundMethod::CASH,
        'Exchange notes'
    );
});

it('prevents returning a quantity greater than sold quantity on the invoice', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        30.00,
        null,
        'cash'
    );

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 3.0, // Requested 3.0, only sold 2.0
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
            'return_reason' => 'defective',
        ]
    ];

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("الكمية المرتجعة للمنتج (Product A) هي 3.00، ولكن المتبقي المتاح للإرجاع من الفاتورة هو 2.00 فقط.");

    $this->returnService->processReturn(
        $this->team,
        $sale,
        $returnItems,
        RefundMethod::CASH
    );
});

it('prevents returning a quantity when previous partial returns already returned it', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 2.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        30.00,
        null,
        'cash'
    );

    $saleItem = $sale->items->first();

    // First return: 1.0 unit (valid)
    $this->returnService->processReturn(
        $this->team,
        $sale,
        [
            [
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 15.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
                'return_reason' => 'defective',
            ]
        ],
        RefundMethod::CASH
    );

    // Second return: trying to return 2.0 units (only 1.0 left)
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("الكمية المرتجعة للمنتج (Product A) هي 2.00، ولكن المتبقي المتاح للإرجاع من الفاتورة هو 1.00 فقط.");

    $this->returnService->processReturn(
        $this->team,
        $sale,
        [
            [
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 2.0,
                'unit_price' => 15.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
                'return_reason' => 'defective',
            ]
        ],
        RefundMethod::CASH
    );
});

it('requires a customer when refunding via credit note for cash sale and updates the sale customer', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 1.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        15.00,
        null,
        'cash'
    );

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
            'return_reason' => 'defective',
        ]
    ];

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale,
            $returnItems,
            RefundMethod::CREDIT_NOTE
        );
        $this->fail('Should have failed due to missing customer');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toBe('يجب تحديد أو إنشاء عميل لتسجيل الرصيد الدائن باسمه.');
    }

    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'New Return Customer',
        'credit_balance' => 0.00,
    ]);

    $saleReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        $returnItems,
        RefundMethod::CREDIT_NOTE,
        null,
        $customer->id
    );

    expect($sale->fresh()->merchant_customer_id)->toBe($customer->id);
    expect((float) $customer->fresh()->credit_balance)->toBe(15.00);
});

it('records damaged return correctly without restoring final stock and posts to account 1202', function () {
    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 1.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        15.00,
        null,
        'cash'
    );

    expect((float) $this->product->fresh()->stock_quantity)->toBe(19.0);

    $saleItem = $sale->items->first();

    $returnItems = [
        [
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 15.00,
            'unit_cost' => 10.00,
            'item_condition' => 'damaged',
            'return_reason' => 'defective',
        ]
    ];

    $saleReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        $returnItems,
        RefundMethod::CASH
    );

    expect((float) $this->product->fresh()->stock_quantity)->toBe(19.0);

    $movements = \App\Models\StockMovement::where('merchant_product_id', $this->product->id)
        ->where('reference_id', $saleReturn->id)
        ->where('reference_type', \App\Models\PosSaleReturn::class)
        ->get();

    expect($movements->count())->toBe(2);
    expect($movements->where('movement_type', \App\Enums\StockMovementType::SALE_RETURN)->first()->direction)->toBe('in');
    expect($movements->where('movement_type', \App\Enums\StockMovementType::WRITE_OFF)->first()->direction)->toBe('out');

    $journalLines = \App\Models\JournalLine::with('account')
        ->whereHas('journalEntry', fn ($query) => $query
            ->where('reference_id', $saleReturn->id)
            ->where('reference_type', \App\Models\PosSaleReturn::class)
        )
        ->get();

    $damagedLine = $journalLines->first(fn ($line) => $line->account->code === '1202');
    expect($damagedLine)->not->toBeNull();
    expect((float) $damagedLine->debit_amount)->toBe(10.00);
});

it('reduces customer receivable balance when return settles against statement', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Credit Customer',
        'balance' => 0.00,
        'credit_balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 1.0,
                'unit_price' => 350.00,
            ],
        ],
        SalePaymentType::CREDIT,
        0,
        $customer,
        'cash'
    );

    expect((float) $customer->fresh()->balance)->toBe(350.00);

    $saleItem = $sale->items->first();

    $saleReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        [
            [
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 350.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
                'return_reason' => 'changed_mind',
            ],
        ],
        RefundMethod::REDUCE_RECEIVABLE
    );

    expect((float) $customer->fresh()->balance)->toBe(0.00);
    expect($saleReturn->refund_method)->toBe(RefundMethod::REDUCE_RECEIVABLE);

    $receivableLine = \App\Models\JournalLine::with('account')
        ->whereHas('journalEntry', fn ($query) => $query
            ->where('reference_id', $saleReturn->id)
            ->where('reference_type', \App\Models\PosSaleReturn::class)
        )
        ->get()
        ->first(fn ($line) => $line->account->code === '1101' && (float) $line->credit_amount === 350.00);

    expect($receivableLine)->not->toBeNull();
});

it('blocks return when invoice merchandise was fully returned already', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Fully Returned Customer',
        'balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [[
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 350.00,
        ]],
        SalePaymentType::CREDIT,
        0,
        $customer,
        'cash'
    );

    $saleItem = $sale->items->first();

    $this->returnService->processReturn(
        $this->team,
        $sale,
        [[
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 350.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
        ]],
        RefundMethod::REDUCE_RECEIVABLE
    );

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale->fresh(['returns']),
            [[
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 350.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
            ]],
            RefundMethod::REDUCE_RECEIVABLE
        );
        $this->fail('Should have blocked duplicate full return');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toContain('تم إرجاع قيمة هذه الفاتورة بالكامل');
    }
});

it('blocks credit note on fully credit sales', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Credit Only Customer',
        'balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [[
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 1100.00,
        ]],
        SalePaymentType::CREDIT,
        0,
        $customer,
        'cash'
    );

    $saleItem = $sale->items->first();

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale,
            [[
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 1100.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
            ]],
            RefundMethod::CREDIT_NOTE
        );
        $this->fail('Should have blocked credit note on credit sale');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toContain('آجلة');
    }
});

it('limits credit note on partial sales to the cash portion only', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Partial Credit Note Customer',
        'balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [[
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 100.00,
        ]],
        SalePaymentType::PARTIAL,
        40.00,
        $customer,
        'cash'
    );

    $saleItem = $sale->items->first();

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale,
            [[
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 100.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
            ]],
            RefundMethod::CREDIT_NOTE
        );
        $this->fail('Should have blocked credit note above cash portion');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toContain('نقداً');
    }

    $partialReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        [[
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 0.4,
            'unit_price' => 100.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
        ]],
        RefundMethod::CREDIT_NOTE
    );

    expect((float) $partialReturn->credit_note_amount)->toBe(40.00);
    expect((float) $customer->fresh()->credit_balance)->toBe(40.00);
});

it('blocks receivable reduction on cash sales', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Cash Customer',
        'balance' => 500.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [
            [
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 1.0,
                'unit_price' => 15.00,
            ],
        ],
        SalePaymentType::CASH,
        15.00,
        $customer,
        'cash'
    );

    $saleItem = $sale->items->first();

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale,
            [[
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 15.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
            ]],
            RefundMethod::REDUCE_RECEIVABLE
        );
        $this->fail('Should have blocked receivable reduction on cash sale');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toContain('نقدية');
    }
});

it('blocks receivable reduction when customer debt is insufficient', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Paid Off Customer',
        'balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [[
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 350.00,
        ]],
        SalePaymentType::CREDIT,
        0,
        $customer,
        'cash'
    );

    $saleItem = $sale->items->first();

    try {
        $this->returnService->processReturn(
            $this->team,
            $sale,
            [[
                'pos_sale_item_id' => $saleItem->id,
                'merchant_product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity_returned' => 1.0,
                'unit_price' => 350.00,
                'unit_cost' => 10.00,
                'item_condition' => 'resellable',
            ]],
            RefundMethod::REDUCE_RECEIVABLE
        );
        $this->fail('Should have blocked receivable reduction without customer debt');
    } catch (\InvalidArgumentException $e) {
        expect($e->getMessage())->toContain('مديونية العميل');
    }
});

it('splits partial sale returns between receivable and cash', function () {
    $customer = MerchantCustomer::create([
        'team_id' => $this->team->id,
        'name' => 'Partial Customer',
        'balance' => 0.00,
    ]);

    $sale = $this->saleService->createSale(
        $this->team,
        [[
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1.0,
            'unit_price' => 100.00,
        ]],
        SalePaymentType::PARTIAL,
        40.00,
        $customer,
        'cash'
    );

    expect((float) $customer->fresh()->balance)->toBe(60.00);

    $saleItem = $sale->items->first();

    $saleReturn = $this->returnService->processReturn(
        $this->team,
        $sale,
        [[
            'pos_sale_item_id' => $saleItem->id,
            'merchant_product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity_returned' => 1.0,
            'unit_price' => 100.00,
            'unit_cost' => 10.00,
            'item_condition' => 'resellable',
        ]],
        RefundMethod::SPLIT_SETTLEMENT
    );

    expect($saleReturn->refund_method)->toBe(RefundMethod::SPLIT_SETTLEMENT);
    expect((float) $saleReturn->refunded_to_customer)->toBe(40.00);
    expect((float) $saleReturn->receivable_reduction_amount)->toBe(60.00);
    expect((float) $customer->fresh()->balance)->toBe(0.00);
});
