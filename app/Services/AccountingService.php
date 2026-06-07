<?php

namespace App\Services;

use App\Enums\JournalEntryStatus;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * @param  array<int, array{account_id?: int, account_code?: string, debit_amount?: float, credit_amount?: float, description?: string|null, subledger_type?: string|null, subledger_id?: int|null}>  $lines
     */
    public function post(
        Team $team,
        array $lines,
        string $description,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?\DateTimeInterface $entryDate = null,
    ): JournalEntry {
        $this->validateBalance($lines);

        return DB::transaction(function () use ($team, $lines, $description, $referenceType, $referenceId, $entryDate) {
            $entry = JournalEntry::create([
                'team_id' => $team->id,
                'entry_number' => $this->generateEntryNumber($team),
                'entry_date' => $entryDate ?? now(),
                'description' => $description,
                'status' => JournalEntryStatus::POSTED,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_by' => Auth::id() ?? 1,
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $account = isset($line['account_id'])
                    ? Account::where('team_id', $team->id)->findOrFail($line['account_id'])
                    : $this->getAccountByCode($team, $line['account_code'] ?? '');

                if (! $account) {
                    throw new InvalidArgumentException('الحساب غير موجود');
                }

                if (! $account->isLeaf()) {
                    throw new InvalidArgumentException('لا يمكن الترحيل على حساب تجميعي');
                }

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $account->id,
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null,
                    'subledger_type' => $line['subledger_type'] ?? null,
                    'subledger_id' => $line['subledger_id'] ?? null,
                ]);
            }

            return $entry->load('lines.account');
        });
    }

    public function voidEntry(JournalEntry $entry): JournalEntry
    {
        if ($entry->status === JournalEntryStatus::VOID) {
            throw new InvalidArgumentException('القيد ملغي مسبقاً');
        }

        return DB::transaction(function () use ($entry) {
            $entry->load('lines');

            $reversalLines = $entry->lines->map(fn (JournalLine $line) => [
                'account_id' => $line->account_id,
                'debit_amount' => $line->credit_amount,
                'credit_amount' => $line->debit_amount,
                'description' => 'عكس قيد: '.$line->description,
                'subledger_type' => $line->subledger_type,
                'subledger_id' => $line->subledger_id,
            ])->all();

            $reversal = $this->post(
                $entry->team,
                $reversalLines,
                'إلغاء قيد رقم '.$entry->entry_number,
                JournalEntry::class,
                $entry->id,
            );

            $entry->update(['status' => JournalEntryStatus::VOID]);

            return $reversal;
        });
    }

    public function getAccountByCode(Team $team, string $code): ?Account
    {
        return Account::where('team_id', $team->id)
            ->where('code', $code)
            ->first();
    }

    /**
     * @param  array<int, array{debit_amount?: float, credit_amount?: float}>  $lines
     */
    public function validateBalance(array $lines): void
    {
        $totalDebit = collect($lines)->sum(fn ($line) => (float) ($line['debit_amount'] ?? 0));
        $totalCredit = collect($lines)->sum(fn ($line) => (float) ($line['credit_amount'] ?? 0));

        if (bccomp((string) $totalDebit, (string) $totalCredit, 2) !== 0) {
            throw new InvalidArgumentException(
                'القيد غير متوازن: المدين ('.number_format($totalDebit, 2).') لا يساوي الدائن ('.number_format($totalCredit, 2).')'
            );
        }

        if ($totalDebit <= 0) {
            throw new InvalidArgumentException('يجب أن يكون مجموع المدين أكبر من صفر');
        }
    }

    public function generateEntryNumber(Team $team): string
    {
        $lastEntry = JournalEntry::where('team_id', $team->id)
            ->orderByDesc('id')
            ->first();

        $nextNumber = $lastEntry ? ((int) $lastEntry->entry_number) + 1 : 1;

        return str_pad((string) $nextNumber, 7, '0', STR_PAD_LEFT);
    }
}
