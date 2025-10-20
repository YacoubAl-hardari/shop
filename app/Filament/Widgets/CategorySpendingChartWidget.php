<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BudgetCategoryRepository;

class CategorySpendingChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;


    public function getHeading(): ?string
    {
        return 'الإنفاق حسب الفئة';
    }

    protected function getData(): array
    {
        $categoryRepo = app(BudgetCategoryRepository::class);
        $categories = $categoryRepo->getSpendingByCategory(Auth::id());

        if ($categories->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => 'الإنفاق',
                        'data' => [0],
                        'backgroundColor' => ['#e5e7eb'],
                    ]
                ],
                'labels' => ['لا توجد فئات'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإنفاق (ريال)',
                    'data' => $categories->pluck('spent_amount')->toArray(),
                    'backgroundColor' => $categories->pluck('color')->toArray(),
                ]
            ],
            'labels' => $categories->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}

