<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalYearClosing extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'fiscal_year',
        'closing_date',
        'status',
        'total_revenue',
        'total_expense',
        'net_income',
        'retained_earnings_before',
        'retained_earnings_after',
        'journal_entry_id',
        'notes',
        'closed_by',
        'posted_at',
    ];

    protected $casts = [
        'closing_date'             => 'date',
        'total_revenue'            => 'decimal:2',
        'total_expense'            => 'decimal:2',
        'net_income'               => 'decimal:2',
        'retained_earnings_before' => 'decimal:2',
        'retained_earnings_after'  => 'decimal:2',
        'posted_at'                => 'datetime',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}
