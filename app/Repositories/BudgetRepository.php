<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Collection;

class BudgetRepository
{
    /**
     * Get active budget for user
     */
    public function getActiveBudget(int $userId): ?Budget
    {
        return Budget::where('user_id', $userId)
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest()
            ->first();
    }

    /**
     * Get all budgets for user
     */
    public function getUserBudgets(int $userId): Collection
    {
        return Budget::where('user_id', $userId)
            ->with(['categories', 'alerts'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new budget
     */
    public function create(array $data): Budget
    {
        $budget = Budget::create($data);
        $budget->remaining_amount = $budget->total_limit - $budget->spent_amount;
        $budget->save();

        return $budget;
    }

    /**
     * Update budget
     */
    public function update(Budget $budget, array $data): Budget
    {
        $budget->update($data);
        $budget->remaining_amount = $budget->total_limit - $budget->spent_amount;
        $budget->save();

        return $budget->fresh();
    }

    /**
     * Delete budget
     */
    public function delete(Budget $budget): bool
    {
        return $budget->delete();
    }

    /**
     * Add spending to budget
     */
    public function addSpending(Budget $budget, float $amount): Budget
    {
        $budget->updateSpending($amount);
        return $budget->fresh();
    }

    /**
     * Get expired budgets with auto_renew enabled
     */
    public function getExpiredAutoRenewBudgets(): Collection
    {
        return Budget::where('auto_renew', true)
            ->where('end_date', '<', now())
            ->where('is_active', true)
            ->get();
    }

    /**
     * Reset budget
     */
    public function reset(Budget $budget): Budget
    {
        $budget->reset();
        return $budget->fresh();
    }

    /**
     * Get budget statistics
     */
    public function getStatistics(Budget $budget): array
    {
        return [
            'total_limit' => $budget->total_limit,
            'spent_amount' => $budget->spent_amount,
            'remaining_amount' => $budget->remaining_amount,
            'spent_percentage' => $budget->spent_percentage,
            'is_over_limit' => $budget->isOverLimit(),
            'is_near_limit' => $budget->isNearLimit(),
            'is_expired' => $budget->isExpired(),
            'days_remaining' => $budget->end_date->diffInDays(now()),
        ];
    }
}


