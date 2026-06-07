<?php

namespace App\Filament\Widgets\Merchant;

use App\Models\PosSale;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantSalesTrendChart extends ApexChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $chartId = 'merchantSalesTrendChart';

    protected static ?string $heading = 'اتجاه المبيعات';

    protected static ?string $subheading = 'إجمالي مبيعات نقطة البيع خلال آخر 12 شهراً';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
    ];

    protected function getOptions(): array
    {
        $teamId = Filament::getTenant()?->id;
        $months = collect();
        $data = collect();

        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push($arabicMonths[$month->month].' '.$month->year);

            $total = (float) PosSale::query()
                ->where('team_id', $teamId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');

            $data->push(round($total, 2));
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 320,
                'toolbar' => ['show' => true],
            ],
            'series' => [
                [
                    'name' => 'المبيعات',
                    'data' => $data->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $months->toArray(),
                'labels' => [
                    'style' => ['fontFamily' => 'inherit', 'fontWeight' => 600],
                ],
            ],
            'colors' => ['#f59e0b'],
            'stroke' => ['curve' => 'smooth', 'width' => 3],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 0.4,
                    'opacityFrom' => 0.5,
                    'opacityTo' => 0.05,
                ],
            ],
            'dataLabels' => ['enabled' => false],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
        {
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val.toFixed(2) + ' ر.س'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2) + ' ر.س'
                    }
                }
            }
        }
        JS);
    }
}
