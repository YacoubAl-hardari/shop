<?php

use App\Models\Team;
use App\Models\User;
use App\Models\MerchantProduct;
use App\Services\InventoryCountService;
use App\Enums\InventoryCountStatus;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->merchant()->create();
    $this->team = Team::create(['name' => 'Test Team', 'slug' => 'test-team']);
    $this->team->members()->attach($this->user, ['role' => 'owner']);
    (new ChartOfAccountsSeeder)->run($this->team);
    $this->actingAs($this->user);
    $this->service = app(InventoryCountService::class);
    $this->product = MerchantProduct::create([
        'team_id' => $this->team->id,
        'name' => 'Product A',
        'cost' => 10.00,
        'price' => 15.00,
        'stock_quantity' => 20.0,
        'is_active' => true,
    ]);
});

it('creates, completes, and approves inventory counts with variances', function () {
    // 1. Create Count
    $count = $this->service->createCount($this->team, now(), 2026);
    expect($count->status)->toBe(InventoryCountStatus::DRAFT);
    expect($count->items)->toHaveCount(1);
    
    $item = $count->items->first();
    expect((float) $item->book_quantity)->toBe(20.0);

    // 2. Update Counted Qty to 22 (Gain of 2)
    $this->service->updateCountedQuantity($item, 22.0, 'Found extra stock');
    
    // 3. Complete Count
    $this->service->completeCount($count);
    expect($count->fresh()->status)->toBe(InventoryCountStatus::COMPLETED);
    expect((float) $count->fresh()->variance_value)->toBe(20.00); // 2 gain * 10 cost = 20

    // 4. Approve and Post
    $this->service->approveAndPost($count, $this->team);
    expect($count->fresh()->status)->toBe(InventoryCountStatus::APPROVED);
    expect((float) $this->product->fresh()->stock_quantity)->toBe(22.0); // Qty updated to 22
});

it('calculates the net sold quantity correctly for an inventory count item', function () {
    // 1. Create a sale movement (5 units out)
    app(\App\Services\StockMovementService::class)->record(
        $this->team,
        $this->product,
        \App\Enums\StockMovementType::SALE,
        5.0,
        (float) $this->product->cost
    );

    // 2. Create a return movement (2 units in)
    app(\App\Services\StockMovementService::class)->record(
        $this->team,
        $this->product,
        \App\Enums\StockMovementType::SALE_RETURN,
        2.0,
        (float) $this->product->cost
    );

    // 3. Create Inventory Count
    $count = $this->service->createCount($this->team, now(), 2026);
    $item = $count->items->first();

    // 4. Assert getSoldQuantity() returns 3.0 (5.0 sale - 2.0 return)
    expect((float) $item->getSoldQuantity())->toBe(3.0);
});

it('calculates the net damaged quantity correctly for an inventory count item', function () {
    // 1. Create a write_off movement (3 units)
    app(\App\Services\StockMovementService::class)->record(
        $this->team,
        $this->product,
        \App\Enums\StockMovementType::WRITE_OFF,
        3.0,
        (float) $this->product->cost
    );

    // 2. Create Inventory Count
    $count = $this->service->createCount($this->team, now(), 2026);
    $item = $count->items->first();

    // 3. Assert getDamagedQuantity() returns 3.0
    expect((float) $item->getDamagedQuantity())->toBe(3.0);
});

it('reposts journal entry for an approved inventory count when the entry is voided', function () {
    $count = $this->service->createCount($this->team, now(), 2026);
    $item = $count->items->first();
    $this->service->updateCountedQuantity($item, 22.0, 'Found extra stock');
    $this->service->completeCount($count);
    $this->service->approveAndPost($count, $this->team);

    $originalEntry = $count->fresh()->journalEntry;
    expect($originalEntry)->not->toBeNull();
    expect($originalEntry->status)->toBe(\App\Enums\JournalEntryStatus::POSTED);

    // Simulate voiding the entry by updating its status directly in database
    $originalEntry->update(['status' => \App\Enums\JournalEntryStatus::VOID]);
    expect($originalEntry->fresh()->status)->toBe(\App\Enums\JournalEntryStatus::VOID);

    // Repost the journal entry
    $newEntry = $this->service->repostJournalEntry($count->fresh(), $this->team);
    expect($newEntry)->not->toBeNull();
    expect($newEntry->status)->toBe(\App\Enums\JournalEntryStatus::POSTED);
    expect($count->fresh()->journal_entry_id)->toBe($newEntry->id);
    expect($newEntry->id)->not->toBe($originalEntry->id);
});
