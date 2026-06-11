<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchant;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantOrder;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MerchantComparisonStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    
    protected static ?int $sort = 0;
    
    protected function getStats(): array
    {
        $userId = Auth::user()->id;
        
        // Get total merchants
        $totalMerchants = UserMerchant::where('user_id', $userId)
            ->where('is_active', true)
            ->count();
            
        // Get total products across all merchants
        $totalProducts = UserMerchantProduct::join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.is_active', true)
            ->where('user_merchant_products.is_active', true)
            ->count();
            
        // Get products with same barcode/name (duplicates)
        $duplicateProducts = UserMerchantProduct::select(
            DB::raw('CASE 
                WHEN user_merchant_products.barcode IS NOT NULL AND user_merchant_products.barcode != "" 
                THEN user_merchant_products.barcode 
                ELSE user_merchant_products.name 
            END as comparison_key')
        )
        ->join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
        ->where('user_merchants.user_id', $userId)
        ->where('user_merchants.is_active', true)
        ->where('user_merchant_products.is_active', true)
        ->groupBy(DB::raw('CASE 
            WHEN user_merchant_products.barcode IS NOT NULL AND user_merchant_products.barcode != "" 
            THEN user_merchant_products.barcode 
            ELSE user_merchant_products.name 
        END'))
        ->havingRaw('COUNT(*) > 1')
        ->count();
        
        // Get total sales across all merchants
        $totalSales = UserMerchantOrder::join('user_merchants', 'user_merchant_orders.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.is_active', true)
            ->sum('user_merchant_orders.total_price');
            
        // Get average price difference for duplicate products
        $avgPriceDifference = 0;
        if ($duplicateProducts > 0) {
            $priceDifferences = UserMerchantProduct::select(
                DB::raw('CASE 
                    WHEN user_merchant_products.barcode IS NOT NULL AND user_merchant_products.barcode != "" 
                    THEN user_merchant_products.barcode 
                    ELSE user_merchant_products.name 
                END as comparison_key'),
                DB::raw('MAX(user_merchant_products.price) - MIN(user_merchant_products.price) as price_diff')
            )
            ->join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.is_active', true)
            ->where('user_merchant_products.is_active', true)
            ->groupBy(DB::raw('CASE 
                WHEN user_merchant_products.barcode IS NOT NULL AND user_merchant_products.barcode != "" 
                THEN user_merchant_products.barcode 
                ELSE user_merchant_products.name 
            END'))
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
            $avgPriceDifference = $priceDifferences->avg('price_diff') ?? 0;
        }
        
        return [
            Stat::make('إجمالي التجار النشطين', $totalMerchants)
                ->description('عدد التجار الذين يتعامل معهم المستخدم')
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color('primary'),
                
            Stat::make('إجمالي المنتجات', $totalProducts)
                ->description('عدد المنتجات المتاحة من جميع التجار')
                ->descriptionIcon('heroicon-o-cube')
                ->color('info'),
                
            Stat::make('المنتجات المتشابهة', $duplicateProducts)
                ->description('منتجات لها نفس الباركود أو الاسم')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('warning'),
                
            Stat::make('متوسط فرق السعر', number_format($avgPriceDifference, 2) . ' ' . \App\Helpers\CurrencyHelper::getSymbol())
                ->description('متوسط الفرق في الأسعار للمنتجات المتشابهة')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color($avgPriceDifference > 0 ? 'danger' : 'success'),
        ];
    }
}

