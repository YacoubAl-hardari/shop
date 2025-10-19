<?php

namespace App\Filament\Resources\UserMerchants\Pages\Widgets;

use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\PaymentTransactionStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserMerchantPaymentTransaction;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MerchantDebtChart extends ApexChartWidget
{
    protected static ?string $chartId = 'merchantDebtChart';
    
    protected static ?string $heading = 'تطور الديون مع التاجر';

    public ?Model $record = null; 

    protected function getOptions(): array
    {
        $merchant = $this->record;
        
        if (!$merchant) {
            return [];
        }
        
        $user = Auth::user();

        // Get orders and payments for the last 12 months
        $startDate = now('Asia/Riyadh')->subMonths(11)->startOfMonth();
        
        // Get monthly orders (debt increases)
        $monthlyOrders = UserMerchantOrder::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        // Get monthly payments (debt decreases)
        $monthlyPayments = UserMerchantPaymentTransaction::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');


        // Build data for last 12 months
        $categories = [];
        $debtData = [];
        $runningDebt = 0;

        // Calculate initial debt (before our date range)
        $initialOrders = (float) UserMerchantOrder::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('created_at', '<', $startDate)
            ->sum('total_price');
        
        $initialPayments = (float) UserMerchantPaymentTransaction::where('user_merchant_id', $merchant->id)
            ->where('user_id', $user->id)
            ->where('status', PaymentTransactionStatus::COMPLETED->value)
            ->where('created_at', '<', $startDate)
            ->sum('amount');
        
        $runningDebt = $initialOrders - $initialPayments;

        for ($i = 11; $i >= 0; $i--) {
            $date = now('Asia/Riyadh')->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->locale('ar')->translatedFormat('M Y');
            
            $orders = (float) ($monthlyOrders[$monthKey] ?? 0);
            $payments = (float) ($monthlyPayments[$monthKey] ?? 0);
            
            $runningDebt += $orders - $payments;
            
            $categories[] = $monthLabel;
            $debtData[] = round($runningDebt, 2);
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'الديون المتراكمة',
                    'data' => $debtData,
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
                'title' => [
                    'text' => 'المبلغ (ريال)',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#ef4444'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function(val) { return val.toFixed(2) + " ريال"; }',
                ],
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

