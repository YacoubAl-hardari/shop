<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchant;
use Filament\Facades\Filament;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProductPriceComparisonWidget extends ApexChartWidget
{
    protected static bool $isDiscovered = false;
    
    protected static ?string $chartId = 'productPriceComparison';
    
    protected static ?string $heading = 'مقارنة أسعار المنتجات بين التجار';
    
    protected static ?string $subheading = 'متوسط أسعار المنتجات لكل تاجر';
    
    protected static ?int $sort = 2;
    
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get average price per merchant
        $merchantPrices = UserMerchant::select(
            'user_merchants.name as merchant_name',
            DB::raw('AVG(user_merchant_products.price) as avg_price'),
            DB::raw('MIN(user_merchant_products.price) as min_price'),
            DB::raw('MAX(user_merchant_products.price) as max_price')
        )
        ->leftJoin('user_merchant_products', 'user_merchants.id', '=', 'user_merchant_products.user_merchant_id')
        ->where('user_merchants.user_id', $userId)
        ->where('user_merchants.is_active', true)
        ->where('user_merchants.team_id', Filament::getTenant()?->id)
        ->where('user_merchant_products.is_active', true)
        ->groupBy('user_merchants.id', 'user_merchants.name')
        ->orderByDesc('avg_price')
        ->get();
        
        $merchantNames = $merchantPrices->pluck('merchant_name')->filter()->toArray();
        $avgPrices = $merchantPrices->pluck('avg_price')->filter()->map(fn($price) => round($price ?? 0, 2))->toArray();
        $minPrices = $merchantPrices->pluck('min_price')->filter()->map(fn($price) => round($price ?? 0, 2))->toArray();
        $maxPrices = $merchantPrices->pluck('max_price')->filter()->map(fn($price) => round($price ?? 0, 2))->toArray();
        
        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'متوسط السعر',
                    'data' => $avgPrices,
                ],
                [
                    'name' => 'أقل سعر',
                    'data' => $minPrices,
                ],
                [
                    'name' => 'أعلى سعر',
                    'data' => $maxPrices,
                ],
            ],
            'xaxis' => [
                'categories' => $merchantNames,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                    'formatter' => 'function(value) { return value + " " + ' . json_encode(\App\Helpers\CurrencyHelper::getSymbol()) . '; }',
                ],
            ],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
            'stroke' => [
                'width' => 3,
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontSize' => '10px',
                    'colors' => ['#fff'],
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'right',
            ],
        ];
    }
}

