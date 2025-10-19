<x-filament-panels::page>
    <div class="space-y-6">
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

        {{-- Current Financial Status --}}
        @php
            $user = auth()->user();
            $totalDebt = $user->merchants->sum('balance');
            $debtRatio = $user->salary && $user->salary > 0 ? ($totalDebt / $user->salary) * 100 : 0;
        @endphp

        @if ($user->salary && $user->salary > 0)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    نظرة عامة على وضعك المالي الحالي
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($user->salary, 2) }} ريال
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            راتبك الشهري
                        </div>
                    </div>

                    <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ number_format($totalDebt, 2) }} ريال
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            إجمالي ديونك
                        </div>
                    </div>

                    <div
                        class="text-center p-4 rounded-lg {{ $debtRatio >= 80 ? 'bg-red-50 dark:bg-red-900/20' : ($debtRatio >= 50 ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-green-50 dark:bg-green-900/20') }}">
                        <div
                            class="text-2xl font-bold {{ $debtRatio >= 80 ? 'text-red-600 dark:text-red-400' : ($debtRatio >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                            {{ number_format($debtRatio, 1) }}%
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            نسبة الديون من الراتب
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
