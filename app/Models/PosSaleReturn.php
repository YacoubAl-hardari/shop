<?php

namespace App\Models;

use App\Enums\RefundMethod;
use App\Enums\ReturnType;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosSaleReturn extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'pos_sale_id',
        'return_number',
        'return_type',
        'refund_method',
        'returned_amount',
        'exchange_amount',
        'price_difference',
        'refunded_to_customer',
        'charged_to_customer',
        'credit_note_amount',
        'status',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'return_type'           => ReturnType::class,
        'refund_method'         => RefundMethod::class,
        'returned_amount'       => 'decimal:2',
        'exchange_amount'       => 'decimal:2',
        'price_difference'      => 'decimal:2',
        'refunded_to_customer'  => 'decimal:2',
        'charged_to_customer'   => 'decimal:2',
        'credit_note_amount'    => 'decimal:2',
    ];

    public function originalSale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(PosSaleReturnItem::class);
    }

    public function exchangeItems(): HasMany
    {
        return $this->hasMany(PosExchangeItem::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isExchange(): bool
    {
        return $this->return_type === ReturnType::EXCHANGE;
    }

    public function isReturn(): bool
    {
        return $this->return_type === ReturnType::RETURN;
    }
}
