<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class OrdersOverTimeChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'ordersOverTimeChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'الطلبات عبر الزمن';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'اتجاهات الطلبات الشهرية لتجارك';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get orders for the last 12 months
        $months = collect();
        $data = collect();
        
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($arabicMonths[$month->month] . ' ' . $month->year);
            
            // Count orders for this month for user's merchants
            $count = UserMerchantOrder::whereHas('userMerchant', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count();
            
            $data->push($count);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'الطلبات',
                    'data' => $data->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $months->toArray(),
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
            'colors' => ['#10b981'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.3,
                ],
            ],
        ];
    }
}
