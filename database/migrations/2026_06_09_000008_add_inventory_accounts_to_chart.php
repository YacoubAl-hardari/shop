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
            $this->addAccountsForTeam($team);
        }
    }

    protected function addAccountsForTeam(Team $team): void
    {
        // حساب مخزون البضائع الأصلي (1201) هو الأب
        $inventoryParent = Account::where('team_id', $team->id)->where('code', '1201')->first();

        // إضافة حساب فوارق الجرد إذا لم يكن موجوداً
        if ($inventoryParent && ! Account::where('team_id', $team->id)->where('code', '1202')->exists()) {
            Account::create([
                'team_id' => $team->id,
                'code' => '1202',
                'name' => 'فوارق جرد المخزون',
                'type' => AccountType::ASSET,
                'normal_balance' => NormalBalance::DEBIT,
                'is_system' => true,
                'is_active' => true,
            ], $inventoryParent);
        }

        // حساب ملخص الدخل (مؤقت للإغلاق السنوي)
        $equityParent = Account::where('team_id', $team->id)->where('code', '3000')->first();
        if ($equityParent && ! Account::where('team_id', $team->id)->where('code', '3005')->exists()) {
            Account::create([
                'team_id' => $team->id,
                'code' => '3005',
                'name' => 'ملخص الدخل (حساب إغلاق)',
                'type' => AccountType::EQUITY,
                'normal_balance' => NormalBalance::CREDIT,
                'is_system' => true,
                'is_active' => true,
            ], $equityParent);
        }
    }

    public function down(): void
    {
        Account::whereIn('code', ['1202', '3005'])->delete();
    }
};
