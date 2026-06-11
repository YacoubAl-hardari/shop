<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchant;
use App\Models\UserMerchantOrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductPerformanceTableWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'جدول مقارنة أداء المنتجات بين التجار';

    protected static ?string $subheading = 'مقارنة مفصلة للمنتجات والأسعار والمبيعات';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $userId = Auth::user()->id;

        return $table
            ->query(
                UserMerchantProduct::query()
                    ->select([
                        'user_merchant_products.*',
                        'user_merchants.name as merchant_name',
                        DB::raw('COALESCE(SUM(user_merchant_order_items.quantity), 0) as total_quantity_sold'),
                        DB::raw('COALESCE(SUM(user_merchant_order_items.total_price), 0) as total_revenue'),
                        DB::raw('COUNT(DISTINCT user_merchant_order_items.user_merchant_order_id) as orders_count')
                    ])
                    ->join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
                    ->leftJoin('user_merchant_order_items', 'user_merchant_products.id', '=', 'user_merchant_order_items.user_merchant_product_id')
                    ->where('user_merchants.user_id', $userId)
                    ->where('user_merchants.is_active', true)
                    ->groupBy('user_merchant_products.id')
            )
            ->columns([
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

                Tables\Columns\TextColumn::make('total_quantity_sold')
                    ->label('الكمية المباعة')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('إجمالي الإيرادات')

                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('عدد الطلبات')
                    ->numeric()
                    ->sortable()
                    ->alignEnd(),

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

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('المنتجات النشطة')
                    ->boolean()
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط')
                    ->native(false),
            ])
            ->actions([
                // يمكن إضافة actions هنا لاحقاً
            ])
            ->bulkActions([
                // يمكن إضافة bulk actions هنا لاحقاً
            ])
            ->defaultSort('total_revenue', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}

