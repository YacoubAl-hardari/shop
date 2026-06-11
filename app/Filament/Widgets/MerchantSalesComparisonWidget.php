<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchant;
use Filament\Facades\Filament;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantSalesComparisonWidget extends ApexChartWidget
{
    protected static bool $isDiscovered = false;
    
    protected static ?string $chartId = 'merchantSalesComparison';
    
    protected static ?string $heading = 'مقارنة المبيعات بين التجار';
    
    protected static ?string $subheading = 'إجمالي المبيعات وعدد الطلبات لكل تاجر';
    
    protected static ?int $sort = 3;
    
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get sales data per merchant
        $merchantSales = UserMerchant::select(
            'user_merchants.name as merchant_name',
            DB::raw('COALESCE(SUM(user_merchant_orders.total_price), 0) as total_sales'),
            DB::raw('COUNT(user_merchant_orders.id) as orders_count')
        )
        ->leftJoin('user_merchant_orders', 'user_merchants.id', '=', 'user_merchant_orders.user_merchant_id')
        ->where('user_merchants.user_id', $userId)
        ->where('user_merchants.is_active', true)
        ->where('user_merchants.team_id', Filament::getTenant()?->id)
        ->groupBy('user_merchants.id', 'user_merchants.name')
        ->orderByDesc('total_sales')
        ->get();
        
        $merchantNames = $merchantSales->pluck('merchant_name')->filter()->toArray();
        $totalSales = $merchantSales->pluck('total_sales')->filter()->map(fn($sales) => round($sales ?? 0, 2))->toArray();
        $ordersCount = $merchantSales->pluck('orders_count')->filter()->toArray();
        
        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'إجمالي المبيعات (' . \App\Helpers\CurrencyHelper::getSymbol() . ')',
                    'data' => $totalSales,
                ],
                [
                    'name' => 'عدد الطلبات',
                    'data' => $ordersCount,
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
                [
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                        'formatter' => 'function(value) { return value + " " + ' . json_encode(\App\Helpers\CurrencyHelper::getSymbol()) . '; }',
                    ],
                ],
                [
                    'opposite' => true,
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#8b5cf6', '#06b6d4'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => false,
                ],
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

