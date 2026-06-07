<?php

namespace App\Filament\Resources\Accounts\Pages;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Enums\UserType;
use App\Models\Account;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Wsmallnews\FilamentNestedset\Pages\NestedsetPage;

class ManageAccountsTree extends NestedsetPage
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'شجرة الحسابات';

    protected static ?string $modelLabel = 'حساب';

    protected static ?string $pluralModelLabel = 'شجرة الحسابات';

    protected static ?string $slug = 'accounts';

    protected static string $recordTitleAttribute = 'name';

    protected static ?int $level = 2;

    protected static ?string $emptyLabel = 'لا توجد حسابات';

    protected static ?string $emptyTipLabel = 'ابدأ بإنشاء مجموعة حسابات جديدة';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->hasAnyRole([UserType::MERCHANT]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return 'شجرة الحسابات';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'المحاسبة';
    }

    public function getRecordLabel(Model $record): HtmlString|string
    {
        $status = $record->is_active ? '' : ' — معطل';

        return "{$record->code} — {$record->name}{$status}";
    }

    public function canBeDeleted(Model $record): bool
    {
        if ($record->is_system) {
            return false;
        }

        return parent::canBeDeleted($record);
    }

    protected function schema(array $arguments): array
    {
        return [
            TextInput::make('code')
                ->label('الرمز')
                ->required()
                ->maxLength(20)
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('team_id', filament()->getTenant()?->id);
                }),
            TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('النوع')
                ->options(AccountType::options())
                ->required()
                ->native(false),
            Select::make('normal_balance')
                ->label('الرصيد الطبيعي')
                ->options([
                    NormalBalance::DEBIT->value => NormalBalance::DEBIT->arabicLabel(),
                    NormalBalance::CREDIT->value => NormalBalance::CREDIT->arabicLabel(),
                ])
                ->required()
                ->native(false),
            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
            Textarea::make('description')
                ->label('الوصف')
                ->columnSpanFull(),
        ];
    }

    protected function editSchema(array $arguments): array
    {
        $record = isset($arguments['id'])
            ? $this->getQuery()->find($arguments['id'])
            : null;

        $isSystem = $record?->is_system ?? false;

        return [
            TextInput::make('code')
                ->label('الرمز')
                ->required()
                ->maxLength(20)
                ->disabled($isSystem)
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('team_id', filament()->getTenant()?->id);
                }),
            TextInput::make('name')
                ->label('الاسم')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('النوع')
                ->options(AccountType::options())
                ->required()
                ->disabled($isSystem)
                ->native(false),
            Select::make('normal_balance')
                ->label('الرصيد الطبيعي')
                ->options([
                    NormalBalance::DEBIT->value => NormalBalance::DEBIT->arabicLabel(),
                    NormalBalance::CREDIT->value => NormalBalance::CREDIT->arabicLabel(),
                ])
                ->required()
                ->disabled($isSystem)
                ->native(false),
            Toggle::make('is_active')
                ->label('نشط')
                ->default(true),
            Textarea::make('description')
                ->label('الوصف')
                ->columnSpanFull(),
        ];
    }

    protected function infolistSchema(): array
    {
        return [
            TextEntry::make('type')
                ->label('النوع')
                ->formatStateUsing(fn ($state) => $state?->arabicLabel()),
            TextEntry::make('normal_balance')
                ->label('الرصيد الطبيعي')
                ->formatStateUsing(fn ($state) => $state?->arabicLabel()),
            IconEntry::make('is_system')
                ->label('حساب نظام')
                ->boolean(),
            IconEntry::make('is_active')
                ->label('نشط')
                ->boolean(),
        ];
    }
}
