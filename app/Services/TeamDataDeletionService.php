<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Distributor;
use App\Models\FiscalYearClosing;
use App\Models\InventoryCount;
use App\Models\InventoryCountItem;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerFinancialTransfer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantCustomerStatementShare;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\PosExchangeItem;
use App\Models\PosSale;
use App\Models\PosSaleItem;
use App\Models\PosSaleReturn;
use App\Models\PosSaleReturnItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamDataDeletionService
{
    public function deleteTeamBusinessData(Team $team): bool
    {
        return DB::transaction(fn () => $this->purgeTeamBusinessData($team));
    }

    public function purgeTeamBusinessData(Team $team): bool
    {
        Log::info("Starting business data deletion for team: {$team->id}");

        // 1. Delete stock movements
        StockMovement::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        // 2. Delete return items and exchange items, then sale returns
        $returnIds = PosSaleReturn::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('id');

        if ($returnIds->isNotEmpty()) {
            PosSaleReturnItem::whereIn('pos_sale_return_id', $returnIds)->delete();
            PosExchangeItem::whereIn('pos_sale_return_id', $returnIds)->delete();
        }
        PosSaleReturn::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        // 3. Delete inventory count items, then inventory counts
        $countIds = InventoryCount::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->pluck('id');

        if ($countIds->isNotEmpty()) {
            InventoryCountItem::whereIn('inventory_count_id', $countIds)->delete();
        }
        InventoryCount::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        // 4. Delete fiscal year closings
        FiscalYearClosing::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        // 5. Delete financial transfers & statement shares
        MerchantCustomerFinancialTransfer::withoutGlobalScopes()->where('team_id', $team->id)->delete();
        MerchantCustomerStatementShare::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        // 6. Existing deletion logic
        $journalEntryIds = JournalEntry::withoutGlobalScopes()
                ->where('team_id', $team->id)
                ->pluck('id');

            if ($journalEntryIds->isNotEmpty()) {
                JournalLine::whereIn('journal_entry_id', $journalEntryIds)->delete();
            }

            JournalEntry::withoutGlobalScopes()->where('team_id', $team->id)->delete();

            $saleIds = PosSale::withoutGlobalScopes()
                ->where('team_id', $team->id)
                ->pluck('id');

            if ($saleIds->isNotEmpty()) {
                PosSaleItem::whereIn('pos_sale_id', $saleIds)->delete();
            }

            PosSale::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            MerchantCustomerPayment::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            MerchantProduct::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            MerchantCustomer::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            MerchantPaymentAccount::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            Distributor::withoutGlobalScopes()->where('team_id', $team->id)->delete();
            Supplier::withoutGlobalScopes()->where('team_id', $team->id)->delete();

        Account::withoutGlobalScopes()
            ->where('team_id', $team->id)
            ->orderByDesc('_lft')
            ->get()
            ->each(fn (Account $account) => $account->delete());

        Log::info("Successfully deleted business data for team: {$team->id}");

        return true;
    }
}
