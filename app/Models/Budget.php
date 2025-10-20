<?php

namespace App\Models;

use App\Enums\BudgetPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'period',
        'total_limit',
        'spent_amount',
        'remaining_amount',
        'start_date',
        'end_date',
        'alert_percentage',
        'is_active',
        'auto_renew',
    ];

    protected $casts = [
        'period' => BudgetPeriod::class,
        'total_limit' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the categories for this budget.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    /**
     * Get the alerts for this budget.
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
        if ($this->total_limit <= 0) {
            return 0;
        }

        return round(($this->spent_amount / $this->total_limit) * 100, 2);
    }

    /**
     * Check if budget is over the limit.
     */
    public function isOverLimit(): bool
    {
        return $this->spent_amount > $this->total_limit;
    }

    /**
     * Check if budget is near the limit.
     */
    public function isNearLimit(): bool
    {
        return $this->spent_percentage >= $this->alert_percentage;
    }

    /**
     * Check if budget is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Update spent amount and remaining amount.
     */
    public function updateSpending(float $amount): void
    {
        $this->spent_amount += $amount;
        $this->remaining_amount = $this->total_limit - $this->spent_amount;
        $this->save();
    }

    /**
     * Reset budget (for auto-renewal).
     */
    public function reset(): void
    {
        $this->spent_amount = 0;
        $this->remaining_amount = $this->total_limit;
        
        // Update dates based on period
        if ($this->period !== BudgetPeriod::CUSTOM) {
            $days = $this->period->getDays();
            $this->start_date = now();
            $this->end_date = now()->addDays($days);
        }
        
        $this->save();
    }
}


