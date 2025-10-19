<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Support\RawJs;
use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyStatsChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'monthlyStatsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'الأداء الشهري';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'مقارنة الطلبات والإيرادات';

    /**
     * Chart height
     *
     * @var int|null
     */
    protected static ?int $contentHeight = 350;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get data for the last 12 months
        $months = collect();
        $ordersData = collect();
        $revenueData = collect();
        
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($arabicMonths[$month->month] . ' ' . $month->year);
            
            // Get orders and revenue for this month
            $query = UserMerchantOrder::whereHas('userMerchant', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month);
            
            $ordersData->push($query->count());
            $revenueData->push(round($query->sum('total_price'), 2));
        }

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
                    'name' => 'الإيرادات',
                    'type' => 'column',
                    'data' => $revenueData->toArray(),
                ],
                [
                    'name' => 'الطلبات',
                    'type' => 'line',
                    'data' => $ordersData->toArray(),
                ],
            ],
            'stroke' => [
                'width' => [0, 4],
                'curve' => 'smooth',
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
                [
                    'title' => [
                        'text' => 'الإيرادات ($)',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
                [
                    'opposite' => true,
                    'title' => [
                        'text' => 'الطلبات',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#3b82f6', '#10b981'],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }

    /**
     * Add currency formatting
     */
    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: [
                {
                    labels: {
                        formatter: function (val) {
                            return '$' + val.toFixed(2)
                        }
                    }
                },
                {
                    labels: {
                        formatter: function (val) {
                            return val.toFixed(0)
                        }
                    }
                }
            ],
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        if (opts.seriesIndex === 0) {
                            return '$' + val.toFixed(2)
                        }
                        return val
                    }
                }
            }
        }
        JS);
    }
}

