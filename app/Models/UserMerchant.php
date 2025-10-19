<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserMerchant extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'information',
        'is_active',
        'balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the user that owns the merchant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the merchant wallets.
     */
    public function wallets(): HasMany
    {
        return $this->hasMany(UserMerchantWallet::class);
    }

    /**
     * Get the merchant products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(UserMerchantProduct::class);
    }

    /**
     * Get the merchant orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(UserMerchantOrder::class);
    }

    /**
     * Get the account statements for this merchant.
     */
    public function accountStatements(): HasMany
    {
        return $this->hasMany(UserMerchantAccountStatement::class);
    }

    /**
     * Get the payment transactions for this merchant.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(UserMerchantPaymentTransaction::class);
    }

    /**
     * Get the account entries for this merchant.
     */
    public function accountEntries(): HasMany
    {
        return $this->hasMany(UserMerchantAccountEntry::class);
    }
}
