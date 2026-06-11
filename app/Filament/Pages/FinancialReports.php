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
use Filament\Facades\Filament;

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

    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $activeTab = 'income_statement'; // 'trial_balance', 'income_statement', 'balance_sheet'

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة';
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfYear()->toDateString();
        $this->endDate = now()->endOfYear()->toDateString();
    }

    public function setPreset(string $preset): void
    {
        switch ($preset) {
            case 'today':
                $this->startDate = now()->startOfDay()->toDateString();
                $this->endDate = now()->endOfDay()->toDateString();
                break;
            case 'month':
                $this->startDate = now()->startOfMonth()->toDateString();
                $this->endDate = now()->endOfMonth()->toDateString();
                break;
            case 'quarter':
                $this->startDate = now()->startOfQuarter()->toDateString();
                $this->endDate = now()->endOfQuarter()->toDateString();
                break;
            case 'year':
                $this->startDate = now()->startOfYear()->toDateString();
                $this->endDate = now()->endOfYear()->toDateString();
                break;
            case 'all':
            default:
                $this->startDate = null;
                $this->endDate = null;
                break;
        }
    }

    public function getKpis(): array
    {
        $income = $this->getIncomeStatement();
        $revenue = $income['revenue'];
        $expenses = $income['expenses'];
        $netIncome = $income['net_income'];

        $margin = $revenue > 0 ? ($netIncome / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_income' => $netIncome,
            'margin' => $margin,
        ];
    }

    public function getTrialBalance(): array
    {
        $accounts = Account::query()
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();

        return $accounts->map(function (Account $account) {
            $debitQuery = JournalLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                    if ($this->startDate) {
                        $q->where('entry_date', '>=', $this->startDate);
                    }
                    if ($this->endDate) {
                        $q->where('entry_date', '<=', $this->endDate);
                    }
                });

            $creditQuery = JournalLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                    if ($this->startDate) {
                        $q->where('entry_date', '>=', $this->startDate);
                    }
                    if ($this->endDate) {
                        $q->where('entry_date', '<=', $this->endDate);
                    }
                });

            $debit = (float) $debitQuery->sum('debit_amount');
            $credit = (float) $creditQuery->sum('credit_amount');

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
        })->filter(fn ($row) => abs($row['debit']) > 0.001 || abs($row['credit']) > 0.001 || abs($row['balance']) > 0.001)->values()->all();
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

    public function getBalanceSheet(): array
    {
        $assets = $this->getAccountsWithBalances(AccountType::ASSET);
        $liabilities = $this->getAccountsWithBalances(AccountType::LIABILITY);
        $equity = $this->getAccountsWithBalances(AccountType::EQUITY);

        $totalAssets = collect($assets)->sum('balance');
        $totalLiabilities = collect($liabilities)->sum('balance');
        
        $netIncome = $this->getIncomeStatement()['net_income'];
        $totalEquity = collect($equity)->sum('balance') + $netIncome;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'net_income' => $netIncome,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilities + $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    protected function getAccountsWithBalances(AccountType $type): array
    {
        $accounts = Account::where('type', $type)
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();

        return $accounts->map(function (Account $account) {
            $debitQuery = JournalLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                    if ($this->startDate) {
                        $q->where('entry_date', '>=', $this->startDate);
                    }
                    if ($this->endDate) {
                        $q->where('entry_date', '<=', $this->endDate);
                    }
                });

            $creditQuery = JournalLine::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                    if ($this->startDate) {
                        $q->where('entry_date', '>=', $this->startDate);
                    }
                    if ($this->endDate) {
                        $q->where('entry_date', '<=', $this->endDate);
                    }
                });

            $debit = (float) $debitQuery->sum('debit_amount');
            $credit = (float) $creditQuery->sum('credit_amount');

            $balance = $account->normal_balance->value === 'debit'
                ? $debit - $credit
                : $credit - $debit;

            return [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];
        })->filter(fn ($row) => abs($row['balance']) > 0.001)->values()->all();
    }

    protected function sumByType(AccountType $type): float
    {
        $accountIds = Account::where('type', $type)->pluck('id');

        $debitQuery = JournalLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function($q) {
                $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                if ($this->startDate) {
                    $q->where('entry_date', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $q->where('entry_date', '<=', $this->endDate);
                }
            });

        $creditQuery = JournalLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', function($q) {
                $q->where('status', \App\Enums\JournalEntryStatus::POSTED);
                if ($this->startDate) {
                    $q->where('entry_date', '>=', $this->startDate);
                }
                if ($this->endDate) {
                    $q->where('entry_date', '<=', $this->endDate);
                }
            });

        $debit = (float) $debitQuery->sum('debit_amount');
        $credit = (float) $creditQuery->sum('credit_amount');

        return $type === AccountType::REVENUE ? ($credit - $debit) : ($debit - $credit);
    }
}
