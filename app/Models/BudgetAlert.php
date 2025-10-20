<?php

namespace App\Models;

use App\Enums\BudgetAlertType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetAlert extends Model
{
    protected $fillable = [
        'user_id',
        'budget_id',
        'budget_category_id',
        'type',
        'title',
        'message',
        'trigger_percentage',
        'current_amount',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'type' => BudgetAlertType::class,
        'trigger_percentage' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the budget that owns the alert.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the category that owns the alert.
     */
    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(BudgetCategory::class);
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    /**
     * Scope a query to only include unread alerts.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include read alerts.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}


