<?php

namespace App\Services;

use App\Enums\BudgetAlertType;
use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\BudgetCategory;
use App\Models\User;
use App\Models\UserMerchantOrder;
use App\Repositories\BudgetRepository;
use App\Repositories\BudgetCategoryRepository;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        protected BudgetRepository $budgetRepository,
        protected BudgetCategoryRepository $categoryRepository
    ) {}

    /**
     * Process order spending against budget
     */
    public function processOrderSpending(UserMerchantOrder $order): void
    {
        $user = $order->user;
        $merchant = $order->userMerchant;
        $amount = $order->total_price;

        DB::transaction(function () use ($user, $merchant, $amount, $order) {
            // 1. Update active budget
            $budget = $this->budgetRepository->getActiveBudget($user->id);
            if ($budget) {
                $this->budgetRepository->addSpending($budget, $amount);
                $this->checkBudgetAlerts($budget, $amount, $order);
            }

            // 2. Update category spending
            if ($merchant->budget_category_id) {
                $category = BudgetCategory::find($merchant->budget_category_id);
                if ($category) {
                    $this->categoryRepository->addSpending($category, $amount);
                    $this->checkCategoryAlerts($category, $amount, $order);
                }
            }
        });
    }

    /**
     * Check and create budget alerts
     */
    protected function checkBudgetAlerts(Budget $budget, float $amount, UserMerchantOrder $order): void
    {
        $percentage = $budget->spent_percentage;

        // Alert if exceeded
        if ($budget->isOverLimit()) {
            $this->createAlert(
                $budget->user_id,
                $budget->id,
                null,
                BudgetAlertType::EXCEEDED,
                '🚨 تجاوزت ميزانيتك!',
                sprintf(
                    'صرفت %.2f ريال من ميزانية %.2f ريال. تجاوزت الحد بـ %.2f ريال!',
                    $budget->spent_amount,
                    $budget->total_limit,
                    $budget->spent_amount - $budget->total_limit
                ),
                $percentage,
                $budget->spent_amount
            );

            Notification::make()
                ->danger()
                ->title('🚨 تجاوزت ميزانيتك!')
                ->body(sprintf('تجاوزت ميزانية "%s" بمبلغ %.2f ريال', $budget->name, $budget->spent_amount - $budget->total_limit))
                ->persistent()
                ->send();
        }
        // Alert if near limit
        elseif ($budget->isNearLimit()) {
            $this->createAlert(
                $budget->user_id,
                $budget->id,
                null,
                BudgetAlertType::WARNING,
                '⚠️ اقتربت من نهاية ميزانيتك',
                sprintf(
                    'صرفت %.1f%% من ميزانيتك (%.2f من %.2f ريال). تبقى لك %.2f ريال فقط.',
                    $percentage,
                    $budget->spent_amount,
                    $budget->total_limit,
                    $budget->remaining_amount
                ),
                $percentage,
                $budget->spent_amount
            );

            Notification::make()
                ->warning()
                ->title('⚠️ اقتربت من نهاية ميزانيتك')
                ->body(sprintf('صرفت %.1f%% من ميزانية "%s"', $percentage, $budget->name))
                ->send();
        }
    }

    /**
     * Check and create category alerts
     */
    protected function checkCategoryAlerts(BudgetCategory $category, float $amount, UserMerchantOrder $order): void
    {
        $percentage = $category->spent_percentage;

        // Alert if exceeded
        if ($category->isOverLimit()) {
            $this->createAlert(
                $category->user_id,
                null,
                $category->id,
                BudgetAlertType::EXCEEDED,
                '🚨 تجاوزت ميزانية الفئة!',
                sprintf(
                    'صرفت %.2f ريال من ميزانية فئة "%s" (الحد: %.2f ريال)',
                    $category->spent_amount,
                    $category->name,
                    $category->budget_limit
                ),
                $percentage,
                $category->spent_amount
            );

            Notification::make()
                ->danger()
                ->title('🚨 تجاوزت ميزانية الفئة!')
                ->body(sprintf('تجاوزت ميزانية فئة "%s"', $category->name))
                ->send();
        }
        // Alert if over 80%
        elseif ($percentage >= 80) {
            $this->createAlert(
                $category->user_id,
                null,
                $category->id,
                BudgetAlertType::WARNING,
                '⚠️ اقتربت من نهاية ميزانية الفئة',
                sprintf(
                    'صرفت %.1f%% من ميزانية فئة "%s" (%.2f من %.2f ريال)',
                    $percentage,
                    $category->name,
                    $category->spent_amount,
                    $category->budget_limit
                ),
                $percentage,
                $category->spent_amount
            );
        }
    }

    /**
     * Create a budget alert
     */
    protected function createAlert(
        int $userId,
        ?int $budgetId,
        ?int $categoryId,
        BudgetAlertType $type,
        string $title,
        string $message,
        float $percentage,
        float $amount
    ): BudgetAlert {
        return BudgetAlert::create([
            'user_id' => $userId,
            'budget_id' => $budgetId,
            'budget_category_id' => $categoryId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'trigger_percentage' => $percentage,
            'current_amount' => $amount,
        ]);
    }

    /**
     * Check budget status before order
     */
    public function checkBudgetBeforeOrder(User $user, float $amount): array
    {
        $budget = $this->budgetRepository->getActiveBudget($user->id);

        if (!$budget) {
            return [
                'status' => 'no_budget',
                'can_proceed' => true,
                'message' => 'ليس لديك ميزانية نشطة حالياً',
            ];
        }

        $newTotal = $budget->spent_amount + $amount;
        $newPercentage = ($newTotal / $budget->total_limit) * 100;

        if ($newTotal > $budget->total_limit) {
            return [
                'status' => 'over_budget',
                'can_proceed' => true, // يمكن المتابعة ولكن مع تحذير
                'message' => sprintf(
                    'هذه العملية ستتجاوز ميزانيتك! الحد: %.2f ريال، المتوقع بعد العملية: %.2f ريال',
                    $budget->total_limit,
                    $newTotal
                ),
                'budget' => $budget,
                'new_total' => $newTotal,
                'new_percentage' => $newPercentage,
            ];
        }

        if ($newPercentage >= $budget->alert_percentage) {
            return [
                'status' => 'near_limit',
                'can_proceed' => true,
                'message' => sprintf(
                    'تنبيه: بعد هذه العملية ستصل إلى %.1f%% من ميزانيتك',
                    $newPercentage
                ),
                'budget' => $budget,
                'new_total' => $newTotal,
                'new_percentage' => $newPercentage,
            ];
        }

        return [
            'status' => 'ok',
            'can_proceed' => true,
            'message' => 'ضمن الميزانية',
            'budget' => $budget,
            'new_total' => $newTotal,
            'new_percentage' => $newPercentage,
        ];
    }

    /**
     * Auto-renew expired budgets
     */
    public function autoRenewExpiredBudgets(): int
    {
        $expiredBudgets = $this->budgetRepository->getExpiredAutoRenewBudgets();
        $count = 0;

        foreach ($expiredBudgets as $budget) {
            $this->budgetRepository->reset($budget);
            $count++;

            Notification::make()
                ->success()
                ->title('🔄 تم تجديد ميزانيتك تلقائياً')
                ->body(sprintf('تم تجديد ميزانية "%s" لفترة جديدة', $budget->name))
                ->sendToDatabase($budget->user);
        }

        return $count;
    }
}


