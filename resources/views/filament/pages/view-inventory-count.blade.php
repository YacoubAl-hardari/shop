<x-filament-panels::page>
    <div class="space-y-6">
        {{-- رأس الجرد --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs text-gray-500">رقم الجرد</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $record->count_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">السنة المالية</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $record->fiscal_year }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">تاريخ الجرد</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $record->count_date?->format('Y/m/d') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">الحالة</p>
                    <x-filament::badge :color="$record->status->color()">{{ $record->status->label() }}</x-filament::badge>
                </div>
            </div>

            @if($record->status === \App\Enums\InventoryCountStatus::APPROVED)
            <div class="mt-4 grid grid-cols-3 gap-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                <div>
                    <p class="text-xs text-gray-500">القيمة الدفترية</p>
                    <p class="mt-1 font-bold text-gray-900 dark:text-white">{{ number_format($record->total_book_value, 2) }} ر.س</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">القيمة الفعلية</p>
                    <p class="mt-1 font-bold text-gray-900 dark:text-white">{{ number_format($record->total_counted_value, 2) }} ر.س</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">الفارق</p>
                    <p class="mt-1 font-bold {{ (float)$record->variance_value > 0 ? 'text-success-600' : ((float)$record->variance_value < 0 ? 'text-danger-600' : 'text-gray-600') }}">
                        {{ number_format($record->variance_value, 2) }} ر.س
                    </p>
                </div>
            </div>

            <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <p class="text-xs text-gray-500">القيد المحاسبي المرتبط</p>
                        @if($record->journalEntry)
                            <div class="mt-1 flex items-center gap-2">
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    رقم القيد: {{ $record->journalEntry->entry_number }}
                                </span>
                                <x-filament::badge :color="$record->journalEntry->status->value === 'posted' ? 'success' : 'danger'">
                                    {{ $record->journalEntry->status->value === 'posted' ? 'مرحّل' : 'ملغي' }}
                                </x-filament::badge>
                            </div>
                        @else
                            <p class="mt-1 font-semibold text-danger-600 dark:text-danger-400">غير موجود / غير مرحّل</p>
                        @endif
                    </div>
                    @if($record->journalEntry)
                        <a 
                            href="{{ \App\Filament\Resources\JournalEntries\JournalEntryResource::getUrl('view', ['record' => $record->journalEntry]) }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
                        >
                            <span>عرض القيد المحاسبي</span>
                            <svg class="h-4 w-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- جدول الأصناف --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">المنتج</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الوحدة</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الكمية الدفترية</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الكمية المباعة</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الكمية التالفة</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">المتبقي في المخزن</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الكمية الفعلية</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">الفارق</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">قيمة الفارق</th>
                        @if($record->isEditable())
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">إجراء</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($record->items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800" wire:key="item-{{ $item->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $item->unit ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ number_format($item->book_quantity, 2) }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ number_format($item->getSoldQuantity(), 2) }}</td>
                        <td class="px-4 py-3 text-center text-danger-600 dark:text-danger-400 font-medium">{{ $item->getDamagedQuantity() > 0 ? number_format($item->getDamagedQuantity(), 2) : '0.00' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ number_format($item->product?->stock_quantity ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($record->isEditable())
                                <input
                                    type="number"
                                    step="1"
                                    min="0"
                                    value="{{ isset($countedQuantities[$item->id]) ? (int) $countedQuantities[$item->id] : '' }}"
                                    wire:change="saveQuantity({{ $item->id }}, $event.target.value)"
                                    class="w-24 rounded-lg border border-gray-300 px-2 py-1 text-center text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="أدخل..."
                                />
                            @else
                                <span class="{{ $item->counted_quantity !== null ? 'font-medium text-gray-900 dark:text-white' : 'text-gray-400' }}">
                                    {{ $item->counted_quantity !== null ? number_format($item->counted_quantity, 0) : '—' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->counted_quantity !== null)
                                <span class="{{ (float)$item->variance_quantity > 0 ? 'text-success-600 font-medium' : ((float)$item->variance_quantity < 0 ? 'text-danger-600 font-medium' : 'text-gray-400') }}">
                                    {{ number_format($item->variance_quantity, 2) }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->counted_quantity !== null)
                                <span class="{{ (float)$item->variance_value > 0 ? 'text-success-600' : ((float)$item->variance_value < 0 ? 'text-danger-600' : 'text-gray-400') }}">
                                    {{ number_format($item->variance_value, 2) }} ر.س
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @if($record->isEditable())
                        <td class="px-4 py-3 text-center text-xs text-gray-400">تلقائي</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
