<?php

namespace App\Models;

use App\Enums\JournalEntryStatus;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class JournalEntry extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'entry_number',
        'entry_date',
        'description',
        'status',
        'reference_type',
        'reference_id',
        'created_by',
        'posted_at',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'status' => JournalEntryStatus::class,
        'posted_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isBalanced(): bool
    {
        $debit = $this->lines()->sum('debit_amount');
        $credit = $this->lines()->sum('credit_amount');

        return bccomp((string) $debit, (string) $credit, 2) === 0;
    }
}
