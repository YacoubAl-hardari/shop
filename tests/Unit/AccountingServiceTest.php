<?php

use App\Models\Account;
use App\Models\Team;
use App\Models\User;
use App\Services\AccountingService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->merchant()->create();
    $this->team = Team::create(['name' => 'Test Team', 'slug' => 'test-team']);
    $this->team->members()->attach($this->user, ['role' => 'owner']);
    (new ChartOfAccountsSeeder)->run($this->team);
    $this->actingAs($this->user);
    $this->service = app(AccountingService::class);
});

it('posts a balanced journal entry', function () {
    $entry = $this->service->post(
        $this->team,
        [
            ['account_code' => '1001', 'debit_amount' => 100],
            ['account_code' => '4003', 'credit_amount' => 100],
        ],
        'بيع تجريبي',
    );

    expect($entry->lines)->toHaveCount(2);
    expect($entry->isBalanced())->toBeTrue();
});

it('rejects unbalanced journal entries', function () {
    $this->service->post(
        $this->team,
        [
            ['account_code' => '1001', 'debit_amount' => 100],
            ['account_code' => '4003', 'credit_amount' => 50],
        ],
        'قيد غير متوازن',
    );
})->throws(InvalidArgumentException::class);

it('finds account by code', function () {
    $account = $this->service->getAccountByCode($this->team, '1001');

    expect($account)->toBeInstanceOf(Account::class);
    expect($account->code)->toBe('1001');
});

it('validates balance without posting', function () {
    expect(fn () => $this->service->validateBalance([
        ['debit_amount' => 200, 'credit_amount' => 0],
        ['debit_amount' => 0, 'credit_amount' => 200],
    ]))->not->toThrow(InvalidArgumentException::class);

    expect(fn () => $this->service->validateBalance([
        ['debit_amount' => 100],
        ['credit_amount' => 50],
    ]))->toThrow(InvalidArgumentException::class);
});
