<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchant;
use Filament\Facades\Filament;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantProductComparisonWidget extends ApexChartWidget
{
    protected static bool $isDiscovered = false;
    
    protected static ?string $chartId = 'merchantProductComparison';
    
    protected static ?string $heading = 'مقارنة عدد المنتجات بين التجار';
    
    protected static ?string $subheading = 'عدد المنتجات المتاحة لكل تاجر';
    
    protected static ?int $sort = 1;
    
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get product count per merchant
        $merchantProducts = UserMerchant::select(
            'user_merchants.name as merchant_name',
            DB::raw('COUNT(user_merchant_products.id) as product_count')
        )
        ->leftJoin('user_merchant_products', 'user_merchants.id', '=', 'user_merchant_products.user_merchant_id')
        ->where('user_merchants.user_id', $userId)
        ->where('user_merchants.is_active', true)
        ->where('user_merchants.team_id', Filament::getTenant()?->id)
        ->groupBy('user_merchants.id', 'user_merchants.name')
        ->orderByDesc('product_count')
        ->get();
        
        $merchantNames = $merchantProducts->pluck('merchant_name')->filter()->toArray();
        $productCounts = $merchantProducts->pluck('product_count')->filter()->toArray();
        
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
                    'name' => 'عدد المنتجات',
                    'data' => $productCounts,
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
                ],
            ],
            'colors' => ['#3b82f6'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#fff'],
                ],
            ],
        ];
    }
}

