<?php

namespace App\Repositories;

use App\Models\BudgetCategory;
use Illuminate\Support\Collection;

class BudgetCategoryRepository
{
    /**
     * Get all categories for user
     */
    public function getUserCategories(int $userId): Collection
    {
        return BudgetCategory::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get categories by budget
     */
    public function getCategoriesByBudget(int $budgetId): Collection
    {
        return BudgetCategory::where('budget_id', $budgetId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Create a new category
     */
    public function create(array $data): BudgetCategory
    {
        return BudgetCategory::create($data);
    }

    /**
     * Update category
     */
    public function update(BudgetCategory $category, array $data): BudgetCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Delete category
     */
    public function delete(BudgetCategory $category): bool
    {
        return $category->delete();
    }

    /**
     * Add spending to category
     */
    public function addSpending(BudgetCategory $category, float $amount): BudgetCategory
    {
        $category->updateSpending($amount);
        return $category->fresh();
    }

    /**
     * Get category by merchant
     */
    public function getCategoryByMerchant(int $merchantId): ?BudgetCategory
    {
        return BudgetCategory::whereHas('merchants', function ($query) use ($merchantId) {
            $query->where('user_merchants.id', $merchantId);
        })->first();
    }

    /**
     * Get spending by category for user
     */
    public function getSpendingByCategory(int $userId): Collection
    {
        return BudgetCategory::where('user_id', $userId)
            ->where('is_active', true)
            ->select('id', 'name', 'budget_limit', 'spent_amount', 'color', 'icon')
            ->orderByDesc('spent_amount')
            ->get();
    }
}


