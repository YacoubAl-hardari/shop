<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetCategory extends Model
{
    protected $fillable = [
        'user_id',
        'budget_id',
        'name',
        'description',
        'budget_limit',
        'spent_amount',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'budget_limit' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the budget that owns the category.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the merchants in this category.
     */
    public function merchants(): HasMany
    {
        return $this->hasMany(UserMerchant::class);
    }

    /**
     * Get the alerts for this category.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(BudgetAlert::class);
    }

    /**
     * Get the percentage spent.
     */
    public function getSpentPercentageAttribute(): float
    {
        if ($this->budget_limit <= 0) {
            return 0;
        }

        return round(($this->spent_amount / $this->budget_limit) * 100, 2);
    }

    /**
     * Check if category is over the limit.
     */
    public function isOverLimit(): bool
    {
        return $this->spent_amount > $this->budget_limit;
    }

    /**
     * Get remaining amount.
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->budget_limit - $this->spent_amount);
    }

    /**
     * Update spent amount.
     */
    public function updateSpending(float $amount): void
    {
        $this->spent_amount += $amount;
        $this->save();
    }
}


