<?php

namespace App\Filament\Resources\MerchantProducts\Schemas;

use App\Models\Distributor;
use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use JeffersonGoncalves\Filament\QrCodeField\Forms\Components\QrCodeInput;

class MerchantProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('بيانات المنتج')
                ->schema([
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required(),
                    QrCodeInput::make('barcode')
                        ->label('الباركود / QR')
                        ->maxLength(255)
                        ->icon('heroicon-o-qr-code')
                        ->helperText('امسح الرمز أو أدخله يدوياً')
                        ->nullable()
                        ->unique(
                            table: 'merchant_products',
                            column: 'barcode',
                            ignoreRecord: true,
                            modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule) {
                                $tenant = \Filament\Facades\Filament::getTenant();
                                return $tenant ? $rule->where('team_id', $tenant->id) : $rule;
                            }
                        )
                        ->validationMessages([
                            'unique' => 'رقم الباركود هذا مسجل مسبقاً لمنتج آخر.',
                        ]),
                    TextInput::make('sku')
                        ->label('رمز المنتج الداخلي')
                        ->helperText('اختياري — للتصنيف الداخلي'),
                    TextInput::make('price')
                        ->label('سعر البيع')
                        ->numeric()
                        ->required(),
                    TextInput::make('cost')
                        ->label('التكلفة')
                        ->numeric()
                        ->default(0),
                    TextInput::make('stock_quantity')
                        ->label('الكمية')
                        ->numeric()
                        ->default(0),
                    TextInput::make('unit')
                        ->label('الوحدة'),
                    Textarea::make('description')
                        ->label('الوصف')
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                ])
                ->columns(3)
                ->columnSpanFull(),

            Section::make('المورد والموزع')
                ->description('اختياري — لربط المنتج بمصدر التوريد')
                ->schema([
                    Select::make('supplier_id')
                        ->label('المورد')
                        ->options(fn () => Supplier::query()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('distributor_id', null)),
                    Select::make('distributor_id')
                        ->label('الموزع')
                        ->options(function (Get $get): array {
                            $query = Distributor::query();

                            if ($supplierId = $get('supplier_id')) {
                                $query->where('supplier_id', $supplierId);
                            }

                            return $query->pluck('name', 'id')->all();
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if (! $state) {
                                return;
                            }

                            $supplierId = Distributor::query()
                                ->whereKey($state)
                                ->value('supplier_id');

                            if ($supplierId) {
                                $set('supplier_id', $supplierId);
                            }
                        }),
                ])
                ->columns(2)
                ->columnSpanFull()
                ->collapsed(),
        ]);
    }
}
