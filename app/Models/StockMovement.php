<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'merchant_product_id',
        'movement_type',
        'direction',
        'quantity',
        'unit_cost',
        'total_cost',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'journal_entry_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'movement_type'   => StockMovementType::class,
        'quantity'        => 'decimal:2',
        'unit_cost'       => 'decimal:2',
        'total_cost'      => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after'  => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(MerchantProduct::class, 'merchant_product_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isIncoming(): bool
    {
        return $this->direction === 'in';
    }

    public function isOutgoing(): bool
    {
        return $this->direction === 'out';
    }
}
