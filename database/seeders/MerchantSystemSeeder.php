<?php

namespace Database\Seeders;

use App\Enums\MerchantPaymentAccountType;
use App\Enums\UserType;
use App\Models\MerchantCustomer;
use App\Models\MerchantPaymentAccount;
use App\Models\MerchantProduct;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MerchantSystemSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = User::factory()->merchant()->create([
            'name' => 'تاجر تجريبي',
            'email' => 'merchant@example.com',
            'password' => Hash::make('password'),
        ]);

        $team = Team::create([
            'name' => 'فرع الرياض',
            'slug' => 'riyadh-branch',
            'description' => 'الفرع الرئيسي',
        ]);

        $team->members()->attach($merchant, ['role' => 'owner']);

        (new ChartOfAccountsSeeder)->run($team);

        MerchantPaymentAccount::create([
            'team_id' => $team->id,
            'type' => MerchantPaymentAccountType::BANK,
            'name' => 'البنك الأهلي',
            'account_number' => 'SA1234567890123456789012',
            'is_default' => true,
        ]);

        MerchantPaymentAccount::create([
            'team_id' => $team->id,
            'type' => MerchantPaymentAccountType::CARD,
            'name' => 'مدى',
            'account_number' => 'MERCHANT-MD-001',
            'is_default' => true,
        ]);

        MerchantProduct::create([
            'team_id' => $team->id,
            'name' => 'شامبو',
            'barcode' => '0001',
            'sku' => 'SH-001',
            'price' => 45.00,
            'cost' => 25.00,
            'stock_quantity' => 100,
            'unit' => 'قطعة',
        ]);

        MerchantProduct::create([
            'team_id' => $team->id,
            'name' => 'زيت شعر',
            'sku' => 'HS-002',
            'price' => 35.00,
            'cost' => 18.00,
            'stock_quantity' => 50,
            'unit' => 'قطعة',
        ]);

        MerchantCustomer::create([
            'team_id' => $team->id,
            'name' => 'عميل نقدي',
            'phone' => '0500000001',
        ]);

        $user = User::factory()->user()->create([
            'name' => 'مستخدم تجريبي',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        $userTeam = Team::create([
            'name' => 'حسابي الشخصي',
            'slug' => 'personal-account',
        ]);

        $userTeam->members()->attach($user, ['role' => 'owner']);
    }
}
