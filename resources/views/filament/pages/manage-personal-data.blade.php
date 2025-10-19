<x-filament-panels::page>
    {{-- Export Info Section --}}
    <x-filament::section>
        <x-slot name="heading">
            تصدير البيانات
        </x-slot>

        <x-slot name="description">
            يمكنك تصدير جميع بياناتك بصيغتين مختلفتين حسب احتياجك.
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">تصدير JSON</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">للاسترجاع لاحقاً</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    استخدم هذا الخيار إذا كنت تريد حفظ نسخة احتياطية كاملة لاسترجاع بياناتك مستقبلاً.
                </p>
                <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <li>✓ محمي بتوقيع رقمي</li>
                    <li>✓ يمكن استرجاعه بالكامل</li>
                    <li>✓ يحفظ العلاقات بين البيانات</li>
                </ul>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/50">
                <div class="flex items-center gap-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">تصدير Excel</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">للعرض والتحليل</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    ملف Excel منظم بصفحات متعددة، مناسب للطباعة والمراجعة والتحليل.
                </p>
                <ul class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <li>✓ 9 صفحات منفصلة</li>
                    <li>✓ سهل القراءة والطباعة</li>
                    <li>✓ يمكن تحليله في Excel</li>
                </ul>
            </div>
        </div>
    </x-filament::section>

    {{-- Import Data Section --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            استرجاع البيانات
        </x-slot>

        <x-slot name="description">
            قم برفع ملف JSON الذي قمت بتصديره مسبقاً لاسترجاع جميع بياناتك.
        </x-slot>

        <form wire:submit.prevent="importDataAction" class="space-y-4">
            <div>
                <label for="importFile" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                        اختر ملف البيانات
                    </span>
                </label>

                <div class="mt-2">
                    <div
                        class="fi-fo-file-upload rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20">
                        <input type="file" id="importFile" wire:model="importFile" accept=".json,application/json"
                            class="block w-full text-sm text-gray-500 dark:text-gray-400 cursor-pointer
                            file:me-4 file:py-2.5 file:px-4
                            file:rounded-s-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-gray-50 file:text-gray-700
                            hover:file:bg-gray-100
                            dark:file:bg-gray-800 dark:file:text-gray-300
                            dark:hover:file:bg-gray-700
                            focus:outline-none" />
                    </div>

                    @error('importFile')
                        <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400 mt-2">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="fi-fo-field-wrp-hint text-sm text-gray-500 dark:text-gray-400 mt-2">
                        ملف JSON فقط، الحد الأقصى 10MB
                    </p>
                </div>
            </div>

            <div wire:loading wire:target="importFile"
                class="flex items-center gap-2 text-sm text-primary-600 dark:text-primary-400">
                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span>جاري رفع الملف...</span>
            </div>

            <x-filament::button type="submit" color="success">
                استرجاع البيانات
            </x-filament::button>
        </form>
    </x-filament::section>

    {{-- Delete Account Section --}}
    <x-filament::section collapsible collapsed class="mt-6">
        <x-slot name="heading">
            حذف الحساب نهائياً
        </x-slot>

        <x-slot name="description">
            حذف حسابك وجميع البيانات المرتبطة به بشكل نهائي. هذا الإجراء لا يمكن التراجع عنه.
        </x-slot>

        <div class="space-y-4">
            {{-- Warning Message --}}
            <div class="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-red-900 dark:text-red-100 mb-2">
                    تحذير: عملية دائمة
                </h4>
                <p class="text-sm text-red-800 dark:text-red-200 mb-3">
                    عند حذف حسابك، سيتم حذف جميع البيانات التالية بشكل نهائي:
                </p>
                <ul class="space-y-1 text-sm text-red-800 dark:text-red-200">
                    <li>• حساب المستخدم ومعلومات الدخول</li>
                    <li>• جميع التجار والموردين</li>
                    <li>• المنتجات والطلبات والفواتير</li>
                    <li>• كشوف الحسابات والمعاملات المالية</li>
                </ul>
            </div>

            {{-- Recommendation --}}
            <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-900 rounded-lg p-4">
                <p class="text-sm font-medium text-amber-900 dark:text-amber-100 mb-1">
                    نصيحة
                </p>
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    قم بتصدير بياناتك أولاً حتى تتمكن من استرجاعها لاحقاً إذا احتجت إليها.
                </p>
            </div>

            {{-- Delete Form --}}
            <form wire:submit.prevent="deleteAccountAction" class="space-y-4">
                <div>
                    <label for="deletePassword" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                            كلمة المرور للتأكيد
                        </span>
                    </label>

                    <div class="mt-2">
                        <div
                            class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 ring-gray-950/10 dark:ring-white/20 focus-within:ring-2 focus-within:ring-primary-600 dark:focus-within:ring-primary-500">
                            <div class="min-w-0 flex-1">
                                <input type="password" id="deletePassword" wire:model="deletePassword"
                                    placeholder="أدخل كلمة المرور" required
                                    class="fi-input block w-full border-none bg-transparent px-3 py-2.5 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6" />
                            </div>
                        </div>

                        @error('deletePassword')
                            <p class="fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400 mt-2">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <x-filament::button type="submit" color="danger">
                    حذف حسابي نهائياً
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>

    {{-- Info Section --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            البيانات المشمولة
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">بيانات الحساب</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">معلومات المستخدم والإعدادات</p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">التجار والموردين</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">جميع العلاقات التجارية</p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">المنتجات</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">كافة البيانات التجارية</p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">الطلبات والفواتير</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">السجل الكامل للمعاملات</p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">كشوف الحسابات</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">جميع السجلات المحاسبية</p>
                </div>
            </div>

            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">معاملات الدفع</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">سجل الدفع والتحويلات</p>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Security Info --}}
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            الأمان والخصوصية
        </x-slot>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">مصادقة إلزامية</span>
            </div>

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">تشفير البيانات</span>
            </div>

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">تسجيل العمليات</span>
            </div>

            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-400">Transactions آمنة</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
