<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserMerchantAccountStatement extends Model
{
    protected $fillable = [
        'user_id',
        'user_merchant_id',
        'debit_amount',
        'credit_amount',
        'balance',
        'transaction_type',
        'reference_type',
        'reference_id',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Get the user that owns the account statement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant that owns the account statement.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the reference model (order, payment transaction, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
