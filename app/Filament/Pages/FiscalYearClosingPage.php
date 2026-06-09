<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Filament\Concerns\HasRoleAccess;
use App\Models\FiscalYearClosing;
use App\Services\FiscalYearClosingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class FiscalYearClosingPage extends Page implements HasForms
{
    use HasRoleAccess;
    use InteractsWithForms;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;
    protected static ?string $navigationLabel = 'الإغلاق السنوي';
    protected static ?string $title = 'الإغلاق المحاسبي السنوي';
    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.fiscal-year-closing';

    public ?array $data = [];
    public ?FiscalYearClosing $preview = null;

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة';
    }

    public function mount(): void
    {
        $this->form->fill(['fiscal_year' => now()->year - 1]);
    }

    public function form($form)
    {
        return $form
            ->schema([
                Select::make('fiscal_year')
                    ->label('السنة المالية للإغلاق')
                    ->options(function (): array {
                        $years = [];
                        for ($y = now()->year; $y >= now()->year - 5; $y--) {
                            $years[$y] = $y;
                        }
                        return $years;
                    })
                    ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات الإغلاق')
                    ->rows(2),
            ])
            ->statePath('data');
    }

    public function previewClosing(): void
    {
        $data = $this->form->getState();
        $team = Filament::getTenant();

        try {
            $this->preview = app(FiscalYearClosingService::class)
                ->prepare($team, (int) $data['fiscal_year']);

            Notification::make()
                ->title('تم حساب الأرباح والمصروفات')
                ->body('صافي الدخل: '.number_format($this->preview->net_income, 2).' ر.س')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ')->body($e->getMessage())->danger()->send();
        }
    }

    public function postClosing(): void
    {
        if (! $this->preview) {
            Notification::make()->title('يجب معاينة الإغلاق أولاً')->warning()->send();
            return;
        }

        $team = Filament::getTenant();

        try {
            $result = app(FiscalYearClosingService::class)
                ->post($this->preview, $team);

            $this->preview = null;
            $this->form->fill(['fiscal_year' => now()->year - 1]);

            Notification::make()
                ->title('تم ترحيل الإغلاق السنوي بنجاح')
                ->body('رقم القيد: '.$result->journalEntry?->entry_number)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ في الإغلاق')->body($e->getMessage())->danger()->send();
        }
    }

    public function repostClosing(int $closingId): void
    {
        $closing = FiscalYearClosing::findOrFail($closingId);
        $team = Filament::getTenant();

        try {
            $result = app(FiscalYearClosingService::class)->post($closing, $team);

            Notification::make()
                ->title('تم إعادة ترحيل الإغلاق السنوي بنجاح')
                ->body('رقم القيد الجديد: '.$result->journalEntry?->entry_number)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('خطأ في الإغلاق')->body($e->getMessage())->danger()->send();
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('معاينة الإغلاق')
                ->icon(Heroicon::OutlinedEye)
                ->color('info')
                ->action(fn () => $this->previewClosing()),

            Action::make('post')
                ->label('ترحيل الإغلاق السنوي')
                ->icon(Heroicon::OutlinedLockClosed)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('تأكيد الإغلاق السنوي')
                ->modalDescription('سيتم إقفال جميع حسابات الإيرادات والمصروفات ونقل صافي الدخل إلى الأرباح المحتجزة. هذا الإجراء لا يمكن التراجع عنه.')
                ->visible(fn () => $this->preview !== null)
                ->action(fn () => $this->postClosing()),
        ];
    }
}
