<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ManageFinancialSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'الإعدادات المالية';

    protected static ?string $title = 'الإعدادات المالية';

    protected ?string $heading = 'إدارة إعداداتك المالية';

    protected ?string $subheading = 'حدد راتبك وحدودك المالية للحصول على تنبيهات ومراقبة أفضل';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function getView(): string
    {
        return 'filament.pages.manage-financial-settings';
    }

    public function mount(): void
    {
        $user = Auth::user();
        
        $this->form->fill([
            'salary' => $user->salary,
            'min_spending_limit' => $user->min_spending_limit,
            'max_spending_limit' => $user->max_spending_limit,
            'max_debt_limit' => $user->max_debt_limit,
            'debt_warning_percentage' => $user->debt_warning_percentage ?? 50,
            'debt_danger_percentage' => $user->debt_danger_percentage ?? 80,
        ]);
    }

    public function form($form)
    {
        return $form
            ->schema([
               Section::make('معلومات الراتب')
                ->description('أدخل راتبك الشهري لحساب نسب المخاطر المالية بدقة')
                ->schema([
                    TextInput::make('salary')
                        ->label('الراتب الشهري')
                        ->numeric()
                        ->prefix('ريال')
                        ->minValue(0)
                        ->step(0.01)
                        ->helperText('راتبك الشهري الصافي')
                        ->required(),
                ])
                ->columns(1),

                Section::make('حدود المشتريات')
                    ->description('حدد الحد الأدنى والأقصى لقيمة المشتريات في الطلب الواحد')
                    ->schema([
                        TextInput::make('min_spending_limit')
                            ->label('الحد الأدنى للمشتريات')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم إشعارك إذا كانت قيمة الطلب أقل من هذا الحد'),
                        
                        TextInput::make('max_spending_limit')
                            ->label('الحد الأقصى للمشتريات')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم تنبيهك إذا تجاوزت قيمة الطلب هذا الحد'),
                    ])
                    ->columns(2),

                Section::make('حدود الديون')
                    ->description('حدد الحد الأقصى للديون ونسب التحذير')
                    ->schema([
                        TextInput::make('max_debt_limit')
                            ->label('الحد الأقصى للديون (بالريال)')
                            ->numeric()
                            ->prefix('ريال')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('سيتم تنبيهك إذا تجاوزت ديونك الإجمالية هذا الحد'),
                        
                        TextInput::make('debt_warning_percentage')
                            ->label('نسبة التحذير من الراتب')
                            ->numeric()
                            ->suffix('%')
                            ->default(50)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->helperText('سيتم إرسال تحذير عندما تصل الديون لهذه النسبة من راتبك')
                            ->required(),
                        
                        TextInput::make('debt_danger_percentage')
                            ->label('نسبة الخطر من الراتب')
                            ->numeric()
                            ->suffix('%')
                            ->default(80)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(1)
                            ->helperText('سيتم إرسال تنبيه خطر عندما تصل الديون لهذه النسبة من راتبك')
                            ->required(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon('heroicon-o-check')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $user = Auth::user();
            
            $user->fill($data);
            $user->save();

            Notification::make()
                ->title('تم حفظ الإعدادات بنجاح')
                ->success()
                ->body('تم تحديث إعداداتك المالية')
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في حفظ الإعدادات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}

