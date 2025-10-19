<x-filament-panels::page>
    <div class="space-y-6">

    
        {{-- Current Financial Status --}}
        @php
            $user = auth()->user();
            $totalDebt = $user->merchants->sum('balance');
            $debtRatio = $user->salary && $user->salary > 0 ? ($totalDebt / $user->salary) * 100 : 0;
            $remainingAmount = $user->salary - $totalDebt;
            $warningThreshold = $user->salary * ($user->debt_warning_percentage / 100);
            $dangerThreshold = $user->salary * ($user->debt_danger_percentage / 100);

            // Status determination
            $statusColor = 'green';
            $statusIcon = 'check-circle';
            $statusText = 'وضع مالي جيد';
            $statusBg = 'bg-green-50 dark:bg-green-900/30';
            $statusBorder = 'border-green-300 dark:border-green-700';

            if ($totalDebt >= $dangerThreshold) {
                $statusColor = 'red';
                $statusIcon = 'exclamation-circle';
                $statusText = 'تحذير: ديون مرتفعة';
                $statusBg = 'bg-red-50 dark:bg-red-900/30';
                $statusBorder = 'border-red-300 dark:border-red-700';
            } elseif ($totalDebt >= $warningThreshold) {
                $statusColor = 'yellow';
                $statusIcon = 'exclamation-triangle';
                $statusText = 'تنبيه: راقب ديونك';
                $statusBg = 'bg-yellow-50 dark:bg-yellow-900/30';
                $statusBorder = 'border-yellow-300 dark:border-yellow-700';
            }
        @endphp

        @if ($user->salary && $user->salary > 0)
            <div
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                {{-- Header with Status --}}
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-5 dark:border-gray-700 dark:bg-gray-900/50">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-primary-500 p-2.5">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    نظرة عامة على وضعك المالي
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    تحديث فوري لحالتك المالية الحالية
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-2 rounded-lg border px-4 py-2 {{ $statusBorder }} {{ $statusBg }}">
                            <svg class="h-5 w-5 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($statusIcon === 'check-circle')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @elseif ($statusIcon === 'exclamation-triangle')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                @endif
                            </svg>
                            <span
                                class="text-sm font-semibold text-{{ $statusColor }}-700 dark:text-{{ $statusColor }}-300">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Main Stats Cards --}}
                <div class="p-6">
                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        {{-- Salary Card --}}
                        <div class="relative overflow-hidden rounded-lg bg-blue-500 p-6 text-white shadow-md">
                            <div class="relative z-10">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ number_format($user->salary, 0) }}
                                    <span class="text-base font-normal opacity-90">ريال</span>
                                </div>
                                <div class="text-sm font-medium opacity-90">
                                    راتبك الشهري
                                </div>
                            </div>
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                        </div>

                        {{-- Total Debt Card --}}
                        <div class="relative overflow-hidden rounded-lg bg-red-500 p-6 text-white shadow-md">
                            <div class="relative z-10">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ number_format($totalDebt, 0) }}
                                    <span class="text-base font-normal opacity-90">ريال</span>
                                </div>
                                <div class="text-sm font-medium opacity-90">
                                    إجمالي الديون
                                </div>
                            </div>
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                        </div>

                        {{-- Remaining Amount Card --}}
                        <div class="relative overflow-hidden rounded-lg bg-green-500 p-6 text-white shadow-md">
                            <div class="relative z-10">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ number_format(max(0, $remainingAmount), 0) }}
                                    <span class="text-base font-normal opacity-90">ريال</span>
                                </div>
                                <div class="text-sm font-medium opacity-90">
                                    المبلغ المتبقي
                                </div>
                            </div>
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                        </div>

                        {{-- Debt Ratio Card --}}
                        <div class="relative overflow-hidden rounded-lg bg-purple-500 p-6 text-white shadow-md">
                            <div class="relative z-10">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-white/20">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-2xl font-bold">
                                    {{ number_format($debtRatio, 1) }}%
                                </div>
                                <div class="text-sm font-medium opacity-90">
                                    نسبة الديون من الراتب
                                </div>
                            </div>
                            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                        </div>
                    </div>

                    {{-- Debt Progress Indicator --}}
                    <div
                        class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-900/50">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">مؤشر الديون</h4>
                                <p class="text-xs text-gray-600 dark:text-gray-400">نسبة ديونك من راتبك الشهري</p>
                            </div>
                            <div class="text-right">
                                <div
                                    class="text-2xl font-bold {{ $debtRatio >= 80 ? 'text-red-600 dark:text-red-400' : ($debtRatio >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                    {{ number_format($debtRatio, 1) }}%
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">من راتبك</div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="relative">
                            <div class="mb-2 flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                <span>0%</span>
                                <span>{{ $user->debt_warning_percentage }}%</span>
                                <span>{{ $user->debt_danger_percentage }}%</span>
                                <span>100%</span>
                            </div>
                            <div class="relative h-6 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                {{-- Progress Fill --}}
                                <div class="h-full transition-all duration-500 {{ $debtRatio >= 80 ? 'bg-red-500' : ($debtRatio >= 50 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                    style="width: {{ min($debtRatio, 100) }}%">
                                </div>

                                {{-- Warning Marker --}}
                                <div class="absolute bottom-0 top-0 w-0.5 bg-yellow-600"
                                    style="right: {{ 100 - $user->debt_warning_percentage }}%"></div>

                                {{-- Danger Marker --}}
                                <div class="absolute bottom-0 top-0 w-0.5 bg-red-600"
                                    style="right: {{ 100 - $user->debt_danger_percentage }}%"></div>
                            </div>
                        </div>

                        {{-- Legend --}}
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-xs">
                            <div class="flex items-center gap-1.5">
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                <span class="text-gray-700 dark:text-gray-300">آمن (أقل من
                                    {{ $user->debt_warning_percentage }}%)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                                <span class="text-gray-700 dark:text-gray-300">تحذير
                                    ({{ $user->debt_warning_percentage }}-{{ $user->debt_danger_percentage }}%)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                <span class="text-gray-700 dark:text-gray-300">خطر (أكثر من
                                    {{ $user->debt_danger_percentage }}%)</span>
                            </div>
                        </div>
                    </div>

                    {{-- Financial Limits --}}
                    @if ($user->max_debt_limit || $user->min_spending_limit || $user->max_spending_limit)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @if ($user->max_debt_limit)
                                <div
                                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ number_format($user->max_debt_limit, 0) }} ريال
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                الحد الأقصى للديون
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($user->min_spending_limit)
                                <div
                                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ number_format($user->min_spending_limit, 0) }} ريال
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                الحد الأدنى للمشتريات
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($user->max_spending_limit)
                                <div
                                    class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ number_format($user->max_spending_limit, 0) }} ريال
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                الحد الأقصى للمشتريات
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
        {{-- Info Card --}}
        <div
            class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-primary-900 dark:text-primary-100">
                        لماذا الإعدادات المالية مهمة؟
                    </h3>
                    <div class="mt-2 text-sm text-primary-700 dark:text-primary-300 space-y-1">
                        <p>• <strong>المراقبة الذكية:</strong> احصل على تنبيهات فورية عند تجاوز حدودك المالية</p>
                        <p>• <strong>تقييم المخاطر:</strong> شاهد نسبة ديونك مقارنة براتبك في كل تاجر</p>
                        <p>• <strong>الوقاية من الديون:</strong> تحكم في مشترياتك قبل أن تتراكم الديون</p>
                        <p>• <strong>اتخاذ قرارات أفضل:</strong> بيانات واضحة تساعدك على التخطيط المالي</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end gap-3 mt-6">
                <x-filament::button type="submit">
                    حفظ الإعدادات
                </x-filament::button>
            </div>
        </form>

    </div>
</x-filament-panels::page>
