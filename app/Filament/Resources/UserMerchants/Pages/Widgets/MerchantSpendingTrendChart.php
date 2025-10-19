<?php

namespace App\Filament\Resources\UserMerchants\Pages\Widgets;

use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;

class MerchantSpendingTrendChart extends ApexChartWidget
{
    protected static ?string $chartId = 'merchantSpendingTrendChart';
    
    protected static ?string $heading = 'اتجاه المشتريات الشهرية';

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

        // Get monthly orders for the last 12 months
        $startDate = now('Asia/Riyadh')->subMonths(11)->startOfMonth();
        
        $monthlyOrders = UserMerchantOrder::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_price) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Build data for last 12 months
        $categories = [];
        $spendingData = [];
        $countData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now('Asia/Riyadh')->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->locale('ar')->translatedFormat('M Y');
            
            $monthData = $monthlyOrders->get($monthKey);
            
            $categories[] = $monthLabel;
            $spendingData[] = $monthData ? round((float) $monthData->total, 2) : 0;
            $countData[] = $monthData ? (int) $monthData->count : 0;
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'إجمالي المشتريات (ريال)',
                    'type' => 'column',
                    'data' => $spendingData,
                ],
                [
                    'name' => 'عدد السجلات',
                    'type' => 'line',
                    'data' => $countData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
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
                        'text' => 'المبلغ (ريال)',
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
                        'text' => 'عدد السجلات',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#3b82f6', '#10b981'],
            'stroke' => [
                'width' => [0, 4],
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
            ],
        ];
    }
}

