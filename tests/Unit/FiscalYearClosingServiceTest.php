<?php

use App\Models\Team;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\FiscalYearClosingService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->merchant()->create();
    $this->team = Team::create(['name' => 'Test Team', 'slug' => 'test-team']);
    $this->team->members()->attach($this->user, ['role' => 'owner']);
    (new ChartOfAccountsSeeder)->run($this->team);
    $this->actingAs($this->user);
    $this->accountingService = app(AccountingService::class);
    $this->closingService = app(FiscalYearClosingService::class);
});

it('prepares and posts fiscal year closing correctly', function () {
    // 1. Post a revenue entry
    $this->accountingService->post(
        $this->team,
        [
            ['account_code' => '1001', 'debit_amount' => 1000.00, 'description' => 'Cash in'],
            ['account_code' => '4003', 'credit_amount' => 1000.00, 'description' => 'Sales revenue'],
        ],
        'Sale transaction',
        null,
        null,
        Carbon\Carbon::parse('2026-06-01')
    );

    // 2. Post an expense entry
    $this->accountingService->post(
        $this->team,
        [
            ['account_code' => '5001', 'debit_amount' => 400.00, 'description' => 'COGS'],
            ['account_code' => '1201', 'credit_amount' => 400.00, 'description' => 'Inventory out'],
        ],
        'COGS transaction',
        null,
        null,
        Carbon\Carbon::parse('2026-06-01')
    );

    // 3. Prepare closing
    $closing = $this->closingService->prepare($this->team, 2026);
    expect($closing->status)->toBe('draft');
    expect((float) $closing->total_revenue)->toBe(1000.00);
    expect((float) $closing->total_expense)->toBe(400.00);
    expect((float) $closing->net_income)->toBe(600.00);

    // 4. Post closing
    $postedClosing = $this->closingService->post($closing, $this->team);
    expect($postedClosing->status)->toBe('posted');
    expect($postedClosing->journal_entry_id)->not->toBeNull();
});

it('allows reposting fiscal year closing if the previous journal entry was voided', function () {
    // 1. Post a revenue entry
    $this->accountingService->post(
        $this->team,
        [
            ['account_code' => '1001', 'debit_amount' => 1000.00, 'description' => 'Cash in'],
            ['account_code' => '4003', 'credit_amount' => 1000.00, 'description' => 'Sales revenue'],
        ],
        'Sale transaction',
        null,
        null,
        Carbon\Carbon::parse('2026-06-01')
    );

    // 2. Post closing
    $closing = $this->closingService->prepare($this->team, 2026);
    $postedClosing = $this->closingService->post($closing, $this->team);
    expect($postedClosing->status)->toBe('posted');

    $originalEntry = $postedClosing->journalEntry;
    expect($originalEntry)->not->toBeNull();

    // 3. Void the journal entry (updating directly in DB as we did for counts)
    $originalEntry->update(['status' => \App\Enums\JournalEntryStatus::VOID]);

    // 4. Repost closing
    $repostedClosing = $this->closingService->post($postedClosing->fresh(), $this->team);
    expect($repostedClosing->status)->toBe('posted');
    expect($repostedClosing->journal_entry_id)->not->toBe($originalEntry->id);
    expect($repostedClosing->journalEntry->status)->toBe(\App\Enums\JournalEntryStatus::POSTED);
});
