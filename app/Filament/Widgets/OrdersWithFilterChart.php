<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Support\RawJs;
use App\Models\UserMerchant;
use Filament\Schemas\Schema;
use App\Models\UserMerchantOrder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Filament\Widgets\ChartWidget\Concerns\HasFiltersSchema;

class OrdersWithFilterChart extends ApexChartWidget
{
    use HasFiltersSchema;

    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'ordersWithFilterChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'تحليل الطلبات';

    /**
     * Widget Subheading
     *
     * @var string|null
     */
    protected static ?string $subheading = 'تصفية الطلبات حسب النطاق الزمني والتاجر';

    /**
     * Define filter form schema
     */
    public function filtersSchema(Schema $schema): Schema
    {
        $userId = Auth::id();
        
        // Get user's merchants for the dropdown
        $merchants = UserMerchant::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();

        return $schema->components([
            DatePicker::make('date_start')
                ->label('تاريخ البداية')
                ->default(Carbon::now()->subMonths(3)),

            DatePicker::make('date_end')
                ->label('تاريخ النهاية')
                ->default(Carbon::now()),

            Select::make('merchant_id')
                ->label('التاجر')
                ->options($merchants)
                ->placeholder('جميع التجار'),
        ]);
    }

    /**
     * Update chart when filter changes
     */
    public function updatedInteractsWithSchemas(string $statePath): void
    {
        $this->updateOptions();
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $userId = Auth::id();
        
        // Get filter values
        $dateStart = $this->filters['date_start'] ?? Carbon::now()->subMonths(3);
        $dateEnd = $this->filters['date_end'] ?? Carbon::now();
        $merchantId = $this->filters['merchant_id'] ?? null;

        // Parse dates if they're strings
        if (is_string($dateStart)) {
            $dateStart = Carbon::parse($dateStart);
        }
        if (is_string($dateEnd)) {
            $dateEnd = Carbon::parse($dateEnd);
        }

        // Build query
        $query = UserMerchantOrder::whereHas('userMerchant', function ($query) use ($userId, $merchantId) {
            $query->where('user_id', $userId);
            if ($merchantId) {
                $query->where('id', $merchantId);
            }
        })
        ->whereBetween('created_at', [$dateStart, $dateEnd]);

        // Group by day, week, or month based on date range
        $diffInDays = $dateStart->diffInDays($dateEnd);
        
        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
        
        $categories = collect();
        $ordersData = collect();
        $revenueData = collect();

        if ($diffInDays <= 31) {
            // Daily view for ranges up to 1 month
            $currentDate = $dateStart->copy();
            while ($currentDate <= $dateEnd) {
                $categories->push($arabicMonths[$currentDate->month] . ' ' . $currentDate->day);
                
                $dayOrders = (clone $query)
                    ->whereDate('created_at', $currentDate)
                    ->get();
                
                $ordersData->push($dayOrders->count());
                $revenueData->push(round($dayOrders->sum('total_price'), 2));
                
                $currentDate->addDay();
            }
        } elseif ($diffInDays <= 90) {
            // Weekly view for ranges up to 3 months
            $currentDate = $dateStart->copy()->startOfWeek();
            while ($currentDate <= $dateEnd) {
                $weekEnd = $currentDate->copy()->endOfWeek();
                $categories->push($arabicMonths[$currentDate->month] . ' ' . $currentDate->day);
                
                $weekOrders = (clone $query)
                    ->whereBetween('created_at', [$currentDate, $weekEnd])
                    ->get();
                
                $ordersData->push($weekOrders->count());
                $revenueData->push(round($weekOrders->sum('total_price'), 2));
                
                $currentDate->addWeek();
            }
        } else {
            // Monthly view for longer ranges
            $currentDate = $dateStart->copy()->startOfMonth();
            while ($currentDate <= $dateEnd) {
                $monthEnd = $currentDate->copy()->endOfMonth();
                $categories->push($arabicMonths[$currentDate->month] . ' ' . $currentDate->year);
                
                $monthOrders = (clone $query)
                    ->whereYear('created_at', $currentDate->year)
                    ->whereMonth('created_at', $currentDate->month)
                    ->get();
                
                $ordersData->push($monthOrders->count());
                $revenueData->push(round($monthOrders->sum('total_price'), 2));
                
                $currentDate->addMonth();
            }
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
                'categories' => $categories->toArray(),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                        'fontWeight' => 600,
                    ],
                    'rotate' => -45,
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

