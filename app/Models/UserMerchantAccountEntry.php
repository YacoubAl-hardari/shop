<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserMerchantAccountEntry extends Model
{
    protected $fillable = [
        'user_id',
        'user_merchant_id',
        'entry_number',
        'entry_type',
        'amount',
        'debit_amount',
        'credit_amount',
        'description',
        'reference_type',
        'reference_id',
        'balance_after',
        'entry_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'entry_date' => 'date',
    ];

    /**
     * Get the user that owns the account entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant that owns the account entry.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the user who created this entry.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (order, payment transaction, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
