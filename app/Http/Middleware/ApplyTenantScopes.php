<?php

namespace App\Http\Middleware;

use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\BudgetCategory;
use App\Models\MerchantCategory;
use App\Models\UserMerchant;
use App\Models\UserMerchantAccountEntry;
use App\Models\UserMerchantAccountStatement;
use App\Models\UserMerchantOrder;
use App\Models\UserMerchantOrderItem;
use App\Models\UserMerchantPaymentTransaction;
use App\Models\UserMerchantProduct;
use App\Models\UserMerchantWallet;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Apply global scopes to all tenant-aware models
        $models = [
            UserMerchant::class,
            UserMerchantProduct::class,
            UserMerchantOrder::class,
            UserMerchantOrderItem::class,
            UserMerchantWallet::class,
            UserMerchantAccountStatement::class,
            UserMerchantPaymentTransaction::class,
            UserMerchantAccountEntry::class,
            Budget::class,
            BudgetCategory::class,
            BudgetAlert::class,
            MerchantCategory::class,
        ];

        foreach ($models as $model) {
            $model::addGlobalScope(
                'team',
                fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
            );
        }

        return $next($request);
    }
}
