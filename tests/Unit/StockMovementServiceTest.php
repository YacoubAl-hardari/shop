<?php

use App\Models\Team;
use App\Models\User;
use App\Models\MerchantProduct;
use App\Services\StockMovementService;
use App\Enums\StockMovementType;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->merchant()->create();
    $this->team = Team::create(['name' => 'Test Team', 'slug' => 'test-team']);
    $this->team->members()->attach($this->user, ['role' => 'owner']);
    (new ChartOfAccountsSeeder)->run($this->team);
    $this->actingAs($this->user);
    $this->service = app(StockMovementService::class);
    $this->product = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'Product 1',
        'cost' => 100.00,
        'price' => 150.00,
        'stock_quantity' => 10.0,
        'is_active' => true,
    ]);
});

it('records purchase and updates average cost and stock quantity', function () {
    $movement = $this->service->recordPurchase($this->team, $this->product, 5, 120);

    expect((float) $movement->quantity)->toBe(5.0);
    expect($movement->direction)->toBe('in');
    
    $product = $this->product->fresh();
    expect((float) $product->stock_quantity)->toBe(15.0);
    // Average cost: ((10 * 100) + (5 * 120)) / 15 = (1000 + 600) / 15 = 106.6667
    expect((float) $product->cost)->toBe(106.67);
});

it('records sale and decrements stock quantity', function () {
    $movement = $this->service->recordSale($this->team, $this->product, 3);

    expect((float) $movement->quantity)->toBe(3.0);
    expect($movement->direction)->toBe('out');
    
    $product = $this->product->fresh();
    expect((float) $product->stock_quantity)->toBe(7.0);
    expect((float) $product->cost)->toBe(100.0);
});

it('records opening balance movement when product is created with stock', function () {
    $product = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'New Product',
        'cost' => 50.00,
        'price' => 75.00,
        'stock_quantity' => 25.0,
        'is_active' => true,
    ]);

    $movement = \App\Models\StockMovement::where('merchant_product_id', $product->id)
        ->where('movement_type', StockMovementType::OPENING_BALANCE)
        ->first();

    expect($movement)->not->toBeNull();
    expect((float) $movement->quantity)->toBe(25.0);
    expect($movement->direction)->toBe('in');
});

it('records adjustment movement when product stock quantity is updated', function () {
    // 1. Stock quantity increased (Adjustment Add)
    $this->product->update(['stock_quantity' => 15.0]); // was 10.0
    
    $movementAdd = \App\Models\StockMovement::where('merchant_product_id', $this->product->id)
        ->where('movement_type', StockMovementType::ADJUSTMENT_ADD)
        ->first();

    expect($movementAdd)->not->toBeNull();
    expect((float) $movementAdd->quantity)->toBe(5.0);
    expect($movementAdd->direction)->toBe('in');

    // 2. Stock quantity decreased (Adjustment Remove)
    $this->product->update(['stock_quantity' => 12.0]); // was 15.0
    
    $movementRemove = \App\Models\StockMovement::where('merchant_product_id', $this->product->id)
        ->where('movement_type', StockMovementType::ADJUSTMENT_REMOVE)
        ->orderByDesc('id')
        ->first();

    expect($movementRemove)->not->toBeNull();
    expect((float) $movementRemove->quantity)->toBe(3.0);
    expect($movementRemove->direction)->toBe('out');
});
