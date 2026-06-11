<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SimilarProductsComparisonWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'جميع المنتجات مع مقارنة الأسعار';

    protected static ?string $subheading = 'عرض جميع المنتجات مع إمكانية مقارنة الأسعار بين التجار';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $userId = Auth::user()->id;

        // Get all products with merchant info
        $similarProducts = UserMerchantProduct::query()
            ->select([
                'user_merchant_products.*',
                'user_merchants.name as merchant_name',
                DB::raw('CASE 
                    WHEN user_merchant_products.barcode IS NOT NULL AND user_merchant_products.barcode != "" 
                    THEN user_merchant_products.barcode 
                    ELSE user_merchant_products.name 
                END as comparison_key')
            ])
            ->join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.is_active', true)
            ->where('user_merchant_products.is_active', true)
            ->orderBy('user_merchant_products.name')
            ->orderBy('user_merchant_products.price');

        return $table
            ->query($similarProducts)
            ->columns([
                Tables\Columns\TextColumn::make('comparison_key')
                    ->label('المعرف المشترك')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->formatStateUsing(fn($state) => is_string($state) ? $state : 'غير محدد'),

                Tables\Columns\TextColumn::make('merchant_name')
                    ->label('اسم التاجر')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => is_string($state) ? $state : 'غير محدد'),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->formatStateUsing(fn($state) => is_string($state) ? $state : 'غير محدد'),

                Tables\Columns\TextColumn::make('barcode')
                    ->label('الباركود')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => is_string($state) ? $state : 'غير محدد'),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')

                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('العلامة التجارية')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => is_string($state) ? $state : 'غير محدد'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('merchant_name')
                    ->label('التاجر')
                    ->options(
                        UserMerchant::where('user_id', $userId)
                            ->where('is_active', true)
                            ->pluck('name', 'name')
                    ),
            ])
            ->actions([
                // يمكن إضافة actions هنا لاحقاً
            ])
            ->defaultSort('comparison_key')
            ->paginated([10, 25, 50]);
    }
}

