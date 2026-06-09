<?php

namespace App\Models;

use App\Enums\InventoryCountStatus;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCount extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'count_number',
        'count_date',
        'fiscal_year',
        'status',
        'total_book_value',
        'total_counted_value',
        'variance_value',
        'journal_entry_id',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'count_date'          => 'date',
        'status'              => InventoryCountStatus::class,
        'total_book_value'    => 'decimal:2',
        'total_counted_value' => 'decimal:2',
        'variance_value'      => 'decimal:2',
        'approved_at'         => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryCountItem::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [InventoryCountStatus::DRAFT, InventoryCountStatus::IN_PROGRESS]);
    }

    public function canBeApproved(): bool
    {
        return $this->status === InventoryCountStatus::COMPLETED;
    }

    public function recalculateTotals(): void
    {
        $this->total_book_value    = $this->items()->sum('book_value');
        $this->total_counted_value = $this->items()->sum('counted_value');
        $this->variance_value      = $this->total_counted_value - $this->total_book_value;
        $this->save();
    }
}
