<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Right side (Product Catalog & Barcode search) - 7 columns -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Top Search Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
                <div class="flex flex-col sm:flex-row gap-4">

                    <!-- Product Name Search Input -->
                    <div class="relative flex-1">
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
            </div>

            <!-- Product Catalog Grid -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 min-h-[500px]">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-800 pb-3 flex items-center gap-2">
                    <svg class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>كتالوج المنتجات</span>
                </h3>
                
                @php
                    $products = $this->getProducts();
                @endphp

                @if($products->isEmpty())
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-500 space-y-3">
                        <svg class="h-16 w-16 stroke-1 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="text-base font-medium">لم يتم العثور على أي منتجات مطابقة</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 max-h-[600px] overflow-y-auto pr-1 mt-1">
                        @foreach($products as $product)
                            @php
                                $inStock = (float)$product->stock_quantity > 0;
                            @endphp
                            <button 
                                type="button"
                                wire:click="addProduct({{ $product->id }})"
                                @if(!$inStock) disabled @endif
                                class="flex flex-col text-right justify-between p-4 rounded-xl border transition duration-200 relative group overflow-hidden
                                    @if($inStock)
                                        bg-white dark:bg-gray-950 border-gray-200 dark:border-gray-800 hover:border-primary-500 dark:hover:border-primary-500 hover:shadow-md hover:-translate-y-0.1
                                    @else
                                        bg-gray-50 dark:bg-gray-900 border-gray-150 dark:border-gray-800 opacity-60 cursor-not-allowed
                                    @endif"
                            >
                                <!-- Hover Highlight -->
                                <div class="absolute inset-0 bg-primary-50/10 dark:bg-primary-500/5 opacity-0 group-hover:opacity-100 transition duration-200"></div>
                                
                                <div class="relative z-10 space-y-2 w-full">
                                    <!-- Stock Level Badge -->
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
        </div>

        <!-- Left side (Cart Items & Checkout Form) - 5 columns -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Shopping Cart Panel -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
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

                <!-- Scrollable Items Area -->
                <div class="max-h-[350px] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($this->data['items'] ?? [] as $index => $item)
                        <div class="p-4 flex justify-between items-center gap-4 hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition">
                            <!-- Item Description -->
                            <div class="flex-1 min-w-0 text-right">
                                <div class="font-bold text-sm text-gray-900 dark:text-white truncate">
                                    {{ $item['product_name'] }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <span>سعر الوحدة:</span>
                                    <span class="font-semibold">{{ number_format($item['unit_price'], 2) }} ر.س</span>
                                </div>
                            </div>

                            <!-- Quantity Adjust Controls -->
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    wire:click="decrementQuantity({{ $index }})"
                                    class="w-7 h-7 rounded-lg border border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                
                                <input 
                                    type="number" 
                                    wire:model.live="data.items.{{ $index }}.quantity" 
                                    class="w-12 text-center py-0.5 px-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                                    min="0.01"
                                    step="any"
                                />
                                
                                <button 
                                    type="button"
                                    wire:click="incrementQuantity({{ $index }})"
                                    class="w-7 h-7 rounded-lg border border-gray-300 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Row Total & Delete -->
                            <div class="flex items-center gap-3 min-w-[95px] justify-end">
                                <span class="font-bold text-sm text-gray-900 dark:text-white font-mono">
                                    {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }}
                                </span>
                                <button 
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="text-gray-400 hover:text-rose-600 transition"
                                    title="حذف الصنف"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 dark:text-gray-500 space-y-2">
                            <svg class="h-12 w-12 mx-auto stroke-1 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <p class="text-sm font-medium">السلة فارغة. اختر منتجات للبدء</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Settlement & Checkout Panel -->
            <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 space-y-4">
                <form wire:submit="completeSale" class="space-y-4">
                    {{ $this->form }}

                    @if (! $this->canCompleteSale())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400">
                            {{ $this->getCompleteSaleBlockReason() }}
                        </div>
                    @endif

                    <div class="pt-2">
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
    </div>
</x-filament-panels::page>
