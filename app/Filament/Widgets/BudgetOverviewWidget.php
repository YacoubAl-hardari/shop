<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BudgetRepository;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class BudgetOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $budgetRepo = app(BudgetRepository::class);
        $activeBudget = $budgetRepo->getActiveBudget(Auth::id());

        if (!$activeBudget) {
            return [
                Stat::make('لا توجد ميزانية نشطة', 'قم بإنشاء ميزانية لتتبع إنفاقك')
                    ->description('ابدأ الآن')
                    ->descriptionIcon('heroicon-o-plus-circle')
                    ->color('gray')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),
            ];
        }

        $percentage = $activeBudget->spent_percentage;
        $daysRemaining = $activeBudget->end_date->diffInDays(now());

        return [
            Stat::make('حد الميزانية', number_format($activeBudget->total_limit, 2) . ' ريال')
                ->description($activeBudget->name)
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success')
                ->chart([
                    $activeBudget->total_limit,
                    $activeBudget->spent_amount,
                    $activeBudget->remaining_amount,
                ]),

            Stat::make('المبلغ المصروف', number_format($activeBudget->spent_amount, 2) . ' ريال')
                ->description(
                    $percentage >= 100
                        ? '⚠️ تجاوزت الميزانية!'
                        : ($percentage >= 80 ? '⚠️ اقتربت من الحد' : 'ضمن الميزانية')
                )
                ->descriptionIcon(
                    $percentage >= 100
                        ? 'heroicon-o-exclamation-circle'
                        : ($percentage >= 80 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                )
                ->color(
                    $percentage >= 100
                        ? 'danger'
                        : ($percentage >= 80 ? 'warning' : 'success')
                )
                ->chart([0, $activeBudget->spent_amount]),

            Stat::make('المبلغ المتبقي', number_format($activeBudget->remaining_amount, 2) . ' ريال')
                ->description($daysRemaining . ' يوم متبقي')
                ->descriptionIcon('heroicon-o-calendar')
                ->color($activeBudget->remaining_amount > 0 ? 'success' : 'danger')
                ->chart([
                    $activeBudget->remaining_amount,
                    max(0, $activeBudget->remaining_amount - 100),
                ]),

            Stat::make('نسبة الإنفاق', $percentage . '%')
                ->description(
                    $percentage >= 100
                        ? 'تجاوزت بـ ' . number_format($activeBudget->spent_amount - $activeBudget->total_limit, 2) . ' ريال'
                        : 'من أصل 100%'
                )
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color(
                    $percentage >= 100
                        ? 'danger'
                        : ($percentage >= 80 ? 'warning' : 'success')
                ),
        ];
    }
}

