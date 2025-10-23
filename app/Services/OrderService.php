<?php

namespace App\Services;

use App\Models\UserMerchantOrder;
use App\Repositories\UserMerchantOrderRepository;
use App\Repositories\UserMerchantOrderItemRepository;
use App\Repositories\UserMerchantRepository;
use App\Repositories\UserMerchantAccountEntryRepository;
use App\Repositories\UserMerchantAccountStatementRepository;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected UserMerchantOrderRepository $orderRepository,
        protected UserMerchantOrderItemRepository $orderItemRepository,
        protected UserMerchantRepository $merchantRepository,
        protected UserMerchantAccountEntryRepository $accountEntryRepository,
        protected UserMerchantAccountStatementRepository $accountStatementRepository,
        protected BudgetService $budgetService
    ) {}

    /**
     * Generate order number for a user
     *
     * @param int $userId
     * @return string
     */
    public function generateOrderNumber(int $userId): string
    {
        return $this->orderRepository->generateOrderNumber($userId);
    }

    /**
     * Generate order number for a merchant
     *
     * @param int $merchantId
     * @return string
     */
    public function generateOrderNumberForMerchant(int $merchantId): string
    {
        return $this->orderRepository->generateOrderNumberForMerchant($merchantId);
    }

    /**
     * Calculate total price from order items
     *
     * @param array $items
     * @return float
     */
    public function calculateTotalPrice(array $items): float
    {
        return $this->orderItemRepository->calculateTotalPrice($items);
    }

    /**
     * Process order after creation (create items, entry, and update statement)
     *
     * @param UserMerchantOrder $order
     * @param array $orderItems
     * @return void
     */
    public function processOrder(UserMerchantOrder $order, array $orderItems): void
    {
        DB::transaction(function () use ($order, $orderItems) {
            // Create order items
            $this->orderItemRepository->createOrderItems($order->id, $orderItems);

            // Get current balance
            $currentBalance = $this->merchantRepository->getBalance($order->user_merchant_id);
            
            // Calculate new balance (order increases merchant's receivable)
            $newBalance = $currentBalance + $order->total_price;

            // Create account entry
            $this->accountEntryRepository->createOrderEntry($order, $newBalance);

            // Update merchant balance
            $this->merchantRepository->updateBalance($order->user_merchant_id, $newBalance);

            // Update account statement
            $this->accountStatementRepository->updateForOrder(
                $order->user_merchant_id,
                $order->total_price
            );

            // Check and send financial alerts
            $this->checkFinancialAlerts($order, $newBalance);

            // Process budget spending
            $this->budgetService->processOrderSpending($order);
        });
    }

    /**
     * Check and send financial alerts based on user's settings
     *
     * @param UserMerchantOrder $order
     * @param float $newDebt
     * @return void
     */
    protected function checkFinancialAlerts(UserMerchantOrder $order, float $newDebt): void
    {
        $user = $order->user;
        $merchant = $order->userMerchant;

        // Check if user has salary configured
        if (!$user->salary || $user->salary <= 0) {
            return;
        }

        // Calculate debt ratio
        $debtRatio = ($newDebt / $user->salary) * 100;

        // Check spending limits
        if ($user->max_spending_limit && $order->total_price > $user->max_spending_limit) {
            Notification::make()
                ->warning()
                ->title('تحذير: تجاوز حد المشتريات')
                ->body(sprintf(
                    'قيمة الطلب (%.2f ريال) تجاوزت الحد الأقصى المسموح (%.2f ريال)',
                    $order->total_price,
                    $user->max_spending_limit
                ))
                ->persistent()
                ->icon('heroicon-o-exclamation-triangle')
                ->send();
        }

        if ($user->min_spending_limit && $order->total_price < $user->min_spending_limit) {
            Notification::make()
                ->info()
                ->title('معلومة: أقل من الحد الأدنى')
                ->body(sprintf(
                    'قيمة الطلب (%.2f ريال) أقل من الحد الأدنى المحدد (%.2f ريال)',
                    $order->total_price,
                    $user->min_spending_limit
                ))
                ->persistent()
                ->icon('heroicon-o-information-circle')
                ->send();
        }

        // Check debt limit
        if ($user->max_debt_limit && $newDebt > $user->max_debt_limit) {
            Notification::make()
                ->danger()
                ->title('تحذير: تجاوز حد الديون!')
                ->body(sprintf(
                    'إجمالي ديونك لدى %s (%.2f ريال) تجاوز الحد الأقصى المسموح (%.2f ريال)',
                    $merchant->name,
                    $newDebt,
                    $user->max_debt_limit
                ))
                ->persistent()
                ->icon('heroicon-o-exclamation-circle')
                ->persistent()
                ->send();
        }

        // Check debt ratio warnings
        $dangerPercentage = $user->debt_danger_percentage ?? 80;
        $warningPercentage = $user->debt_warning_percentage ?? 50;

        if ($debtRatio >= $dangerPercentage) {
            Notification::make()
                ->danger()
                ->title('خطر: نسبة الديون مرتفعة جداً!')
                ->body(sprintf(
                    'ديونك لدى %s وصلت إلى %.1f%% من راتبك (%.2f من %.2f ريال). يُنصح بشدة تقليل المشتريات!',
                    $merchant->name,
                    $debtRatio,
                    $newDebt,
                    $user->salary
                ))
                ->persistent()
                ->icon('heroicon-o-shield-exclamation')
                ->persistent()
                ->send();
        } elseif ($debtRatio >= $warningPercentage) {
            Notification::make()
                ->warning()
                ->title('تنبيه: نسبة الديون في منطقة الحذر')
                ->body(sprintf(
                    'ديونك لدى %s وصلت إلى %.1f%% من راتبك (%.2f من %.2f ريال). راقب مشترياتك بعناية.',
                    $merchant->name,
                    $debtRatio,
                    $newDebt,
                    $user->salary
                ))
                ->persistent()
                ->icon('heroicon-o-exclamation-triangle')
                ->send();
        }
    }
}

