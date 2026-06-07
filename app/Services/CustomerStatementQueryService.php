<?php

namespace App\Services;

use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use Illuminate\Support\Collection;

class CustomerStatementQueryService
{
    public function linesForCustomer(
        MerchantCustomer $customer,
        ?int $teamId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        string $sortDirection = 'desc',
    ): Collection {
        $query = JournalLine::query()
            ->select('journal_lines.*')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.subledger_type', MerchantCustomer::class)
            ->where('journal_lines.subledger_id', $customer->id)
            ->with([
                'journalEntry' => fn ($relation) => $teamId !== null
                    ? $relation->withoutGlobalScopes()
                    : $relation,
                'account' => fn ($relation) => $teamId !== null
                    ? $relation->withoutGlobalScopes()
                    : $relation,
            ]);

        if ($teamId !== null) {
            $query->where('journal_entries.team_id', $teamId);
        }

        if ($dateFrom) {
            $query->whereDate('journal_entries.entry_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('journal_entries.entry_date', '<=', $dateTo);
        }

        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query
            ->orderBy('journal_entries.entry_date', $direction)
            ->orderBy('journal_entries.entry_number', $direction)
            ->orderBy('journal_lines.id', $direction)
            ->get();
    }
}
