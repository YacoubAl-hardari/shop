<?php

namespace App\Filament\Widgets\Merchant;

use App\Enums\SalePaymentType;
use App\Models\PosSale;
use Filament\Facades\Filament;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantPaymentMixChart extends ApexChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $chartId = 'merchantPaymentMixChart';

    protected static ?string $heading = 'توزيع طرق الدفع';

    protected static ?string $subheading = 'نسبة المبيعات حسب نوع الدفع (هذا الشهر)';

    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $teamId = Filament::getTenant()?->id;

        $totals = PosSale::query()
            ->where('team_id', $teamId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('payment_type, SUM(total_amount) as total')
            ->groupBy('payment_type')
            ->pluck('total', 'payment_type');

        $labels = [];
        $series = [];

        foreach (SalePaymentType::cases() as $type) {
            $amount = (float) ($totals[$type->value] ?? 0);

            if ($amount > 0) {
                $labels[] = $type->arabicLabel();
                $series[] = round($amount, 2);
            }
        }

        if ($series === []) {
            $labels = ['لا توجد مبيعات'];
            $series = [0];
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => ['#22c55e', '#ef4444', '#3b82f6'],
            'legend' => [
                'position' => 'bottom',
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                    ],
                ],
            ],
        ];
    }
}
