<?php

namespace App\Filament\Pages;

use App\Enums\UserType;
use App\Exports\TeamCompleteDataExport;
use App\Filament\Concerns\HasRoleAccess;
use App\Services\TeamDataDeletionService;
use App\Services\TeamDataExportService;
use App\Services\TeamDataImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManageMerchantData extends Page implements HasForms
{
    use HasRoleAccess;
    use InteractsWithForms;

    protected static function allowedRoles(): array
    {
        return [UserType::MERCHANT, UserType::ADMIN];
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'إدارة البيانات';

    protected static ?string $title = 'إدارة البيانات';

    protected ?string $heading = 'إدارة بيانات التاجر';

    protected ?string $subheading = 'تصدير، استرجاع، أو حذف جميع بيانات الفرع التجارية';

    protected static ?int $navigationSort = 100;

    public $importFile = null;

    public $deletePassword = '';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function getView(): string
    {
        return 'filament.pages.manage-merchant-data';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('تصدير قاعدة البيانات')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action('exportData')
                ->requiresConfirmation()
                ->modalHeading('تصدير البيانات')
                ->modalDescription('سيتم تصدير جميع بيانات الفرع التجارية (JSON). هل تريد المتابعة؟')
                ->modalSubmitActionLabel('نعم، صدّر البيانات'),

            Action::make('exportExcel')
                ->label('تصدير Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action('exportToExcel')
                ->requiresConfirmation()
                ->modalHeading('تصدير إلى Excel')
                ->modalDescription('سيتم تصدير جميع بيانات الفرع إلى ملف Excel بصفحات متعددة. هل تريد المتابعة؟')
                ->modalSubmitActionLabel('نعم، صدّر إلى Excel'),
        ];
    }

    public function exportData()
    {
        try {
            $team = Filament::getTenant();
            $data = app(TeamDataExportService::class)->exportTeamData($team);

            $filename = 'merchant_data_'.$team->slug.'_'.now()->format('Y-m-d_H-i-s').'.json';
            $path = 'temp/'.$filename;
            Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Notification::make()
                ->title('تم تصدير البيانات بنجاح')
                ->success()
                ->body('سيتم تنزيل ملف JSON الآن')
                ->send();

            return response()->download(
                Storage::path($path),
                $filename,
                ['Content-Type' => 'application/json'],
            )->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في تصدير البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function exportToExcel()
    {
        try {
            $team = Filament::getTenant();
            $filename = 'merchant_complete_data_'.$team->slug.'_'.now()->format('Y-m-d_H-i-s').'.xlsx';

            Notification::make()
                ->title('جاري تصدير البيانات')
                ->success()
                ->body('سيتم تنزيل ملف Excel بعد قليل...')
                ->send();

            return Excel::download(new TeamCompleteDataExport($team), $filename);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في تصدير البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function importDataAction(): void
    {
        try {
            if (! $this->importFile) {
                Notification::make()
                    ->title('خطأ')
                    ->danger()
                    ->body('الرجاء اختيار ملف')
                    ->send();

                return;
            }

            $team = Filament::getTenant();
            $jsonContent = file_get_contents($this->importFile->getRealPath());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Notification::make()
                    ->title('خطأ في الملف')
                    ->danger()
                    ->body('الملف غير صالح أو تالف')
                    ->send();

                return;
            }

            app(TeamDataImportService::class)->importTeamData($team, Auth::user(), $jsonData);

            Notification::make()
                ->title('تم استرجاع البيانات بنجاح')
                ->success()
                ->body('تم استرجاع جميع بيانات الفرع التجارية بنجاح')
                ->send();

            $this->importFile = null;
            $this->redirect(static::getUrl());
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في استرجاع البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    public function deleteDataAction(): void
    {
        try {
            if (! $this->deletePassword) {
                Notification::make()
                    ->title('خطأ')
                    ->danger()
                    ->body('الرجاء إدخال كلمة المرور')
                    ->send();

                return;
            }

            $user = Auth::user();

            if (! Hash::check($this->deletePassword, $user->password)) {
                Notification::make()
                    ->title('كلمة مرور خاطئة')
                    ->danger()
                    ->body('الرجاء إدخال كلمة المرور الصحيحة')
                    ->send();

                return;
            }

            $team = Filament::getTenant();
            app(TeamDataDeletionService::class)->deleteTeamBusinessData($team);

            Notification::make()
                ->title('تم حذف البيانات')
                ->success()
                ->body('تم حذف جميع بيانات الفرع التجارية بنجاح')
                ->send();

            $this->deletePassword = '';
            $this->redirect(static::getUrl());
        } catch (\Throwable $e) {
            Notification::make()
                ->title('خطأ في حذف البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}
