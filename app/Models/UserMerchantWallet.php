<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMerchantWallet extends Model
{
    use BelongsToTeam;

    protected $fillable = [
        'team_id',
        'user_merchant_id',
        'account_name',
        'bank_account_number',
        'bank_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the merchant that owns the wallet.
     */
    public function userMerchant(): BelongsTo
    {
        return $this->belongsTo(UserMerchant::class);
    }

    /**
     * Get the payment transactions using this wallet.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(UserMerchantPaymentTransaction::class);
    }
}
