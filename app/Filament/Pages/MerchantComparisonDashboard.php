<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use BackedEnum;
use Filament\Pages\Page;

class MerchantComparisonDashboard extends Page
{
    use HasRoleAccess;

    protected static function allowedRoles(): array
    {
        return [UserType::USER, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected string $view = 'filament.pages.merchant-comparison-dashboard';
    
    protected static ?string $navigationLabel = 'مقارنة التجار';
    
    protected static ?string $title = 'لوحة مقارنة التجار';
    
    protected static ?int $navigationSort = 1;
    
    public static function getNavigationGroup(): ?string
    {
        return 'الإحصائيات';
    }
    
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\MerchantComparisonStatsWidget::class,
            \App\Filament\Widgets\MerchantProductComparisonWidget::class,
            \App\Filament\Widgets\ProductPriceComparisonWidget::class,
            \App\Filament\Widgets\MerchantSalesComparisonWidget::class,
            \App\Filament\Widgets\ProductPerformanceTableWidget::class,
            \App\Filament\Widgets\SimilarProductsComparisonWidget::class,
        ];
    }
    
    public function getColumns(): int | string | array
    {
        return 2;
    }
}
