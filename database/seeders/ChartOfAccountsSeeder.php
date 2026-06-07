<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\NormalBalance;
use App\Models\Account;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(?Team $team = null): void
    {
        $teams = $team ? collect([$team]) : Team::all();

        foreach ($teams as $teamRecord) {
            $this->seedForTeam($teamRecord);
        }
    }

    protected function seedForTeam(Team $team): void
    {
        if (Account::where('team_id', $team->id)->exists()) {
            return;
        }

        $tree = [
            ['code' => '1000', 'name' => 'الأصول', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT, 'is_system' => true, 'children' => [
                ['code' => '1001', 'name' => 'الصندوق — نقدية', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1002', 'name' => 'البنك — الحساب الجاري', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1003', 'name' => 'بوابات الدفع الإلكتروني', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1101', 'name' => 'ذمم مدينة — عملاء', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1201', 'name' => 'مخزون البضائع', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1301', 'name' => 'مصروفات مدفوعة مقدماً', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1401', 'name' => 'أصول ثابتة — أثاث وتجهيزات', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1402', 'name' => 'أصول ثابتة — معدات وآلات', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '1499', 'name' => 'مجمع إهلاك الأصول الثابتة', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::CREDIT],
            ]],
            ['code' => '2000', 'name' => 'الخصوم', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT, 'is_system' => true, 'children' => [
                ['code' => '2001', 'name' => 'ذمم دائنة — موردون', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '2002', 'name' => 'مستحقات الموظفين والعمولات', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '2004', 'name' => 'مصاريف مستحقة الدفع', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '2005', 'name' => 'التزامات مستحقة أخرى', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '2101', 'name' => 'أرصدة عملاء دائنة (دفعات مقدمة)', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
            ]],
            ['code' => '3000', 'name' => 'حقوق الملكية', 'type' => AccountType::EQUITY, 'normal_balance' => NormalBalance::CREDIT, 'is_system' => true, 'children' => [
                ['code' => '3001', 'name' => 'رأس المال', 'type' => AccountType::EQUITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '3002', 'name' => 'الأرباح المحتجزة', 'type' => AccountType::EQUITY, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '3003', 'name' => 'مسحوبات المالك', 'type' => AccountType::EQUITY, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '3004', 'name' => 'صافي ربح/خسارة السنة الحالية', 'type' => AccountType::EQUITY, 'normal_balance' => NormalBalance::CREDIT],
            ]],
            ['code' => '4000', 'name' => 'الإيرادات', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT, 'is_system' => true, 'children' => [
                ['code' => '4001', 'name' => 'إيرادات الخدمات', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '4002', 'name' => 'إيرادات تشغيلية أخرى', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '4003', 'name' => 'إيرادات مبيعات البضائع', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '4004', 'name' => 'إيرادات الاشتراكات والعقود', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
                ['code' => '4005', 'name' => 'إيرادات متنوعة', 'type' => AccountType::REVENUE, 'normal_balance' => NormalBalance::CREDIT],
            ]],
            ['code' => '5000', 'name' => 'تكلفة المبيعات', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT, 'is_system' => true, 'children' => [
                ['code' => '5001', 'name' => 'تكلفة البضائع المباعة', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '5002', 'name' => 'تكلفة الخدمات المباشرة', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '5003', 'name' => 'مستلزمات تشغيل مستهلكة', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
            ]],
            ['code' => '6000', 'name' => 'المصروفات التشغيلية', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT, 'is_system' => true, 'children' => [
                ['code' => '6001', 'name' => 'إيجار المقر', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6002', 'name' => 'فواتير المرافق (كهرباء، ماء، إنترنت)', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6003', 'name' => 'مصروفات التسويق والإعلان', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6004', 'name' => 'صيانة وإصلاح الأصول', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6005', 'name' => 'رواتب وأجور الموظفين', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6006', 'name' => 'اشتراكات البرمجيات والأنظمة', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6007', 'name' => 'مصاريف النقل والشحن', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6008', 'name' => 'مصاريف إدارية ونثرية', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6009', 'name' => 'مصروف الإهلاك', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '6010', 'name' => 'رسوم بنكية وعمولات مالية', 'type' => AccountType::EXPENSE, 'normal_balance' => NormalBalance::DEBIT],
            ]],
            ['code' => '8000', 'name' => 'ضريبة القيمة المضافة', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT, 'is_system' => true, 'children' => [
                ['code' => '8001', 'name' => 'ضريبة القيمة المضافة — مدخلات (قابلة للاسترداد)', 'type' => AccountType::ASSET, 'normal_balance' => NormalBalance::DEBIT],
                ['code' => '8002', 'name' => 'ضريبة القيمة المضافة — مخرجات (مستحقة)', 'type' => AccountType::LIABILITY, 'normal_balance' => NormalBalance::CREDIT],
            ]],
        ];

        foreach ($tree as $group) {
            $this->createAccountTree($team, $group);
        }
    }

    protected function createAccountTree(Team $team, array $node, ?Account $parent = null): void
    {
        $account = Account::create([
            'team_id' => $team->id,
            'code' => $node['code'],
            'name' => $node['name'],
            'type' => $node['type'],
            'normal_balance' => $node['normal_balance'],
            'is_system' => $node['is_system'] ?? true,
            'is_active' => true,
        ], $parent);

        foreach ($node['children'] ?? [] as $child) {
            $this->createAccountTree($team, $child, $account);
        }
    }
}
