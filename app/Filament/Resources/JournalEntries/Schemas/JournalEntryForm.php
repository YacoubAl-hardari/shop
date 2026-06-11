<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\Account;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([

            // ── قسم 1: بيانات القيد ─────────────────────────────────
            Section::make('بيانات القيد')
                ->description('المعلومات الأساسية لقيد اليومية')
                ->icon(Heroicon::OutlinedDocumentText)
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('entry_date')
                            ->label('تاريخ القيد')
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->displayFormat('Y/m/d')
                            ->helperText('تاريخ تسجيل القيد المحاسبي'),

                        Textarea::make('description')
                            ->label('الوصف / البيان')
                            ->required()
                            ->rows(2)
                            ->placeholder('اكتب وصفاً موجزاً للقيد...')
                            ->helperText('يظهر في كشف الحسابات وتقارير الدفتر العام'),
                    ]),
                ])
                ->collapsible()
                ->columnSpanFull()
                ->compact(),

            // ── قسم 2: سطور القيد ───────────────────────────────────
            Section::make('سطور القيد')
                ->description('أدخل الأطراف المدينة والدائنة — يجب أن يتوازن مجموع المدين مع مجموع الدائن')
                ->icon(Heroicon::OutlinedTableCells)
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
                                    ->get()
                                    ->mapWithKeys(fn (Account $account) => [
                                        $account->id => $account->code . ' — ' . $account->name,
                                    ])
                                    ->all())
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('حساب دفتر الأستاذ')
                                ->columnSpan(2),

                            TextInput::make('debit_amount')
                                ->label('مدين (Dr)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->prefix('ر.س')
                                ->inputMode('decimal')
                                ->step(0.01)
                                ->live(onBlur: true)
                                ->extraInputAttributes(['class' => 'text-left font-mono'])
                                ->helperText('الطرف المدين'),

                            TextInput::make('credit_amount')
                                ->label('دائن (Cr)')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->prefix('ر.س')
                                ->inputMode('decimal')
                                ->step(0.01)
                                ->live(onBlur: true)
                                ->extraInputAttributes(['class' => 'text-left font-mono'])
                                ->helperText('الطرف الدائن'),

                            TextInput::make('description')
                                ->label('بيان السطر')
                                ->placeholder('تفصيل اختياري...')
                                ->columnSpan(2),
                        ])
                        ->columns(6)
                        ->minItems(2)
                        ->validationMessages([
                            'min' => 'يجب إضافة سطرين على الأقل في القيد المحاسبي (مدين ودائن)',
                        ])
                        ->required()
                        ->columnSpanFull()
                        ->addActionLabel('+ إضافة سطر')
                        ->reorderableWithButtons()
                        ->cloneable()
                        ->itemLabel(fn (array $state): ?string =>
                            filled($state['account_id'] ?? null)
                                ? Account::find($state['account_id'])?->code . ' — ' . Account::find($state['account_id'])?->name
                                : null
                        )
                        ->collapsible()
                        ->defaultItems(1),

                    // ── مؤشر التوازن ─────────────────────────────────
                    Placeholder::make('balance_check')
                        ->label('حالة التوازن')
                        ->live()
                        ->columnSpanFull()
                        ->content(function (Get $get): \Illuminate\Support\HtmlString {
                            $lines  = $get('lines') ?? [];
                            $debit  = collect($lines)->sum(fn ($l) => (float) ($l['debit_amount']  ?? 0));
                            $credit = collect($lines)->sum(fn ($l) => (float) ($l['credit_amount'] ?? 0));
                            $diff   = round(abs($debit - $credit), 2);
                            $ok     = $diff === 0.0 && ($debit + $credit) > 0;

                            $debitFmt  = number_format($debit,  2) . ' ر.س';
                            $creditFmt = number_format($credit, 2) . ' ر.س';
                            $diffFmt   = number_format($diff,   2) . ' ر.س';

                            if ($ok) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div class="flex items-center gap-3 rounded-xl border border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-950/30 px-4 py-3">'
                                    . '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-success-100 dark:bg-success-900 text-success-600 dark:text-success-400">'
                                    . '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                                    . '</div>'
                                    . '<div>'
                                    . '<p class="text-sm font-bold text-success-700 dark:text-success-300">✓ القيد متوازن</p>'
                                    . '<p class="text-xs text-success-600 dark:text-success-400 mt-0.5">مدين: <strong>' . $debitFmt . '</strong> &nbsp;|&nbsp; دائن: <strong>' . $creditFmt . '</strong></p>'
                                    . '</div>'
                                    . '</div>'
                                );
                            }

                            $msg = ($debit + $credit) === 0.0
                                ? 'أدخل قيم المدين والدائن في السطور أعلاه'
                                : 'الفرق: ' . $diffFmt . ' — يجب أن يتساوى مجموع المدين مع مجموع الدائن';

                            return new \Illuminate\Support\HtmlString(
                                '<div class="flex items-center gap-3 rounded-xl border border-danger-200 bg-danger-50 dark:border-danger-800 dark:bg-danger-950/30 px-4 py-3">'
                                . '<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-danger-100 dark:bg-danger-900 text-danger-600 dark:text-danger-400">'
                                . '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                                . '</div>'
                                . '<div>'
                                . '<p class="text-sm font-bold text-danger-700 dark:text-danger-300">✗ القيد غير متوازن</p>'
                                . '<p class="text-xs text-danger-600 dark:text-danger-400 mt-0.5">'
                                . 'مدين: <strong>' . $debitFmt . '</strong> &nbsp;|&nbsp; دائن: <strong>' . $creditFmt . '</strong>'
                                . ' &nbsp;—&nbsp; ' . $msg
                                . '</p>'
                                . '</div>'
                                . '</div>'
                            );
                        }),
                ])
                ->collapsible()
                ->columnSpanFull()
                ->compact(),

        ]);
    }
}
