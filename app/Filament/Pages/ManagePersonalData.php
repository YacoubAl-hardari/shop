<?php

namespace App\Filament\Pages;

use App\Exports\UserCompleteDataExport;
use App\Services\UserDataExportService;
use App\Services\UserDataDeletionService;
use App\Services\UserDataImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ManagePersonalData extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'إدارة البيانات الشخصية';

    protected static ?string $title = 'إدارة البيانات الشخصية';

    protected ?string $heading = 'إدارة بياناتك الشخصية';

    protected ?string $subheading = 'تصدير، استرجاع، أو حذف جميع بياناتك';

    protected static ?int $navigationSort = 100;

    public $importFile = null;
    public $deletePassword = '';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

 

    public function getView(): string
    {
        return 'filament.pages.manage-personal-data';
    }


    /**
     * Get header actions for the page
     */
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
                ->modalDescription('سيتم تصدير جميع بياناتك  . هل تريد المتابعة؟')
                ->modalSubmitActionLabel('نعم، صدّر البيانات'),
            
            Action::make('exportExcel')
                ->label('تصدير Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->action('exportToExcel')
                ->requiresConfirmation()
                ->modalHeading('تصدير إلى Excel')
                ->modalDescription('سيتم تصدير جميع بياناتك إلى ملف Excel بصفحات متعددة. هل تريد المتابعة؟')
                ->modalSubmitActionLabel('نعم، صدّر إلى Excel'),
        ];
    }

    /**
     * Export user data as JSON file
     */
    public function exportData()
    {
        try {
            $user = Auth::user();
            $exportService = app(UserDataExportService::class);
            
            $data = $exportService->exportUserData($user);
            
            $filename = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            
            // Store temporarily to allow download
            $path = 'temp/' . $filename;
            Storage::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            Notification::make()
                ->title('تم تصدير البيانات بنجاح')
                ->success()
                ->body('سيتم تنزيل ملف JSON الآن')
                ->send();

            // Return download response
            return response()->download(
                Storage::path($path),
                $filename,
                ['Content-Type' => 'application/json']
            )->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في تصدير البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Export user data to Excel file with multiple sheets
     */
    public function exportToExcel()
    {
        try {
            $user = Auth::user();
            
            $filename = 'user_complete_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            
            Notification::make()
                ->title('جاري تصدير البيانات')
                ->success()
                ->body('سيتم تنزيل ملف Excel بعد قليل...')
                ->send();

            return Excel::download(new UserCompleteDataExport($user), $filename);
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في تصدير البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Handle data import
     */
    public function importDataAction()
    {
        try {
            if (!$this->importFile) {
                Notification::make()
                    ->title('خطأ')
                    ->danger()
                    ->body('الرجاء اختيار ملف')
                    ->send();
                return;
            }

            $user = Auth::user();
            $importService = app(UserDataImportService::class);
            
            // Read the uploaded file using Livewire's temporary file path
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

            // Import data with validation and signature check
            $importService->importUserData($user, $jsonData);

            Notification::make()
                ->title('تم استرجاع البيانات بنجاح')
                ->success()
                ->body('تم استرجاع جميع بياناتك بنجاح')
                ->send();

            // Reset form
            $this->importFile = null;
            
            // Redirect to refresh the page
            $this->redirect(static::getUrl());
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في استرجاع البيانات')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    /**
     * Handle account deletion
     */
    public function deleteAccountAction()
    {
        try {
            if (!$this->deletePassword) {
                Notification::make()
                    ->title('خطأ')
                    ->danger()
                    ->body('الرجاء إدخال كلمة المرور')
                    ->send();
                return;
            }

            $user = Auth::user();
            
            // Verify password
            if (!Hash::check($this->deletePassword, $user->password)) {
                Notification::make()
                    ->title('كلمة مرور خاطئة')
                    ->danger()
                    ->body('الرجاء إدخال كلمة المرور الصحيحة')
                    ->send();
                return;
            }

            $deletionService = app(UserDataDeletionService::class);
            
            // Delete user account
            $deletionService->deleteUserAccount($user);

            Notification::make()
                ->title('تم حذف الحساب')
                ->success()
                ->body('تم حذف حسابك وجميع بياناتك بنجاح')
                ->send();

            // Logout and redirect
            Auth::logout();
            $this->redirect('/');
            
        } catch (\Exception $e) {
            Notification::make()
                ->title('خطأ في حذف الحساب')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
}
