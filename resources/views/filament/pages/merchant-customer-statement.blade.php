<x-filament-panels::page>
    <div class="space-y-4">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-gray-500">العميل</p>
                    <p class="text-lg font-semibold">{{ $this->record->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">الهاتف</p>
                    <p class="text-lg font-semibold">{{ $this->record->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">المديونية</p>
                    <p class="text-lg font-semibold">{{ number_format($this->record->balance, 2) }} ر.س</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">الرصيد الفائض</p>
                    <p class="text-lg font-semibold">{{ number_format($this->record->credit_balance, 2) }} ر.س</p>
                </div>
            </div>
        </div>

        @include('filament.pages.partials.customer-statement-filters')

        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">القيد</th>
                        <th class="px-4 py-3 text-right">الحساب</th>
                        <th class="px-4 py-3 text-right">مدين</th>
                        <th class="px-4 py-3 text-right">دائن</th>
                        <th class="px-4 py-3 text-right">الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getStatementLines() as $line)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3">{{ $line->journalEntry?->entry_date?->format('Y-m-d') }}</td>
                            <td class="px-4 py-3">{{ $line->journalEntry?->entry_number }}</td>
                            <td class="px-4 py-3">{{ $line->account?->name }}</td>
                            <td class="px-4 py-3">{{ number_format($line->debit_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($line->credit_amount, 2) }}</td>
                            <td class="px-4 py-3">{{ $line->description ?? $line->journalEntry?->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا توجد حركات على هذا العميل</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
