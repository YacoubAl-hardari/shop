<?php

namespace App\Filament\Pages;

use App\Enums\AccountType;
use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Models\Account;
use App\Models\JournalLine;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class FinancialReports extends Page
{
    use HasRoleAccess;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'التقارير المالية';

    protected static ?string $title = 'التقارير المالية';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.financial-reports';

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة';
    }

    public function getTrialBalance(): array
    {
        $accounts = Account::query()
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();

        return $accounts->map(function (Account $account) {
            $debit = JournalLine::where('account_id', $account->id)->sum('debit_amount');
            $credit = JournalLine::where('account_id', $account->id)->sum('credit_amount');
            $balance = $account->normal_balance->value === 'debit'
                ? $debit - $credit
                : $credit - $debit;

            return [
                'code' => $account->code,
                'name' => $account->name,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];
        })->filter(fn ($row) => abs($row['balance']) > 0.001)->values()->all();
    }

    public function getIncomeStatement(): array
    {
        $revenue = $this->sumByType(AccountType::REVENUE);
        $expenses = $this->sumByType(AccountType::EXPENSE);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_income' => $revenue - $expenses,
        ];
    }

    protected function sumByType(AccountType $type): float
    {
        $accountIds = Account::where('type', $type)->pluck('id');

        $debit = JournalLine::whereIn('account_id', $accountIds)->sum('debit_amount');
        $credit = JournalLine::whereIn('account_id', $accountIds)->sum('credit_amount');

        return $type === AccountType::REVENUE ? ($credit - $debit) : ($debit - $credit);
    }
}
