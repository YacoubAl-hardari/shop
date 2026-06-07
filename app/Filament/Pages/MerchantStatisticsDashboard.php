<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Widgets\Merchant\MerchantBusinessOverviewWidget;
use App\Filament\Widgets\Merchant\MerchantLowStockWidget;
use App\Filament\Widgets\Merchant\MerchantPaymentMixChart;
use App\Filament\Widgets\Merchant\MerchantRecentSalesWidget;
use App\Filament\Widgets\Merchant\MerchantSalesTrendChart;
use App\Filament\Widgets\Merchant\MerchantTopProductsChart;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MerchantStatisticsDashboard extends Page
{
    use HasRoleAccess;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'لوحة التحكم';

    protected static ?string $title = 'لوحة الإحصائيات';

    protected static ?int $navigationSort = -10;

    protected string $view = 'filament.pages.merchant-statistics-dashboard';

    public function getWidgets(): array
    {
        return [
            MerchantBusinessOverviewWidget::class,
            MerchantSalesTrendChart::class,
            MerchantPaymentMixChart::class,
            MerchantTopProductsChart::class,
            MerchantRecentSalesWidget::class,
            MerchantLowStockWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
