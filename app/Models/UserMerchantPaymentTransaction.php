<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentTransactionStatus;
use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserMerchantPaymentTransaction extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'user_id',
        'user_merchant_id',
        'user_merchant_wallet_id',
        'transaction_number',
        'amount',
        'payment_method',
        'status',
        'notes',
        'reference_number',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentTransactionStatus::class,
    ];

    /**
     * Get the user that owns the payment transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant that owns the payment transaction.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the merchant wallet used for this payment.
     */
    public function userMerchantWallet(): BelongsTo
    {
        return $this->belongsTo(UserMerchantWallet::class);
    }


}
