<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات القيد')
                ->schema([
                    DatePicker::make('entry_date')
                        ->label('تاريخ القيد')
                        ->required()
                        ->default(now()),

                    Textarea::make('description')
                        ->label('الوصف')
                        ->required()
                        ->columnSpanFull(),
                ]),

            Section::make('سطور القيد')
                ->schema([
                    Repeater::make('lines')
                        ->label('السطور')
                        ->schema([
                            Select::make('account_id')
                                ->label('الحساب')
                                ->options(fn () => Account::query()
                                    ->where('is_active', true)
                                    ->whereDoesntHave('children')
                                    ->orderBy('code')
                                    ->pluck('name', 'id')
                                    ->map(fn ($name, $id) => Account::find($id)?->code.' - '.$name)
                                    ->all())
                                ->searchable()
                                ->required(),

                            TextInput::make('debit_amount')
                                ->label('مدين')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            TextInput::make('credit_amount')
                                ->label('دائن')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),

                            TextInput::make('description')
                                ->label('وصف السطر'),
                        ])
                        ->columns(4)
                        ->minItems(2)
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('balance_check')
                        ->label('التوازن')
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(function (Get $get): string {
                            $lines = $get('lines') ?? [];
                            $debit = collect($lines)->sum(fn ($line) => (float) ($line['debit_amount'] ?? 0));
                            $credit = collect($lines)->sum(fn ($line) => (float) ($line['credit_amount'] ?? 0));

                            return 'مدين: '.number_format($debit, 2).' | دائن: '.number_format($credit, 2)
                                .(bccomp((string) $debit, (string) $credit, 2) === 0 ? ' ✓ متوازن' : ' ✗ غير متوازن');
                        }),
                ]),
        ]);
    }
}
