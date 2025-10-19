<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantOrderItem;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopProductsChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'topProductsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'أفضل 10 منتجات';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'المنتجات الأكثر مبيعاً حسب إجمالي الإيرادات';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get top 10 products by total sales for user's merchants
        $topProducts = UserMerchantOrderItem::select(
            'user_merchant_products.name',
            DB::raw('SUM(user_merchant_order_items.total_price) as total_sales')
        )
        ->join('user_merchant_products', 'user_merchant_order_items.user_merchant_product_id', '=', 'user_merchant_products.id')
        ->join('user_merchant_orders', 'user_merchant_order_items.user_merchant_order_id', '=', 'user_merchant_orders.id')
        ->join('user_merchants', 'user_merchant_orders.user_merchant_id', '=', 'user_merchants.id')
        ->where('user_merchants.user_id', $userId)
        ->groupBy('user_merchant_products.id', 'user_merchant_products.name')
        ->orderByDesc('total_sales')
        ->limit(10)
        ->get();

        $productNames = $topProducts->pluck('name')->toArray();
        $salesData = $topProducts->pluck('total_sales')->map(fn($value) => round($value, 2))->toArray();

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
                    'name' => 'إجمالي المبيعات',
                    'data' => $salesData,
                ],
            ],
            'xaxis' => [
                'categories' => $productNames,
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
            'colors' => ['#8b5cf6'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => true,
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

