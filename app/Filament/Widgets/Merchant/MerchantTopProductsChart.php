<?php

namespace App\Filament\Widgets\Merchant;

use App\Models\PosSale;
use App\Models\PosSaleItem;
use Filament\Facades\Filament;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantTopProductsChart extends ApexChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $chartId = 'merchantTopProductsChart';

    protected static ?string $heading = 'أفضل المنتجات مبيعاً';

    protected static ?string $subheading = 'أعلى 10 منتجات حسب إجمالي الإيرادات';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 2,
    ];

    protected function getOptions(): array
    {
        $teamId = Filament::getTenant()?->id;

        $saleIds = PosSale::query()
            ->where('team_id', $teamId)
            ->pluck('id');

        $topProducts = PosSaleItem::query()
            ->select('product_name', DB::raw('SUM(total) as revenue'))
            ->whereIn('pos_sale_id', $saleIds)
            ->groupBy('product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => ['show' => true],
            ],
            'series' => [
                [
                    'name' => 'الإيرادات',
                    'data' => $topProducts->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => $topProducts->pluck('product_name')->toArray(),
                'labels' => [
                    'rotate' => -45,
                    'style' => ['fontFamily' => 'inherit', 'fontWeight' => 600],
                ],
            ],
            'colors' => ['#6366f1'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'horizontal' => true,
                ],
            ],
            'dataLabels' => ['enabled' => false],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        $symbol = \App\Helpers\CurrencyHelper::getSymbol();
        return RawJs::make(<<<JS
        {
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val.toFixed(2) + ' {$symbol}'
                    }
                }
            }
        }
JS);
    }
}
