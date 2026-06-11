<div class="space-y-4">
    <div class="flex items-center justify-between">
        <x-filament::button
            tag="a"
            :href="\App\Filament\Pages\SharedCustomerStatements::getUrl()"
            color="gray"
            icon="heroicon-o-arrow-right"
        >
            العودة للقائمة
        </x-filament::button>
    </div>

    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
            <div>
                <p class="text-sm text-gray-500">التاجر / الفرع</p>
                <p class="text-lg font-semibold">{{ $share->team?->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">اسمك لدى التاجر</p>
                <p class="text-lg font-semibold">{{ $customer->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">الهاتف</p>
                <p class="text-lg font-semibold">{{ $customer->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">المديونية</p>
                <p class="text-lg font-semibold">{{ number_format($customer->balance, 2) }} ر.س</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">الرصيد الفائض</p>
                <p class="text-lg font-semibold">{{ number_format($customer->credit_balance, 2) }} ر.س</p>
            </div>
        </div>
    </div>

    @php
        $transfers = $this->getFinancialTransfers();
        $pendingTransfer = $transfers->first(fn ($t) => $t->status->value === 'pending');
    @endphp

    @if ($pendingTransfer)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/30">
            <p class="font-semibold text-amber-800 dark:text-amber-200">بانتظار تأكيد التاجر</p>
            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                لديك طلب {{ $pendingTransfer->purpose->getLabel() }} بمبلغ {{ number_format($pendingTransfer->amount, 2) }} ر.س
                بتاريخ {{ $pendingTransfer->created_at->format('Y-m-d H:i') }} — لم يُسجّل بعد في كشف الحساب.
            </p>
        </div>
    @endif

    @if ($transfers->isNotEmpty())
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <h3 class="font-semibold">التحويلات المالية</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-right">التاريخ</th>
                        <th class="px-4 py-3 text-right">الغرض</th>
                        <th class="px-4 py-3 text-right">المبلغ</th>
                        <th class="px-4 py-3 text-right">طريقة الدفع</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transfers as $transfer)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-4 py-3">{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">{{ $transfer->purpose->getLabel() }}</td>
                            <td class="px-4 py-3">{{ number_format($transfer->amount, 2) }} ر.س</td>
                            <td class="px-4 py-3">
                                @switch($transfer->payment_method)
                                    @case('cash') نقد @break
                                    @case('card') بطاقة @break
                                    @case('bank_transfer') تحويل بنكي @break
                                    @default {{ $transfer->payment_method }}
                                @endswitch
                            </td>
                            <td class="px-4 py-3">{{ $transfer->status->getLabel() }}</td>
                            <td class="px-4 py-3">
                                @if ($transfer->status->value === 'rejected' && $transfer->rejection_reason)
                                    {{ $transfer->rejection_reason }}
                                @elseif ($transfer->notes)
                                    {{ $transfer->notes }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

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
                @forelse($lines as $line)
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
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا توجد حركات على هذا الحساب</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
