<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Distributor;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MerchantCustomer;
use App\Models\MerchantCustomerPayment;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\PosSale;
use App\Models\PosSaleItem;
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
