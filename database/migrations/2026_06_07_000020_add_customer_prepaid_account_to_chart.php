<?php

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (Team::all() as $team) {
            if (Account::where('team_id', $team->id)->where('code', '2101')->exists()) {
                continue;
            }

            $parent = Account::where('team_id', $team->id)->where('code', '2000')->first();

            if (! $parent) {
                continue;
            }

            Account::create([
                'team_id' => $team->id,
                'code' => '2101',
                'name' => 'أرصدة عملاء دائنة (دفعات مقدمة)',
                'type' => AccountType::LIABILITY,
                'normal_balance' => NormalBalance::CREDIT,
                'is_system' => true,
                'is_active' => true,
            ], $parent);
        }
    }

    public function down(): void
    {
        Account::where('code', '2101')->delete();
    }
};
