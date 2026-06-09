<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if ($this->preview)
        <div class="rounded-xl border border-info-200 bg-info-50 p-6 dark:border-info-800 dark:bg-info-950">
            <h3 class="mb-4 text-lg font-bold text-info-900 dark:text-info-100">معاينة الإغلاق السنوي — {{ $this->preview->fiscal_year }}</h3>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <p class="text-xs text-gray-500">إجمالي الإيرادات</p>
                    <p class="mt-1 text-lg font-bold text-success-600">{{ number_format($this->preview->total_revenue, 2) }} ر.س</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <p class="text-xs text-gray-500">إجمالي المصروفات</p>
                    <p class="mt-1 text-lg font-bold text-danger-600">{{ number_format($this->preview->total_expense, 2) }} ر.س</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <p class="text-xs text-gray-500">صافي الدخل</p>
                    <p class="mt-1 text-lg font-bold {{ (float)$this->preview->net_income >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        {{ number_format($this->preview->net_income, 2) }} ر.س
                    </p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <p class="text-xs text-gray-500">الأرباح المحتجزة بعد الإغلاق</p>
                    <p class="mt-1 text-lg font-bold text-primary-600">{{ number_format($this->preview->retained_earnings_after, 2) }} ر.س</p>
                </div>
            </div>
            <div class="mt-4 rounded-lg bg-warning-50 p-3 dark:bg-warning-900">
                <p class="text-sm text-warning-800 dark:text-warning-200">
                    ⚠ بعد الترحيل سيتم إقفال جميع حسابات الإيرادات والمصروفات ونقل صافي الدخل إلى حساب الأرباح المحتجزة (3002).
                    هذا الإجراء <strong>لا يمكن التراجع عنه</strong>.
                </p>
            </div>
        </div>
        @endif

        {{-- قائمة الإغلاقات السابقة --}}
        @php
            $team = \Filament\Facades\Filament::getTenant();
            $closings = $team ? \App\Models\FiscalYearClosing::where('team_id', $team->id)->orderByDesc('fiscal_year')->get() : collect();
        @endphp
        @if($closings->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">الإغلاقات السابقة</h3>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($closings as $closing)
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">السنة {{ $closing->fiscal_year }}</p>
                        <p class="text-xs text-gray-500">{{ $closing->closing_date?->format('Y/m/d') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold {{ (float)$closing->net_income >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ number_format($closing->net_income, 2) }} ر.س
                        </p>
                        @php
                            $isVoided = $closing->journalEntry && $closing->journalEntry->status->value === 'void';
                            $badgeColor = $isVoided ? 'danger' : ($closing->status === 'posted' ? 'success' : ($closing->status === 'locked' ? 'info' : 'gray'));
                            $badgeLabel = $isVoided ? 'ملغي' : ($closing->status === 'posted' ? 'مُرحَّل' : ($closing->status === 'locked' ? 'مقفل' : 'مسودة'));
                        @endphp
                        <x-filament::badge :color="$badgeColor">
                            {{ $badgeLabel }}
                        </x-filament::badge>
                        @if($isVoided)
                        <button 
                            wire:click="repostClosing({{ $closing->id }})"
                            class="mt-1 text-xs text-warning-600 hover:underline font-semibold block text-left w-full"
                        >
                            إعادة ترحيل الإغلاق
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
