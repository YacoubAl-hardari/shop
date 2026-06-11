<x-filament-panels::page>
    @php
        $currency = \App\Helpers\CurrencyHelper::getSymbol();
        $kpis = $this->getKpis();
        $team = \Filament\Facades\Filament::getTenant();
    @endphp

    <!-- Landscape Print styling and utilities -->
    <style>
        @media print {
            /* 1. Reset base settings */
            html, body {
                background-color: #ffffff !important;
                background-image: none !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Cairo', 'Inter', sans-serif !important;
                font-size: 10pt !important;
                direction: rtl !important;
            }

            /* 2. Force A4 Landscape with proper margins */
            @page {
                size: A4 landscape;
                margin: 10mm 15mm 10mm 15mm;
            }

            /* 3. Hide all non-printable wrappers */
            .fi-sidebar, 
            .fi-topbar, 
            .fi-header,
            .fi-sidebar-close-overlay,
            .fi-topbar-placeholder,
            .no-print,
            nav,
            header,
            aside,
            footer,
            .fi-main-ctn > footer,
            .fi-modal,
            [role="dialog"],
            .fi-sidebar-open,
            button,
            input {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                width: 0 !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            /* 4. Reset main layout structure to occupy full printable area */
            .fi-main,
            .fi-main-ctn,
            .fi-content,
            .fi-body,
            .space-y-6,
            .grid {
                padding: 0 !important;
                margin: 0 !important;
                max-width: none !important;
                box-shadow: none !important;
                background: transparent !important;
                border: none !important;
            }

            /* 5. Force printable wrapper */
            .print-card {
                border: none !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                display: block !important;
            }

            /* 6. Typography & Table styles for print */
            h1, h2, h3, h4, h5, h6 {
                color: #000000 !important;
                font-weight: bold !important;
            }
            
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                page-break-inside: auto !important;
                margin-top: 15px !important;
            }

            tr {
                page-break-inside: avoid !important;
                page-break-after: auto !important;
            }

            th, td {
                padding: 6px 10px !important;
                border-bottom: 1px solid #d1d5db !important;
                color: #000000 !important;
                text-align: right !important;
            }

            th {
                background-color: #f3f4f6 !important;
                font-weight: bold !important;
                border-top: 1px solid #d1d5db !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            tfoot tr {
                border-top: 2px solid #000000 !important;
                border-bottom: 2px solid #000000 !important;
                font-weight: bold !important;
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            /* 7. Force grid printing side by side in landscape */
            .print-grid-cols-2 {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 20mm !important;
                width: 100% !important;
            }

            /* 8. Show print only elements */
            .print-only {
                display: block !important;
                visibility: visible !important;
            }
        }
    </style>

    <div class="space-y-6">
        <!-- Filter Bar (no-print) -->
        <div class="no-print rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <!-- Date Preset Buttons -->
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">الفترة:</span>
                    <button wire:click="setPreset('today')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-colors">
                        اليوم
                    </button>
                    <button wire:click="setPreset('month')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-colors">
                        هذا الشهر
                    </button>
                    <button wire:click="setPreset('quarter')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-colors">
                        هذا الربع
                    </button>
                    <button wire:click="setPreset('year')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-colors">
                        هذا العام
                    </button>
                    <button wire:click="setPreset('all')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 transition-colors">
                        الكل
                    </button>
                </div>

                <!-- Date Inputs -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500 dark:text-gray-400">من:</label>
                        <input type="date" wire:model.live="startDate" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs text-gray-500 dark:text-gray-400">إلى:</label>
                        <input type="date" wire:model.live="endDate" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 bg-white text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    <!-- Print Button -->
                    <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-1.5 text-xs font-bold bg-primary-600 hover:bg-primary-500 text-white rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        طباعة (A4 عرضي)
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Cards Summary (no-print) -->
        <div class="no-print grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Revenue Card -->
            <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 transition duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">إجمالي الإيرادات</span>
                        <h3 class="mt-1 text-2xl font-bold text-green-600 dark:text-green-500">
                            {{ number_format($kpis['revenue'], 2) }} <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                        </h3>
                    </div>
                    <div class="rounded-lg bg-green-50 p-2 text-green-600 dark:bg-green-950/50 dark:text-green-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 transition duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">إجمالي المصروفات</span>
                        <h3 class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-500">
                            {{ number_format($kpis['expenses'], 2) }} <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                        </h3>
                    </div>
                    <div class="rounded-lg bg-rose-50 p-2 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Net Profit Card -->
            <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 transition duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">صافي الربح</span>
                        <h3 class="mt-1 text-2xl font-bold {{ $kpis['net_income'] >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-red-600 dark:text-red-500' }}">
                            {{ number_format($kpis['net_income'], 2) }} <span class="text-xs font-normal text-gray-400">{{ $currency }}</span>
                        </h3>
                    </div>
                    <div class="rounded-lg {{ $kpis['net_income'] >= 0 ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400' : 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400' }}">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Profit Margin Card -->
            <div class="relative overflow-hidden rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 transition duration-300 hover:-translate-y-1 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">هامش الربح التشغيلي</span>
                        <h3 class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-500">
                            {{ number_format($kpis['margin'], 1) }}%
                        </h3>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs (no-print) -->
        <div class="no-print border-b border-gray-200 dark:border-gray-800">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'income_statement')" class="pb-4 text-sm font-bold border-b-2 transition-all {{ $activeTab === 'income_statement' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    قائمة الدخل
                </button>
                <button wire:click="$set('activeTab', 'trial_balance')" class="pb-4 text-sm font-bold border-b-2 transition-all {{ $activeTab === 'trial_balance' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    ميزان المراجعة
                </button>
                <button wire:click="$set('activeTab', 'balance_sheet')" class="pb-4 text-sm font-bold border-b-2 transition-all {{ $activeTab === 'balance_sheet' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}">
                    الميزانية العمومية
                </button>
            </nav>
        </div>

        <!-- Dynamic A4 Print Header (Visible only when printing) -->
        <div class="print-only hidden w-full mb-6 no-print:hidden">
            <div class="flex items-center justify-between border-b-2 border-gray-900 pb-4">
                <!-- Right: Merchant details -->
                <div class="text-right">
                    <h1 class="text-xl font-bold text-gray-900">{{ $team->name }}</h1>
                    <p class="text-xs text-gray-600 mt-1">المعرف الفريد: {{ $team->slug }}</p>
                    @if($team->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $team->description }}</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-0.5">العملة الأساسية: {{ \App\Helpers\CurrencyHelper::getName($team->currency ?? 'SAR') }}</p>
                </div>
                
                <!-- Center: Report Title -->
                <div class="text-center">
                    <h2 class="text-lg font-bold text-gray-950">
                        @if($activeTab === 'income_statement')
                            قائمة الدخل (الربح والخسارة)
                        @elseif($activeTab === 'trial_balance')
                            ميزان المراجعة للأرصدة
                        @else
                            الميزانية العمومية للمركز المالي
                        @endif
                    </h2>
                    <p class="text-xs text-gray-700 mt-1">
                        الفترة من: {{ $startDate ?? 'البداية' }} إلى: {{ $endDate ?? 'اليوم' }}
                    </p>
                    <p class="text-[9pt] text-gray-400 mt-0.5">تاريخ وتوقيت الاستخراج: {{ now()->format('Y-m-d H:i') }}</p>
                </div>

                <!-- Left: Logo -->
                <div class="text-left">
                    @if($team->avatar_url)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists($team->avatar_url) ? \Illuminate\Support\Facades\Storage::disk('public')->url($team->avatar_url) : asset('storage/' . $team->avatar_url) }}" alt="Logo" class="h-14 w-14 rounded-lg object-cover border border-gray-300 shadow-sm" />
                    @else
                        <!-- Elegant fallback monogram badge -->
                        <div class="h-14 w-14 rounded-lg bg-gray-100 flex items-center justify-center border border-gray-300">
                            <span class="text-sm font-bold text-gray-800">
                                {{ mb_substr($team->name, 0, 2, 'utf-8') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="grid gap-6">
            <!-- TAB 1: Income Statement -->
            @if ($activeTab === 'income_statement')
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 print-card">
                    <div class="flex items-center justify-between mb-6 no-print">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">قائمة الدخل (الربح والخسارة)</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">عرض ملخص للإيرادات والمصروفات وصافي الأرباح</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400">مكتمل</span>
                        </div>
                    </div>

                    @php $income = $this->getIncomeStatement(); @endphp
                    <div class="space-y-6">
                        <!-- Summary Progress Bar -->
                        <div class="no-print space-y-2">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>نسبة المصاريف للإيرادات</span>
                                <span>
                                    @if ($income['revenue'] > 0)
                                        {{ number_format(($income['expenses'] / $income['revenue']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2 overflow-hidden">
                                @php
                                    $expPercentage = $income['revenue'] > 0 ? min(100, ($income['expenses'] / $income['revenue']) * 100) : 0;
                                @endphp
                                <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $expPercentage }}%"></div>
                            </div>
                        </div>

                        <!-- Income Statement Details -->
                        <div class="space-y-4 print:space-y-2">
                            <!-- Revenue -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800 print:py-2">
                                <div class="flex items-center gap-3">
                                    <span class="p-2 rounded bg-green-50 text-green-600 dark:bg-green-950/30 dark:text-green-400 no-print">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">إجمالي إيرادات المبيعات</span>
                                </div>
                                <span class="text-base font-bold text-green-600 dark:text-green-500">{{ number_format($income['revenue'], 2) }} {{ $currency }}</span>
                            </div>

                            <!-- Expenses -->
                            <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800 print:py-2">
                                <div class="flex items-center gap-3">
                                    <span class="p-2 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400 no-print">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </span>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200">إجمالي تكلفة المبيعات والمصروفات</span>
                                </div>
                                <span class="text-base font-bold text-rose-600 dark:text-rose-500">- {{ number_format($income['expenses'], 2) }} {{ $currency }}</span>
                            </div>

                            <!-- Net Profit -->
                            <div class="flex justify-between items-center py-4 bg-gray-50 dark:bg-gray-800/40 rounded-xl px-4 mt-4 ring-1 ring-gray-900/5 dark:ring-white/5 print:bg-gray-100 print:py-3">
                                <span class="text-base font-extrabold text-gray-900 dark:text-white print:text-black">صافي الربح / الخسارة</span>
                                <span class="text-lg font-black {{ $income['net_income'] >= 0 ? 'text-indigo-600 dark:text-indigo-400 print:text-black' : 'text-red-600 dark:text-red-500 print:text-black' }}">
                                    {{ number_format($income['net_income'], 2) }} {{ $currency }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 2: Trial Balance -->
            @if ($activeTab === 'trial_balance')
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 print-card" x-data="{ search: '' }">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6 no-print">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">ميزان المراجعة</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">قائمة بالأرصدة الدائنة والمدينة لكافة الحسابات النشطة</p>
                        </div>
                        
                        <!-- Search Filter -->
                        <div class="no-print relative">
                            <input type="text" x-model="search" placeholder="بحث باسم الحساب أو الرمز..." class="w-full sm:w-64 px-3 py-1.5 pl-8 text-xs rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500" />
                            <div class="absolute left-2.5 top-2.5 text-gray-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    @php
                        $trialBalance = $this->getTrialBalance();
                        $totalDebit = collect($trialBalance)->sum('debit');
                        $totalCredit = collect($trialBalance)->sum('credit');
                        $isTrialBalanced = abs($totalDebit - $totalCredit) < 0.01;
                    @endphp

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800 text-gray-500 dark:text-gray-400 print:text-black">
                                    <th class="py-3 text-right font-medium">الرمز</th>
                                    <th class="py-3 text-right font-medium">الحساب</th>
                                    <th class="py-3 text-left font-medium">مدين</th>
                                    <th class="py-3 text-left font-medium">دائن</th>
                                    <th class="py-3 text-left font-medium">الرصيد النهائي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trialBalance as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors"
                                        x-show="search === '' || '{{ $row['name'] }}'.includes(search) || '{{ $row['code'] }}'.includes(search)">
                                        <td class="py-3 text-gray-600 dark:text-gray-400 font-mono print:text-black">{{ $row['code'] }}</td>
                                        <td class="py-3 text-gray-900 dark:text-white font-bold print:text-black">{{ $row['name'] }}</td>
                                        <td class="py-3 text-left text-green-600 dark:text-green-500 font-mono print:text-black">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</td>
                                        <td class="py-3 text-left text-rose-600 dark:text-rose-500 font-mono print:text-black">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</td>
                                        <td class="py-3 text-left font-semibold font-mono print:text-black">{{ number_format($row['balance'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-gray-400 print:text-black">لا توجد قيود مرحّلة في هذه الفترة لتوليد ميزان المراجعة.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            
                            @if(count($trialBalance) > 0)
                                <tfoot>
                                    <tr class="border-t-2 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30 font-bold print:bg-gray-100">
                                        <td colspan="2" class="py-3 text-right">المجموع النهائي</td>
                                        <td class="py-3 text-left text-green-600 dark:text-green-500 font-mono print:text-black">{{ number_format($totalDebit, 2) }}</td>
                                        <td class="py-3 text-left text-rose-600 dark:text-rose-500 font-mono print:text-black">{{ number_format($totalCredit, 2) }}</td>
                                        <td class="py-3 text-left font-mono">
                                            @if($isTrialBalanced)
                                                <span class="text-green-600 dark:text-green-400 print:text-black flex items-center justify-end gap-1 text-xs">
                                                    متوازن
                                                    <svg class="w-4 h-4 no-print" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </span>
                                            @else
                                                <span class="text-rose-600 dark:text-rose-400 print:text-black flex items-center justify-end gap-1 text-xs">
                                                    غير متوازن
                                                    <svg class="w-4 h-4 no-print" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endif

            <!-- TAB 3: Balance Sheet -->
            @if ($activeTab === 'balance_sheet')
                @php $balanceSheet = $this->getBalanceSheet(); @endphp
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 print-card">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6 no-print">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">الميزانية العمومية المبسطة</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">بيان بالمركز المالي للفرع: الأصول، الالتزامات وحقوق الملكية</p>
                        </div>

                        <!-- Balanced Indicator -->
                        <div class="flex items-center gap-2">
                            @if ($balanceSheet['is_balanced'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                    المعاملات المالية متوازنة
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    غير متزنة
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2 print-grid-cols-2">
                        <!-- Left Side: Assets -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-black text-gray-500 uppercase tracking-wider pb-2 border-b border-gray-200 dark:border-gray-800 print:text-black">الأصول (Assets)</h3>
                            <div class="space-y-3">
                                @forelse($balanceSheet['assets'] as $row)
                                    <div class="flex justify-between items-center py-2 text-sm border-b border-gray-50 dark:border-gray-800/30 print:text-black">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium print:text-black">{{ $row['name'] }} <span class="text-xs text-gray-400 font-mono">({{ $row['code'] }})</span></span>
                                        <span class="font-bold text-gray-900 dark:text-white font-mono print:text-black">{{ number_format($row['balance'], 2) }}</span>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400 py-3 text-center print:text-black">لا توجد أصول مسجلة حالياً</p>
                                @endforelse
                            </div>

                            <div class="flex justify-between items-center p-3 bg-green-50 dark:bg-green-950/20 rounded-lg font-bold text-sm text-green-700 dark:text-green-400 print:bg-gray-100 print:text-black">
                                <span>إجمالي الأصول</span>
                                <span class="font-mono">{{ number_format($balanceSheet['total_assets'], 2) }} {{ $currency }}</span>
                            </div>
                        </div>

                        <!-- Right Side: Liabilities & Equity -->
                        <div class="space-y-6">
                            <!-- Liabilities -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-black text-gray-500 uppercase tracking-wider pb-2 border-b border-gray-200 dark:border-gray-800 print:text-black">الالتزامات (Liabilities)</h3>
                                <div class="space-y-3">
                                    @forelse($balanceSheet['liabilities'] as $row)
                                        <div class="flex justify-between items-center py-2 text-sm border-b border-gray-50 dark:border-gray-800/30 print:text-black">
                                            <span class="text-gray-700 dark:text-gray-300 font-medium print:text-black">{{ $row['name'] }} <span class="text-xs text-gray-400 font-mono">({{ $row['code'] }})</span></span>
                                            <span class="font-bold text-gray-900 dark:text-white font-mono print:text-black">{{ number_format($row['balance'], 2) }}</span>
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-400 py-3 text-center print:text-black">لا توجد التزامات مسجلة حالياً</p>
                                    @endforelse
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 rounded-lg font-bold text-xs text-gray-600 dark:text-gray-300 print:bg-gray-50 print:text-black">
                                    <span>إجمالي الالتزامات</span>
                                    <span class="font-mono">{{ number_format($balanceSheet['total_liabilities'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Equity -->
                            <div class="space-y-4">
                                <h3 class="text-sm font-black text-gray-500 uppercase tracking-wider pb-2 border-b border-gray-200 dark:border-gray-800 print:text-black">حقوق الملكية (Equity)</h3>
                                <div class="space-y-3">
                                    @foreach($balanceSheet['equity'] as $row)
                                        <div class="flex justify-between items-center py-2 text-sm border-b border-gray-50 dark:border-gray-800/30 print:text-black">
                                            <span class="text-gray-700 dark:text-gray-300 font-medium print:text-black">{{ $row['name'] }} <span class="text-xs text-gray-400 font-mono">({{ $row['code'] }})</span></span>
                                            <span class="font-bold text-gray-900 dark:text-white font-mono print:text-black">{{ number_format($row['balance'], 2) }}</span>
                                        </div>
                                    @endforeach
                                    <!-- Add dynamic current net income -->
                                    <div class="flex justify-between items-center py-2 text-sm border-b border-gray-50 dark:border-gray-800/30 print:text-black font-semibold">
                                        <span class="text-gray-700 dark:text-gray-300 font-semibold italic print:text-black">صافي دخل الفترة الحالية</span>
                                        <span class="font-bold {{ $balanceSheet['net_income'] >= 0 ? 'text-green-600 dark:text-green-400 print:text-black' : 'text-rose-600 print:text-black' }} font-mono">{{ number_format($balanceSheet['net_income'], 2) }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 rounded-lg font-bold text-xs text-gray-600 dark:text-gray-300 print:bg-gray-50 print:text-black">
                                    <span>إجمالي حقوق الملكية</span>
                                    <span class="font-mono">{{ number_format($balanceSheet['total_equity'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Sum of Liabilities & Equity -->
                            <div class="flex justify-between items-center p-3 bg-indigo-50 dark:bg-indigo-950/20 rounded-lg font-bold text-sm text-indigo-700 dark:text-indigo-400 print:bg-gray-200 print:text-black">
                                <span>إجمالي الالتزامات وحقوق الملكية</span>
                                <span class="font-mono">{{ number_format($balanceSheet['total_liabilities_and_equity'], 2) }} {{ $currency }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
