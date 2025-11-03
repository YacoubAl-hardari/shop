<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserMerchant;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantOrder;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DataCheckWidget extends BaseWidget
{
    protected static ?int $sort = -2;
    
    protected function getStats(): array
    {
        $userId = Auth::user()->id;
        
        $teamId = Filament::getTenant()?->id;
        
        // Check merchants
        $totalMerchants = UserMerchant::where('user_id', $userId)->where('team_id', $teamId)->count();
        $activeMerchants = UserMerchant::where('user_id', $userId)->where('team_id', $teamId)->where('is_active', true)->count();
        
        // Check products
        $totalProducts = UserMerchantProduct::join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.team_id', $teamId)
            ->count();
            
        $activeProducts = UserMerchantProduct::join('user_merchants', 'user_merchant_products.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.team_id', $teamId)
            ->where('user_merchant_products.is_active', true)
            ->count();
            
        // Check orders
        $totalOrders = UserMerchantOrder::join('user_merchants', 'user_merchant_orders.user_merchant_id', '=', 'user_merchants.id')
            ->where('user_merchants.user_id', $userId)
            ->where('user_merchants.team_id', $teamId)
            ->count();
        
        return [
            Stat::make('إجمالي التجار', $totalMerchants)
                ->description("نشط: {$activeMerchants}")
                ->descriptionIcon('heroicon-o-building-storefront')
                ->color($totalMerchants > 0 ? 'success' : 'danger'),
                
            Stat::make('إجمالي المنتجات', $totalProducts)
                ->description("نشط: {$activeProducts}")
                ->descriptionIcon('heroicon-o-cube')
                ->color($totalProducts > 0 ? 'success' : 'danger'),
                
            Stat::make('إجمالي الطلبات', $totalOrders)
                ->description('عدد الطلبات الكلي')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color($totalOrders > 0 ? 'success' : 'warning'),
                
            Stat::make('معرف المستخدم', $userId)
                ->description('ID المستخدم الحالي')
                ->descriptionIcon('heroicon-o-user')
                ->color('info'),
        ];
    }
}

