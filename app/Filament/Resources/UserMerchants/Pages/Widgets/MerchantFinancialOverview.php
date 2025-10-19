<?php

namespace App\Filament\Resources\UserMerchants\Pages\Widgets;

use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\Auth;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserMerchantPaymentTransaction;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class MerchantFinancialOverview extends BaseWidget
{
    public ?Model $record = null; 

    protected function getStats(): array
    {
        $user = Auth::user();
        $merchant = $this->record;

        // Calculate total purchases (أو القروض/السلف من التاجر)
        $totalPurchases = (float) UserMerchantOrder::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->sum('total_price');

        // Count of orders
        $ordersCount = UserMerchantOrder::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->count();

        // Calculate total payments
        $totalPayments = (float) UserMerchantPaymentTransaction::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->sum('amount');

        // Count of payments
        $paymentsCount = UserMerchantPaymentTransaction::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->count();

        // Current debt (merchant balance represents what user owes)
        $currentDebt = (float) $merchant->balance;

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
                ->description($user->salary ? sprintf('%.1f%% من الراتب | رصيد التاجر: %s', $debtRatio, number_format($merchant->balance, 2)) : 'رصيد التاجر: ' . number_format($merchant->balance, 2))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($riskColor),

            Stat::make('مستوى الخطر', $riskLevel)
                ->description($user->salary ? 'بناءً على راتبك: ' . number_format($user->salary, 2) . ' ريال' : 'يرجى تحديد راتبك للمراقبة')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color($riskColor),
        ];
    }
}

