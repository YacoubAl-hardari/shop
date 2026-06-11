<?php

namespace App\Filament\Widgets\Merchant;

use App\Models\MerchantCustomer;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MerchantBusinessOverviewWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $teamId = Filament::getTenant()?->id;

        $todaySales = (float) PosSale::query()
            ->where('team_id', $teamId)
            ->whereDate('created_at', today())
            ->sum('total_amount');

        $monthSales = (float) PosSale::query()
            ->where('team_id', $teamId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $salesCount = PosSale::query()
            ->where('team_id', $teamId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $customersCount = MerchantCustomer::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->count();

        $productsCount = MerchantProduct::query()
            ->where('team_id', $teamId)
            ->where('is_active', true)
            ->count();

        $totalDebt = (float) MerchantCustomer::query()
            ->where('team_id', $teamId)
            ->sum('balance');

        $totalPrepaid = (float) MerchantCustomer::query()
            ->where('team_id', $teamId)
            ->sum('credit_balance');

        return [
            Stat::make('مبيعات اليوم', number_format($todaySales, 2).' '.\App\Helpers\CurrencyHelper::getSymbol())
                ->description('إجمالي مبيعات نقطة البيع اليوم')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('مبيعات الشهر', number_format($monthSales, 2).' '.\App\Helpers\CurrencyHelper::getSymbol())
                ->description("{$salesCount} عملية بيع هذا الشهر")
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('primary'),

            Stat::make('العملاء النشطون', (string) $customersCount)
                ->description('عملاء مسجّلون في الفرع')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),

            Stat::make('المنتجات النشطة', (string) $productsCount)
                ->description('أصناف متاحة للبيع')
                ->descriptionIcon('heroicon-o-cube')
                ->color('warning'),

            Stat::make('مديونية العملاء', number_format($totalDebt, 2).' '.\App\Helpers\CurrencyHelper::getSymbol())
                ->description('إجمالي الذمم المدينة')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($totalDebt > 0 ? 'danger' : 'success'),

            Stat::make('أرصدة العملاء الفائضة', number_format($totalPrepaid, 2).' '.\App\Helpers\CurrencyHelper::getSymbol())
                ->description('دفعات مقدمة قابلة للخصم')
                ->descriptionIcon('heroicon-o-wallet')
                ->color('gray'),
        ];
    }
}
