<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Models\Budget;
use App\Models\BudgetAlert;
use App\Models\BudgetCategory;
use App\Models\JournalEntry;
use App\Models\MerchantCategory;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use App\Models\Distributor;
use App\Models\Supplier;
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
    public function handle(Request $request, Closure $next): Response
    {
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
            Account::class,
            JournalEntry::class,
            MerchantCustomer::class,
            MerchantCustomerPayment::class,
            MerchantPaymentAccount::class,
            MerchantProduct::class,
            PosSale::class,
            Supplier::class,
            Distributor::class,
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
