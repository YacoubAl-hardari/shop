<?php

namespace App\Filament\Resources\UserMerchants\Pages\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantRiskGaugeChart extends ApexChartWidget
{
    protected static ?string $chartId = 'merchantRiskGaugeChart';
    
    protected static ?string $heading = 'مؤشر مستوى المخاطر';

    public ?Model $record = null; 

    protected function getOptions(): array
    {
        $merchant = $this->record;
        
        if (!$merchant) {
            return [];
        }

        $user = Auth::user();

        if (!$merchant || $merchant->user_id !== $user->id) {
            return [];
        }

        // Current debt
        $currentDebt = (float) $merchant->balance;
        
        // Calculate debt ratio
        $debtRatio = 0;
        $salary = $user->salary ?? 0;
        
        if ($salary > 0) {
            $debtRatio = ($currentDebt / $salary) * 100;
        }

        // Define thresholds
        $warningThreshold = $user->debt_warning_percentage ?? 50;
        $dangerThreshold = $user->debt_danger_percentage ?? 80;

        // Calculate segments
        $safeZone = min($debtRatio, $warningThreshold);
        $warningZone = max(0, min($debtRatio - $warningThreshold, $dangerThreshold - $warningThreshold));
        $dangerZone = max(0, $debtRatio - $dangerThreshold);

        // Determine status
        $status = 'آمن';
        
        if ($debtRatio >= $dangerThreshold) {
            $status = 'خطر';
        } elseif ($debtRatio >= $warningThreshold) {
            $status = 'تحذير';
        }

        $series = [];
        $labels = [];
        $colors = [];

        if ($safeZone > 0) {
            $series[] = round($safeZone, 1);
            $labels[] = 'آمن';
            $colors[] = '#22c55e';
        }

        if ($warningZone > 0) {
            $series[] = round($warningZone, 1);
            $labels[] = 'تحذير';
            $colors[] = '#eab308';
        }

        if ($dangerZone > 0) {
            $series[] = round($dangerZone, 1);
            $labels[] = 'خطر';
            $colors[] = '#ef4444';
        }

        // If no data, show safe zone
        if (empty($series)) {
            $series = [0];
            $labels = ['لا توجد بيانات'];
            $colors = ['#e5e7eb'];
        }

        return [
            'chart' => [
                'type' => 'radialBar',
                'height' => 350,
            ],
            'series' => [round($debtRatio, 1)],
            'plotOptions' => [
                'radialBar' => [
                    'startAngle' => -90,
                    'endAngle' => 90,
                    'track' => [
                        'background' => '#e5e7eb',
                        'strokeWidth' => '97%',
                    ],
                    'dataLabels' => [
                        'name' => [
                            'show' => true,
                            'fontSize' => '16px',
                            'offsetY' => -10,
                        ],
                        'value' => [
                            'show' => true,
                            'fontSize' => '30px',
                            'fontWeight' => 700,
                            'offsetY' => -50,
                        ],
                    ],
                    'hollow' => [
                        'size' => '70%',
                    ],
                ],
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'horizontal',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => [$debtRatio >= $dangerThreshold ? '#ef4444' : ($debtRatio >= $warningThreshold ? '#eab308' : '#22c55e')],
                    'stops' => [0, 100],
                ],
            ],
            'stroke' => [
                'lineCap' => 'round',
            ],
            'labels' => [$status],
            'colors' => [$debtRatio >= $dangerThreshold ? '#ef4444' : ($debtRatio >= $warningThreshold ? '#eab308' : '#22c55e')],
            'tooltip' => [
                'enabled' => true,
                'y' => [
                    'formatter' => 'function(val) { return val.toFixed(1) + "%"; }',
                ],
            ],
            'title' => [
                'text' => sprintf(
                    'الديون: %s ريال | الراتب: %s ريال',
                    number_format($currentDebt, 2),
                    $salary > 0 ? number_format($salary, 2) : 'غير محدد'
                ),
                'align' => 'center',
                'style' => [
                    'fontSize' => '14px',
                    'fontFamily' => 'inherit',
                ],
                'offsetY' => 20,
            ],
            'subtitle' => [
                'text' => sprintf('حد التحذير: %d%% | حد الخطر: %d%%', $warningThreshold, $dangerThreshold),
                'align' => 'center',
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'inherit',
                ],
                'offsetY' => 40,
            ],
        ];
    }
}

