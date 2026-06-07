<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-4 text-lg font-bold">ميزان المراجعة</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-right">الرمز</th>
                        <th class="py-2 text-right">الحساب</th>
                        <th class="py-2 text-right">مدين</th>
                        <th class="py-2 text-right">دائن</th>
                        <th class="py-2 text-right">الرصيد</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->getTrialBalance() as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2">{{ $row['code'] }}</td>
                            <td class="py-2">{{ $row['name'] }}</td>
                            <td class="py-2">{{ number_format($row['debit'], 2) }}</td>
                            <td class="py-2">{{ number_format($row['credit'], 2) }}</td>
                            <td class="py-2 font-semibold">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="mb-4 text-lg font-bold">قائمة الدخل (مبسطة)</h2>
            @php $income = $this->getIncomeStatement(); @endphp
            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b pb-2">
                    <span>إجمالي الإيرادات</span>
                    <span class="font-semibold text-green-600">{{ number_format($income['revenue'], 2) }} ر.س</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span>إجمالي المصروفات</span>
                    <span class="font-semibold text-red-600">{{ number_format($income['expenses'], 2) }} ر.س</span>
                </div>
                <div class="flex justify-between pt-2 text-base font-bold">
                    <span>صافي الربح</span>
                    <span>{{ number_format($income['net_income'], 2) }} ر.س</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
