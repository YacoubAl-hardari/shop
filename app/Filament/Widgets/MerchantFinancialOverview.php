<?php

namespace App\Filament\Widgets;

use App\Models\UserMerchant;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchantPaymentTransaction;
use App\Enums\PaymentTransactionStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MerchantFinancialOverview extends BaseWidget
{

    protected function getStats(): array
    {
   

        $user = Auth::user();

  
        
        // Store for later use
        // Calculate total purchases (أو القروض/السلف من التاجر)
        $totalPurchases = (float) UserMerchantOrder::where('user_id', $user->id)
            ->sum('total_price');

        // Count of orders
        $ordersCount = UserMerchantOrder::where('user_id', $user->id)
            ->where('user_id', $user->id)
            ->count();

        // Calculate total payments
        $totalPayments = (float) UserMerchantPaymentTransaction::where('user_id', $user->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->sum('amount');

        // Count of payments
        $paymentsCount = UserMerchantPaymentTransaction::where('user_id', $user->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->count();

        // Current debt (merchant balance represents what user owes)
        $currentDebt = (float) $user->balance;

        // Calculate debt ratio compared to salary
        $debtRatio = 0;
        $riskLevel = 'آمن';
        $riskColor = 'success';

        if ($user->salary && $user->salary > 0) {
            $debtRatio = ($currentDebt / $user->salary) * 100;

            if ($debtRatio >= ($user->debt_danger_percentage ?? 80)) {
                $riskLevel = 'خطر';
                $riskColor = 'danger';
            } elseif ($debtRatio >= ($user->debt_warning_percentage ?? 50)) {
                $riskLevel = 'تحذير';
                $riskColor = 'warning';
            }
        }

        return [
            Stat::make('إجمالي القروض/المشتريات', number_format($totalPurchases, 2) . ' ريال')
                ->description("عدد السجلات: {$ordersCount} | مجموع ما استلمته من التاجر")
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('info'),

            Stat::make('إجمالي المدفوعات', number_format($totalPayments, 2) . ' ريال')
                ->description("عدد الدفعات: {$paymentsCount} | مجموع ما دفعته للتاجر")
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('الديون الحالية', number_format($currentDebt, 2) . ' ريال')
                ->description($user->salary ? sprintf('%.1f%% من الراتب | رصيد التاجر: %s', $debtRatio, number_format($user->balance, 2)) : 'رصيد التاجر: ' . number_format($user->balance, 2))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($riskColor),

            Stat::make('مستوى الخطر', $riskLevel)
                ->description($user->salary ? 'بناءً على راتبك: ' . number_format($user->salary, 2) . ' ريال' : 'يرجى تحديد راتبك للمراقبة')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color($riskColor),
        ];
    }
}

