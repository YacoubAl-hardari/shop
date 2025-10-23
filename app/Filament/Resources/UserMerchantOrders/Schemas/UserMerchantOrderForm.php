<?php

namespace App\Filament\Resources\UserMerchantOrders\Schemas;

use App\Models\User;
use App\Enums\ProductUnit;
use App\Models\UserMerchant;
use Filament\Schemas\Schema;
use App\Models\UserMerchantProduct;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use JeffersonGoncalves\Filament\QrCodeField\Forms\Components\QrCodeInput;

class UserMerchantOrderForm
{
    private static function calculateMainTotal($orderItems): float
    {
        if (!is_array($orderItems)) {
            return 0;
        }
        
        $total = 0;
        foreach ($orderItems as $item) {
            if (isset($item['total_price']) && is_numeric($item['total_price'])) {
                $total += $item['total_price'];
            }
        }
        
        return $total;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        Select::make('user_merchant_id')
                        ->label('التاجر')
                        ->relationship('userMerchant', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state) {
                            // Generate new order number for the selected merchant
                            if ($state) {
                                $lastOrder = \App\Models\UserMerchantOrder::where('user_merchant_id', $state)
                                    ->orderBy('id', 'desc')
                                    ->first();
                                $nextNumber = $lastOrder ? (int) $lastOrder->order_number + 1 : 1;
                                $set('order_number', str_pad($nextNumber, 7, '0', STR_PAD_LEFT));
                            }
                        }),
    
                    TextInput::make('order_number')
                        ->label('رقم الطلب')
                        ->disabled()
                        ->dehydrated()
                        ->live()
                        ->default(function (callable $get) {
                            // Get the last order number for current user and increment it
                            $merchantId = $get('user_merchant_id');
                            if (!$merchantId) {
                                return '';
                            }
                            $lastOrder = \App\Models\UserMerchantOrder::where('user_merchant_id', $merchantId)
                                ->orderBy('id', 'desc')
                                ->first();
                            $nextNumber = $lastOrder ? (int) $lastOrder->order_number + 1 : 1;
                            return str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
                        }),
    
                        TextInput::make('total_price')
                    ->label('إجمالي السعر')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated()
                    ->default(0),

                
    
                    Repeater::make('order_items')
                        ->label('عناصر الطلب')
                        ->live()
                        ->defaultItems(1)
                        ->visible(fn (callable $get) => !empty($get('user_merchant_id')))
                    ->schema([
                        Toggle::make('use_barcode_search')
                            ->label('البحث بالباركود')
                            ->default(false)
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('user_merchant_product_id')
                            ->hidden()
                            ->dehydrated(),

                        Select::make('user_merchant_product_selection')
                            ->label('المنتج')
                            ->options(function (callable $get) {
                                $merchantId = $get('../../user_merchant_id');
                                if (!$merchantId) {
                                    return [];
                                }
                                
                                return UserMerchantProduct::where('user_merchant_id', $merchantId)
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($product) {
                                        return [$product->id => $product->name];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->visible(fn (callable $get) => !$get('use_barcode_search'))
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('user_merchant_product_id', $state);
                                    $product = UserMerchantProduct::find($state);
                                    if ($product) {
                                        $set('price', $product->price);
                                    }
                                }
                            }),

                            QrCodeInput::make('barcode_search')
                            ->label('مسح الباركود')
                            ->required()
                            ->live()
                            ->visible(fn (callable $get) => $get('use_barcode_search') && !$get('user_merchant_product_id'))
                            ->icon('heroicon-o-qr-code')
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                if ($state) {
                                    $merchantId = $get('../../user_merchant_id');
                                    if ($merchantId) {
                                        $product = UserMerchantProduct::where('user_merchant_id', $merchantId)
                                            ->where('barcode', $state)
                                            ->where('is_active', true)
                                            ->first();
                                        
                                        if ($product) {
                                            $set('user_merchant_product_id', $product->id);
                                            $set('price', $product->price);
                                            $set('selected_product_name', $product->name);
                                            // Clear the barcode search field after successful scan
                                            $set('barcode_search', '');
                                        }
                                    }
                                }
                            }),

                        TextInput::make('selected_product_name')
                            ->label('المنتج المختار')
                            ->disabled()
                            ->visible(fn (callable $get) => $get('use_barcode_search') && $get('user_merchant_product_id'))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                // This will be updated when user_merchant_product_id changes
                            })
                            ->default(function (callable $get) {
                                $productId = $get('user_merchant_product_id');
                                if ($productId) {
                                    return UserMerchantProduct::find($productId)?->name;
                                }
                                return null;
                            })
                            ->suffixAction(
                                Action::make('reset_barcode_search')
                                    ->label('إعادة البحث')
                                    ->icon('heroicon-o-arrow-path')
                                    ->action(function (callable $set) {
                                        $set('user_merchant_product_id', null);
                                        $set('barcode_search', '');
                                        $set('selected_product_name', null);
                                        $set('price', null);
                                        $set('quantity', 0);
                                        $set('total_price', 0);
                                    })
                            ),
    
                        Select::make('unit')
                            ->label('الوحدة')
                            ->options(ProductUnit::arabicOptions())
                            ->default(ProductUnit::default()->value)
                            ->required()
                            ->visible(fn (callable $get) => ($get('user_merchant_product_id') ?? false)),
    
                        TextInput::make('quantity')
                            ->label('الكمية')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->visible(fn (callable $get) => ($get('user_merchant_product_id') ?? false))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $price = $get('price');
                                if ($state && $price) {
                                    $set('total_price', $state * $price);
                                }
                                // Update main total
                                $set('../../total_price', self::calculateMainTotal($get('../../order_items')));
                            }),
    
                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->visible(fn (callable $get) => ($get('user_merchant_product_id') ?? false))
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $quantity = $get('quantity');
                                if ($state && $quantity) {
                                    $set('total_price', $state * $quantity);
                                }
                                // Update main total
                                $set('../../total_price', self::calculateMainTotal($get('../../order_items')));
                            }),
    
                        TextInput::make('total_price')
                            ->label('إجمالي السعر')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (callable $get) => ($get('user_merchant_product_id') ?? false)),
                        ])
                        ->grid(1)
                        ->columns(5)
                        ->defaultItems(1)
                        ->addActionLabel('إضافة عنصر')
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => 
                        isset($state['user_merchant_product_id']) && $state['user_merchant_product_id']
                            ? UserMerchantProduct::find($state['user_merchant_product_id'])?->name 
                            : 'عنصر جديد'
                    )
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        // Update main total when order items change
                        $set('total_price', self::calculateMainTotal($state));
                    })
                    ->columnSpanFull(),

                    Textarea::make('note')
                        ->label('ملاحظات')
                        ->rows(3)
                        ->columnSpanFull(),

                    ])
                    ->columnSpanFull()
                    ->columns(3)
                    ,

            ]);
    }
}

