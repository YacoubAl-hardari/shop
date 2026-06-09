<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\FiscalYearClosing;
use App\Models\JournalLine;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FiscalYearClosingService
{
    public function __construct(
        protected AccountingService $accountingService,
    ) {}

    /**
     * حساب الأرصدة وإعداد مسودة الإغلاق (بدون ترحيل).
     */
    public function prepare(Team $team, int $fiscalYear): FiscalYearClosing
    {
        $postedClosing = FiscalYearClosing::where('team_id', $team->id)
            ->where('fiscal_year', $fiscalYear)
            ->where('status', 'posted')
            ->first();

        if ($postedClosing) {
            $postedClosing->load('journalEntry');
            if ($postedClosing->journalEntry && $postedClosing->journalEntry->status !== \App\Enums\JournalEntryStatus::VOID) {
                throw new InvalidArgumentException("السنة المالية {$fiscalYear} مغلقة مسبقاً.");
            }
        }

        [$totalRevenue, $totalExpense] = $this->calculateRevenueAndExpense($team, $fiscalYear);
        $netIncome = $totalRevenue - $totalExpense;

        $retainedEarningsBefore = $this->getAccountBalance($team, '3002');

        $closing = FiscalYearClosing::updateOrCreate(
            ['team_id' => $team->id, 'fiscal_year' => $fiscalYear],
            [
                'closing_date'             => now()->toDateString(),
                'status'                   => 'draft',
                'total_revenue'            => $totalRevenue,
                'total_expense'            => $totalExpense,
                'net_income'               => $netIncome,
                'retained_earnings_before' => $retainedEarningsBefore,
                'retained_earnings_after'  => $retainedEarningsBefore + $netIncome,
                'notes'                    => null,
                'closed_by'                => null,
            ]
        );

        return $closing;
    }

    /**
     * ترحيل قيود الإغلاق السنوي الفعلية.
     */
    public function post(FiscalYearClosing $closing, Team $team): FiscalYearClosing
    {
        $closing->load('journalEntry');
        if ($closing->isPosted() && $closing->journalEntry && $closing->journalEntry->status !== \App\Enums\JournalEntryStatus::VOID) {
            throw new InvalidArgumentException('تم ترحيل الإغلاق مسبقاً.');
        }

        return DB::transaction(function () use ($closing, $team) {
            $fiscalYear = $closing->fiscal_year;
            $lines = [];

            // 1. إقفال الإيرادات: مدين 4xxx → دائن 3005 (ملخص الدخل)
            $revenueAccounts = $this->getLeafAccounts($team, AccountType::REVENUE);
            foreach ($revenueAccounts as $account) {
                $balance = $this->getAccountBalance($team, $account->code, $fiscalYear);
                if ($balance > 0) {
                    $lines[] = ['account_code' => $account->code, 'debit_amount'  => $balance, 'description' => 'إقفال إيراد — '.$account->name];
                    $lines[] = ['account_code' => '3005',         'credit_amount' => $balance, 'description' => 'إقفال إيراد — '.$account->name];
                }
            }

            // 2. إقفال المصروفات: مدين 3005 → دائن 5xxx, 6xxx
            $expenseAccounts = $this->getLeafAccounts($team, AccountType::EXPENSE);
            foreach ($expenseAccounts as $account) {
                $balance = $this->getAccountBalance($team, $account->code, $fiscalYear);
                if ($balance > 0) {
                    $lines[] = ['account_code' => '3005',         'debit_amount'  => $balance, 'description' => 'إقفال مصروف — '.$account->name];
                    $lines[] = ['account_code' => $account->code, 'credit_amount' => $balance, 'description' => 'إقفال مصروف — '.$account->name];
                }
            }

            // 3. نقل صافي الدخل من 3005 → 3002 (الأرباح المحتجزة)
            $netIncome = (float) $closing->net_income;
            if ($netIncome > 0) {
                // ربح: مدين 3005 / دائن 3002
                $lines[] = ['account_code' => '3005', 'debit_amount'  => $netIncome, 'description' => 'نقل صافي الربح إلى الأرباح المحتجزة'];
                $lines[] = ['account_code' => '3002', 'credit_amount' => $netIncome, 'description' => 'أرباح محتجزة — '.$fiscalYear];
            } elseif ($netIncome < 0) {
                // خسارة: مدين 3002 / دائن 3005
                $loss = abs($netIncome);
                $lines[] = ['account_code' => '3002', 'debit_amount'  => $loss, 'description' => 'خسارة صافية — '.$fiscalYear];
                $lines[] = ['account_code' => '3005', 'credit_amount' => $loss, 'description' => 'نقل صافي الخسارة — '.$fiscalYear];
            }

            if (empty($lines)) {
                throw new InvalidArgumentException('لا توجد حسابات مؤقتة لإقفالها في هذه السنة.');
            }

            $journalEntry = $this->accountingService->post(
                $team, $lines,
                "قيد الإغلاق السنوي — {$fiscalYear}",
            );

            $closing->update([
                'status'           => 'posted',
                'journal_entry_id' => $journalEntry->id,
                'closed_by'        => Auth::id(),
                'posted_at'        => now(),
            ]);

            return $closing->fresh();
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * حساب إجمالي الإيرادات والمصروفات لسنة مالية.
     */
    protected function calculateRevenueAndExpense(Team $team, int $fiscalYear): array
    {
        $startDate = "{$fiscalYear}-01-01";
        $endDate   = "{$fiscalYear}-12-31";

        $revenueAccounts = $this->getLeafAccounts($team, AccountType::REVENUE);
        $expenseAccounts = $this->getLeafAccounts($team, AccountType::EXPENSE);

        $totalRevenue = 0.0;
        $totalExpense = 0.0;

        foreach ($revenueAccounts as $account) {
            $totalRevenue += $this->getAccountBalance($team, $account->code, $fiscalYear);
        }

        foreach ($expenseAccounts as $account) {
            $totalExpense += $this->getAccountBalance($team, $account->code, $fiscalYear);
        }

        return [$totalRevenue, $totalExpense];
    }

    /**
     * رصيد حساب (إجمالي سجلات القيود لهذا الحساب).
     *
     * @param  int|null  $fiscalYear  إذا null يحسب كل السجلات
     */
    protected function getAccountBalance(Team $team, string $accountCode, ?int $fiscalYear = null): float
    {
        $account = Account::where('team_id', $team->id)->where('code', $accountCode)->first();
        if (! $account) {
            return 0.0;
        }

        $query = JournalLine::where('account_id', $account->id);

        if ($fiscalYear) {
            $query->whereHas('journalEntry', function ($q) use ($fiscalYear) {
                $q->whereYear('entry_date', $fiscalYear)->where('status', 'posted');
            });
        }

        $debit  = (float) $query->sum('debit_amount');
        $credit = (float) $query->sum('credit_amount');

        // الرصيد الصافي حسب الطبيعة
        return match ($account->normal_balance->value) {
            'debit'  => $debit - $credit,
            'credit' => $credit - $debit,
            default  => $debit - $credit,
        };
    }

    /**
     * الحسابات الورقية (leaf nodes) من نوع معين.
     */
    protected function getLeafAccounts(Team $team, AccountType $type): \Illuminate\Database\Eloquent\Collection
    {
        return Account::where('team_id', $team->id)
            ->where('type', $type)
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->get();
    }
}
