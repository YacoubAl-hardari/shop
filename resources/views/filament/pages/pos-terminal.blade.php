<x-filament-panels::page>
    <!-- Load QRious QR Code generator library from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>

    <style>
    @media print {
        @page {
            margin: 0;
            size: auto;
        }
        body {
            visibility: hidden !important;
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        /* Make only the receipt modal content and its children visible */
        #pos-receipt-modal-content, #pos-receipt-modal-content * {
            visibility: visible !important;
        }
        #pos-receipt-modal-content {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: var(--receipt-width, 80mm) !important;
            padding: 4mm !important;
            margin: 0 !important;
            background: white !important;
            color: black !important;
            box-shadow: none !important;
            border: none !important;
            display: block !important;
        }
        /* Hide screen-only elements */
        .no-print {
            display: none !important;
            visibility: hidden !important;
        }
        /* Clear parent backgrounds and borders for a clean print */
        .fi-layout, .fi-main, .print-modal-backdrop, .print-modal-card {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }
    }
    </style>

    <div x-data="{ activeTab: 'products' }" class="relative">
        
        <!-- Mobile Tab Switcher (Visible only on mobile/tablet) -->
        <div class="lg:hidden flex bg-gray-100 dark:bg-gray-950 p-1 rounded-xl mb-4 border border-gray-200 dark:border-gray-800">
            <button 
                type="button"
                @click="activeTab = 'products'"
                :class="activeTab === 'products' ? 'bg-white dark:bg-gray-900 shadow text-primary-600 dark:text-primary-400 font-bold' : 'text-gray-600 dark:text-gray-400'"
                class="flex-1 py-2 text-center text-sm rounded-lg transition"
            >
                المنتجات
            </button>
            <button 
                type="button"
                @click="activeTab = 'cart'"
                :class="activeTab === 'cart' ? 'bg-white dark:bg-gray-900 shadow text-primary-600 dark:text-primary-400 font-bold' : 'text-gray-600 dark:text-gray-400'"
                class="flex-1 py-2 text-center text-sm rounded-lg transition flex items-center justify-center gap-2"
            >
                <span>السلة والدفع</span>
                @if(count($this->data['items'] ?? []) > 0)
                    <span class="bg-primary-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                        {{ count($this->data['items']) }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Desktop POS Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Right side (Product Catalog) - 7 columns -->
            <!-- On mobile, shown only if activeTab is 'products' -->
            <div 
                :class="activeTab === 'products' ? 'block' : 'hidden lg:block'"
                class="lg:col-span-7 space-y-6 lg:h-[calc(100vh-8.5rem)] lg:flex lg:flex-col"
            >
                <!-- Top Search Panel (Fixed at top of catalog) -->
                <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 shrink-0">
                    <div class="relative">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                            <!-- Search SVG icon -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search" 
                            placeholder="ابحث باسم المنتج أو الباركود..." 
                            class="w-full pr-10 pl-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                        />
                    </div>
                </div>

                <!-- Product Catalog Grid (Scrollable on desktop) -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 flex-1 lg:overflow-hidden lg:flex lg:flex-col min-h-[450px]">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-3 flex items-center gap-2 shrink-0">
                        <svg class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>كتالوج المنتجات</span>
                    </h3>
                    
                    @php
                        $products = $this->getProducts();
                    @endphp

                    @if($products->isEmpty())
                        <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500 space-y-3 flex-1">
                            <svg class="h-16 w-16 stroke-1 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p class="text-base font-medium">لم يتم العثور على أي منتجات مطابقة</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 flex-1 lg:overflow-y-auto pr-1 pb-4 content-start">
                            @foreach($products as $product)
                                @php
                                    $inStock = (float)$product->stock_quantity > 0;
                                @endphp
                                <button 
                                    type="button"
                                    wire:click="addProduct({{ $product->id }})"
                                    @if(!$inStock) disabled @endif
                                    class="w-full flex flex-col text-right justify-between p-4 rounded-xl border transition duration-200 relative group overflow-hidden
                                        @if($inStock)
                                            bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md hover:-translate-y-0.1
                                        @else
                                            bg-gray-50 dark:bg-gray-900 border-gray-150 dark:border-gray-800 opacity-60 cursor-not-allowed
                                        @endif"
                                >
                                    <div class="absolute inset-0 bg-primary-50/10 dark:bg-primary-500/5 opacity-0 group-hover:opacity-100 transition duration-200"></div>
                                    
                                    <div class="relative z-10 space-y-2 w-full">
                                        <!-- Stock level badge -->
                                        <div class="flex justify-between items-center w-full">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                                @if($inStock)
                                                    bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400
                                                @else
                                                    bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400
                                                @endif"
                                            >
                                                @if($inStock)
                                                    {{ number_format($product->stock_quantity, 0) }} {{ $product->unit ?? 'قطعة' }}
                                                @else
                                                    نفذت
                                                @endif
                                            </span>
                                            
                                            @if(filled($product->barcode))
                                                <span class="text-[9px] text-gray-400 dark:text-gray-500 font-mono">
                                                    {{ $product->barcode }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Product Name -->
                                        <div class="font-bold text-sm text-gray-800 dark:text-gray-200 line-clamp-2 h-10 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                            {{ $product->name }}
                                        </div>
                                    </div>

                                    <!-- Product Price -->
                                    <div class="relative z-10 flex justify-between items-center mt-3 pt-2 border-t border-gray-100 dark:border-gray-800/50 w-full">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">السعر</span>
                                        <span class="font-extrabold text-base text-primary-600 dark:text-primary-400">
                                            {{ number_format($product->price, 2) }} <span class="text-xs font-normal">ر.س</span>
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Mobile Floating Bottom Bar (visible on mobile only, when activeTab is products and cart has items) -->
                @if(count($this->data['items'] ?? []) > 0)
                    <div class="lg:hidden fixed bottom-4 left-4 right-4 z-40 bg-primary-600 text-white p-4 rounded-2xl shadow-xl flex items-center justify-between">
                        <div>
                            <span class="text-xs opacity-90 block">إجمالي السلة</span>
                            <span class="text-lg font-black font-mono">
                                {{ number_format($this->calculateTotal($this->data['items'] ?? []), 2) }} ر.س
                            </span>
                        </div>
                        <button 
                            type="button" 
                            @click="activeTab = 'cart'" 
                            class="bg-white text-primary-700 px-4 py-2 rounded-xl font-bold text-sm shadow hover:bg-gray-50 transition flex items-center gap-2"
                        >
                            <span>عرض السلة</span>
                            <span class="bg-primary-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                {{ count($this->data['items']) }}
                            </span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Left side (Shopping Cart & Settlement Form) - 5 columns -->
            <!-- On mobile, shown only if activeTab is 'cart' -->
            <form 
                wire:submit="completeSale"
                :class="activeTab === 'cart' ? 'block' : 'hidden lg:block'"
                class="lg:col-span-5 lg:h-[calc(100vh-8.5rem)] lg:flex lg:flex-col lg:sticky lg:top-[5.5rem] bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden"
            >
                <!-- Cart Header (Fixed at top of sidebar) -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-950/50 shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="font-bold text-gray-800 dark:text-white text-base">سلة المبيعات</span>
                        <span class="bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-400 text-xs px-2.5 py-0.5 rounded-full font-bold">
                            {{ count($this->data['items'] ?? []) }} أصناف
                        </span>
                    </div>
                    
                    @if(count($this->data['items'] ?? []) > 0)
                        <button 
                            type="button" 
                            wire:click="clearCart" 
                            class="text-xs text-rose-600 dark:text-rose-400 font-semibold hover:underline flex items-center gap-1"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            مسح السلة
                        </button>
                    @endif
                </div>

                <!-- Scrollable Body (Contains Cart Items and Filament Form) -->
                <div class="flex-1 overflow-y-auto p-4 space-y-6">
                    
                    <!-- Cart Items List Container -->
                    <div class="bg-gray-50 dark:bg-gray-950 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
                        <div class="max-h-[200px] lg:max-h-[240px] overflow-y-auto divide-y divide-gray-200 dark:divide-gray-800 pr-1">
                            @forelse($this->data['items'] ?? [] as $index => $item)
                                <div class="py-3 flex items-center justify-between gap-4 hover:bg-white dark:hover:bg-gray-900/30 transition first:pt-0 last:pb-0">
                                    <!-- Right: Product Name & Unit Price -->
                                    <div class="flex-1 min-w-0 text-right space-y-1">
                                        <div class="font-bold text-sm text-gray-900 dark:text-white truncate">
                                            {{ $item['product_name'] }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <span>سعر الوحدة:</span>
                                            <span class="font-semibold">{{ number_format($item['unit_price'], 2) }} ر.س</span>
                                        </div>
                                    </div>

                                    <!-- Middle: Quantity controls (Sleek unified pills) -->
                                    <div class="flex items-center bg-gray-250/80 dark:bg-gray-800 rounded-lg p-0.5 shrink-0 select-none">
                                        <button 
                                            type="button"
                                            wire:click="decrementQuantity({{ $index }})"
                                            class="w-7 h-7 rounded-md flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 hover:text-gray-950 dark:hover:text-white transition font-black"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        
                                        <input 
                                            type="number" 
                                            wire:model.live="data.items.{{ $index }}.quantity" 
                                            class="w-10 text-center bg-transparent border-0 p-0 text-sm font-bold text-gray-900 dark:text-white focus:ring-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                            min="0.01"
                                            step="any"
                                        />
                                        
                                        <button 
                                            type="button"
                                            wire:click="incrementQuantity({{ $index }})"
                                            class="w-7 h-7 rounded-md flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-700 hover:text-gray-950 dark:hover:text-white transition font-black"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Left: Subtotal & Delete -->
                                    <div class="flex items-center gap-2 min-w-[90px] justify-end shrink-0">
                                        <span class="font-extrabold text-sm text-gray-900 dark:text-white font-mono">
                                            {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }}
                                        </span>
                                        <button 
                                            type="button"
                                            wire:click="removeItem({{ $index }})"
                                            class="text-gray-400 hover:text-rose-600 dark:hover:text-rose-455 p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/20 transition duration-150"
                                            title="حذف الصنف"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-10 text-center text-gray-400 dark:text-gray-500 space-y-2">
                                    <svg class="h-12 w-12 mx-auto stroke-1 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <p class="text-sm font-medium">السلة فارغة. اختر منتجات للبدء</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Collapsible Currency Exchange Section (المصارفة) -->
                    <div 
                        x-data="{ 
                            open: false, 
                            currency: 'SAR', 
                            amount: '', 
                            rate: 140,
                            get localAmount() {
                                let amt = parseFloat(this.amount) || 0;
                                let rt = parseFloat(this.rate) || 0;
                                return Math.round(amt * rt * 100) / 100;
                            },
                            applyToTendered() {
                                $wire.set('data.cash_tendered', this.localAmount);
                                this.open = false;
                            }
                        }" 
                        class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition mb-4 no-print"
                    >
                        <!-- Section Header (Click to toggle) -->
                        <button 
                            type="button" 
                            @click="open = !open" 
                            class="w-full flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-950 hover:bg-gray-100 dark:hover:bg-gray-900/50 transition select-none text-right"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-bold text-sm text-gray-800 dark:text-white">المصارفة (حاسبة العملات الأجنبية)</span>
                            </div>
                            <svg 
                                class="h-4 w-4 text-gray-500 transition-transform duration-200" 
                                :class="open ? 'transform rotate-180' : ''" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Section Content (Collapsible) -->
                        <div 
                            x-show="open" 
                            x-collapse 
                            class="p-4 border-t border-gray-150 dark:border-gray-800 space-y-4 bg-white dark:bg-gray-900"
                        >
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <!-- Currency Dropdown -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">العملة الأجنبية</label>
                                    <select 
                                        x-model="currency" 
                                        @change="rate = (currency === 'SAR' ? 140 : 530)"
                                        class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-transparent p-2"
                                    >
                                        <option value="SAR">ريال سعودي (SAR)</option>
                                        <option value="USD">دولار أمريكي (USD)</option>
                                    </select>
                                </div>

                                <!-- Foreign Amount Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">المبلغ بالأجنبي</label>
                                    <input 
                                        type="number" 
                                        x-model="amount" 
                                        placeholder="المبلغ..."
                                        class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-transparent p-2 text-left font-mono"
                                    />
                                </div>

                                <!-- Exchange Rate Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 mb-1">سعر الصرف</label>
                                    <input 
                                        type="number" 
                                        x-model="rate" 
                                        placeholder="سعر الصرف..."
                                        class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-transparent p-2 text-left font-mono"
                                    />
                                </div>
                            </div>

                            <!-- Result & Apply Action -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-950 rounded-lg border border-gray-150 dark:border-gray-850">
                                <div>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 block">المقابل بالعملة المحلية</span>
                                    <span class="text-base font-extrabold text-primary-600 dark:text-primary-400 font-mono" x-text="localAmount.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' ر.س'"></span>
                                </div>
                                
                                <button 
                                    type="button" 
                                    @click="applyToTendered()"
                                    :disabled="localAmount <= 0"
                                    :class="localAmount <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500' : 'bg-primary-600 hover:bg-primary-500 text-white shadow-sm'"
                                    class="px-3 py-1.5 text-xs rounded-lg font-bold transition flex items-center gap-1.5"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <span>تطبيق كمبلغ مستلم</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filament Form (Customer, Payment, Notes, totals placeholder) -->
                    <div class="space-y-4">
                        {{ $this->form }}
                    </div>
                </div>

                <!-- Complete Sale Actions Footer (Fixed at the bottom of sidebar) -->
                <div class="p-4 bg-gray-50 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800 shrink-0">
                    @if (! $this->canCompleteSale())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-xs text-red-600 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400 mb-3">
                            {{ $this->getCompleteSaleBlockReason() }}
                        </div>
                    @endif

                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 bg-primary-600 hover:bg-primary-500 text-white rounded-xl font-bold shadow-lg shadow-primary-500/10 flex items-center justify-center gap-2 transition"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>إتمام عملية البيع</span>
                    </button>
                </div>
            </form>
            
        </div>
    </div>

    <!-- Receipt Print Modal -->
    @if($showReceiptId && ($receipt = $this->getReceiptSale()))
        @php
            $taxNumber = $this->getMerchantTaxNumber();
        @endphp
        <div 
            x-data="{ show: true, receiptWidth: '80mm' }" 
            x-show="show" 
            @open-receipt-modal.window="show = true"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-sm print-modal-backdrop"
            defer
        >
            <div 
                @click.away="show = false; $wire.closeReceipt()" 
                class="bg-white dark:bg-gray-900 rounded-3xl max-w-md w-full shadow-2xl border border-gray-150 dark:border-gray-800 overflow-hidden flex flex-col max-h-[90vh] print-modal-card"
            >
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-150 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-950 no-print">
                    <h3 class="text-base font-bold text-gray-950 dark:text-white">فاتورة مبيعات POS</h3>
                    <button 
                        type="button" 
                        @click="show = false; $wire.closeReceipt()" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Print Size Toggle -->
                <div class="flex items-center justify-center gap-2 p-3 bg-gray-50 dark:bg-gray-950 border-b border-gray-150 dark:border-gray-800 no-print">
                    <span class="text-xs text-gray-500 font-bold">عرض الفاتورة:</span>
                    <button 
                        type="button" 
                        @click="receiptWidth = '80mm'" 
                        :class="receiptWidth === '80mm' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        class="px-3 py-1 text-xs rounded-lg font-bold transition"
                    >
                        80 مم (قياسي)
                    </button>
                    <button 
                        type="button" 
                        @click="receiptWidth = '58mm'" 
                        :class="receiptWidth === '58mm' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                        class="px-3 py-1 text-xs rounded-lg font-bold transition"
                    >
                        58 مم (صغير)
                    </button>
                </div>

                <!-- Modal Body / Scrollable Receipt Container -->
                <div class="flex-1 overflow-y-auto p-6 flex justify-center bg-gray-100 dark:bg-gray-950">
                    <!-- Receipt Paper representation -->
                    <div 
                        id="pos-receipt-modal-content"
                        :style="'--receipt-width: ' + receiptWidth + '; width: ' + receiptWidth + ';'"
                        class="bg-white text-black p-5 shadow border border-gray-200 font-sans leading-relaxed text-right select-all"
                        style="font-family: 'JannaLT', sans-serif; direction: rtl;"
                    >
                        <!-- Logo & Store Info -->
                        <div class="text-center space-y-1">
                            @if(\Filament\Facades\Filament::getTenant()->avatar_url)
                                <img src="{{ asset("storage/" . \Filament\Facades\Filament::getTenant()->avatar_url) }}" class="h-14 w-14 rounded-full mx-auto mb-2 object-cover border border-gray-200" alt="logo" />
                            @else
                                <div class="h-14 w-14 rounded-full bg-gray-100 text-gray-800 font-black text-xl flex items-center justify-center mx-auto mb-2 border border-gray-300">
                                    {{ mb_substr(\Filament\Facades\Filament::getTenant()->name, 0, 1, 'utf-8') }}
                                </div>
                            @endif
                            
                            <h2 class="text-base font-black tracking-wide">{{ \Filament\Facades\Filament::getTenant()->name }}</h2>
                            <div class="bg-gray-100 text-gray-800 text-[10px] font-bold py-0.5 px-2 rounded-full inline-block">
                                {{ $taxNumber ? 'فاتورة تبسيطية ضريبية' : 'فاتورة مبيعات' }}
                            </div>
                            <p class="text-[9px] text-gray-500 font-medium">{{ $taxNumber ? 'Simplified Tax Invoice' : 'Sales Invoice' }}</p>
                        </div>

                        <!-- Store details (Tax Number, CR) -->
                        <div class="text-[10px] text-gray-800 space-y-0.5 border-t border-b border-dashed border-gray-300 py-2 my-2 text-right">
                            @if($taxNumber)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">الرقم الضريبي (VAT):</span>
                                    <span class="font-mono font-bold">{{ $taxNumber }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-500">السجل التجاري (CR):</span>
                                <span class="font-mono">1010000000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">العنوان:</span>
                                <span>صنعاء - اليمن / الرياض - السعودية</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">الهاتف:</span>
                                <span class="font-mono">777777777</span>
                            </div>
                        </div>

                        <!-- Invoice metadata -->
                        <div class="space-y-0.5 text-[10px] text-gray-800 pb-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">رقم الفاتورة (Inv No):</span>
                                <span class="font-mono font-black text-xs">{{ $receipt->sale_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">رقم الطلب (Order ID):</span>
                                <span class="font-mono">#{{ $receipt->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">التاريخ والوقت:</span>
                                <span>{{ $receipt->created_at->format('Y-m-d h:i A') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">الكاشير (Cashier):</span>
                                <span>{{ $receipt->seller->name ?? 'نظام البيع' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">العميل (Customer):</span>
                                <span class="font-bold">{{ $receipt->merchantCustomer->name ?? 'عميل سفري / نقدي' }}</span>
                            </div>
                            @if($receipt->merchantCustomer && $receipt->merchantCustomer->phone)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">هاتف العميل:</span>
                                    <span class="font-mono">{{ $receipt->merchantCustomer->phone }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Items Table -->
                        <div class="border-t border-dashed border-black pt-2">
                            <table class="w-full text-[10px] border-collapse text-right">
                                <thead>
                                    <tr class="border-b border-dashed border-black font-bold text-gray-900">
                                        <th class="pb-1 text-right">الصنف (Item)</th>
                                        <th class="pb-1 text-center w-8">ك (Qty)</th>
                                        <th class="pb-1 text-left w-14">سعر (Unit)</th>
                                        <th class="pb-1 text-left w-16">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-dashed divide-gray-200">
                                    @foreach($receipt->items as $item)
                                        <tr class="align-top">
                                            <td class="py-1 text-right font-medium leading-tight">
                                                {{ $item->product_name }}
                                            </td>
                                            <td class="py-1 text-center font-mono font-bold">
                                                {{ number_format($item->quantity, 0) }}
                                            </td>
                                            <td class="py-1 text-left font-mono">
                                                {{ number_format($item->unit_price, 2) }}
                                            </td>
                                            <td class="py-1 text-left font-mono font-extrabold">
                                                {{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Summary / Totals -->
                        @php
                            $total = (float) $receipt->total_amount;
                            $creditApplied = (float) $receipt->customer_credit_applied;
                            $netDue = $total - $creditApplied;
                            $vatRate = 0.15; // 15% VAT
                            $vatAmount = ($total * 15) / 115;
                            $taxableAmount = $total - $vatAmount;
                        @endphp
                        <div class="space-y-1 pt-2 border-t border-dashed border-black text-[10px]">
                            <div class="flex justify-between">
                                <span>إجمالي الأصناف{{ $taxNumber ? ' (شامل الضريبة)' : '' }}:</span>
                                <span class="font-mono font-bold">{{ number_format($total, 2) }} ر.س</span>
                            </div>
                            @if($taxNumber)
                                <div class="flex justify-between text-gray-600">
                                    <span>المبلغ الخاضع للضريبة (Taxable):</span>
                                    <span class="font-mono">{{ number_format($taxableAmount, 2) }} ر.س</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>ضريبة القيمة المضافة (VAT 15%):</span>
                                    <span class="font-mono">{{ number_format($vatAmount, 2) }} ر.س</span>
                                </div>
                            @endif
                            @if($creditApplied > 0)
                                <div class="flex justify-between text-emerald-700 font-bold">
                                    <span>الخصم / الرصيد المستخدم:</span>
                                    <span class="font-mono">- {{ number_format($creditApplied, 2) }} ر.س</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-xs font-black border-t border-double border-black pt-1.5 mt-1">
                                <span>صافي الفاتورة (Net Total):</span>
                                <span class="font-mono">{{ number_format($netDue, 2) }} ر.س</span>
                            </div>
                        </div>

                        <!-- Payment & Cashier Details -->
                        <div class="space-y-0.5 pt-2 border-t border-dashed border-black text-[10px] border-b pb-2 mb-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">طريقة الدفع (Pay Mode):</span>
                                <span class="font-bold">{{ $this->getPaymentMethodLabel($receipt->payment_method) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">نوع التسوية (Settlement):</span>
                                <span>{{ $this->getPaymentTypeLabel($receipt->payment_type) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">المبلغ المستلم (Received):</span>
                                <span class="font-mono font-bold">{{ number_format($receiptCashTendered ?? $receipt->paid_amount, 2) }} ر.س</span>
                            </div>
                            @if($receipt->credit_amount > 0)
                                <div class="flex justify-between text-rose-700 font-bold">
                                    <span>المتبقي في الذمة (Due):</span>
                                    <span class="font-mono">{{ number_format($receipt->credit_amount, 2) }} ر.س</span>
                                </div>
                            @else
                                <div class="flex justify-between">
                                    <span class="text-gray-500">المرتجع / الفكة (Change):</span>
                                    <span class="font-mono font-bold">{{ number_format(max(0, ($receiptCashTendered ?? $receipt->paid_amount) - $netDue), 2) }} ر.س</span>
                                </div>
                            @endif
                            @if($receipt->payment_reference)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">مرجع العملية:</span>
                                    <span class="font-mono">{{ $receipt->payment_reference }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- ZATCA QR Code & Greetings -->
                        <div 
                            @if($taxNumber)
                            x-init="
                                $nextTick(() => {
                                    new QRious({
                                        element: $refs.qrCanvas,
                                        value: '{{ $this->getReceiptQrCodeValue($receipt) }}',
                                        size: 150,
                                        level: 'M'
                                    });
                                });
                            "
                            @endif
                            class="flex flex-col items-center justify-center space-y-2 pt-2 text-center"
                        >
                            @if($taxNumber)
                                <canvas x-ref="qrCanvas" class="mx-auto bg-white p-1 border border-gray-200 rounded-lg" style="width: 100px; height: 100px;"></canvas>
                            @endif
                            
                            <div class="text-[9px] text-gray-550 space-y-0.5 font-bold">
                                @if($taxNumber)
                                    <p class="text-black">فاتورة ضريبية مبسطة رقمية</p>
                                @endif
                                <p>شكراً لتعاملكم معنا</p>
                                <p>يسعدنا خدمتكم دائماً</p>
                                <p class="text-[8px] font-normal text-gray-400 mt-1">البضاعة المباعة تسترجع وتستبدل خلال 7 أيام</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions Footer -->
                <div class="p-4 border-t border-gray-150 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 flex gap-3 shrink-0 no-print">
                    <button 
                        type="button" 
                        onclick="window.print()" 
                        class="flex-1 py-2 px-4 bg-primary-600 hover:bg-primary-500 text-white rounded-xl font-bold flex items-center justify-center gap-2 transition"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>طباعة الفاتورة</span>
                    </button>
                    <button 
                        type="button" 
                        @click="show = false; $wire.closeReceipt()" 
                        class="flex-1 py-2 px-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-bold hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
