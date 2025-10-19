<?php

namespace App\Filament\Resources\UserMerchants\Pages;

use App\Models\UserMerchant;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Filament\Resources\UserMerchants\UserMerchantResource;
use App\Filament\Resources\UserMerchants\Pages\Widgets\MerchantDebtChart;
use App\Filament\Resources\UserMerchants\Pages\Widgets\MerchantRiskGaugeChart;
use App\Filament\Resources\UserMerchants\Pages\Widgets\MerchantFinancialOverview;
use App\Filament\Resources\UserMerchants\Pages\Widgets\MerchantSpendingTrendChart;

class MerchantFinancialStats extends Page
{
    use InteractsWithRecord;

    protected static string $resource = UserMerchantResource::class;

    protected static ?string $title = 'الإحصائيات المالية';

    public function getView(): string
    {
        return 'filament.resources.user-merchants.pages.merchant-financial-stats';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        return 'الإحصائيات المالية - ' . $this->record?->name;
    }

    public function getHeading(): string|Htmlable
    {
        return 'الإحصائيات المالية';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'مراقبة مشترياتك وديونك مع ' . $this->record?->name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MerchantFinancialOverview::class,
            MerchantDebtChart::class,
            MerchantSpendingTrendChart::class,
            MerchantRiskGaugeChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    protected function getHeaderWidgetsData(): array
    {
        return [
            'merchantId' => $this->record?->id,
        ];
    }
}

