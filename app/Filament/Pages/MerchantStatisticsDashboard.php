<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Exports\MerchantFinancialDataExport;
use App\Filament\Concerns\HasRoleAccess;
use App\Filament\Widgets\Merchant\MerchantBusinessOverviewWidget;
use App\Filament\Widgets\Merchant\MerchantLowStockWidget;
use App\Filament\Widgets\Merchant\MerchantPaymentMixChart;
use App\Filament\Widgets\Merchant\MerchantRecentSalesWidget;
use App\Filament\Widgets\Merchant\MerchantSalesTrendChart;
use App\Filament\Widgets\Merchant\MerchantTopProductsChart;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportFinancialExcel')
                ->label('تصدير البيانات المالية')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('تصدير البيانات المالية')
                ->modalDescription('سيتم تصدير المبيعات والقيود اليومية وسدادات العملاء وكشوف الحسابات إلى Excel.')
                ->modalSubmitActionLabel('نعم، صدّر')
                ->action(function () {
                    $team = Filament::getTenant();
                    $filename = 'merchant_financial_data_'.$team->slug.'_'.now()->format('Y-m-d_His').'.xlsx';

                    return Excel::download(new MerchantFinancialDataExport($team), $filename);
                }),
        ];
    }

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
