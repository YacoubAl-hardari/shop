<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Support\RawJs;
use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'revenueChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'الإيرادات الشهرية';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'إجمالي الإيرادات من جميع طلبات التجار';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get revenue for the last 12 months
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
            
            // Sum total price for this month for user's merchants
            $total = UserMerchantOrder::whereHas('userMerchant', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('total_price');
            
            $data->push(round($total, 2));
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'الإيرادات',
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
            'colors' => ['#3b82f6'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetY' => -20,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#304758'],
                ],
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
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + val.toFixed(2)
                    }
                }
            },
            dataLabels: {
                formatter: function (val) {
                    return '$' + val.toFixed(2)
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + val.toFixed(2)
                    }
                }
            }
        }
        JS);
    }
}

