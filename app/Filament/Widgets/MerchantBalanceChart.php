<?php

namespace App\Filament\Widgets;

use App\Models\UserMerchant;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantBalanceChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'merchantBalanceChart';
    protected static ?int $sort = 2;



    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'أرصدة التجار';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'توزيع الرصيد عبر تجارك';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::user()->id;
        
        // Get all merchants for the user with their balances
        $merchants = UserMerchant::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $merchantNames = $merchants->pluck('name')->toArray();
        $balances = $merchants->pluck('balance')->map(fn($value) => round($value, 2))->toArray();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 350,
            ],
            'series' => $balances,
            'labels' => $merchantNames,
            'colors' => ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ef4444', '#ec4899'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'total' => [
                                'show' => true,
                                'label' => 'إجمالي الرصيد',
                                'fontSize' => '18px',
                                'fontWeight' => 600,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

