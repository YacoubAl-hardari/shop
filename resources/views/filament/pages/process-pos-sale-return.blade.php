<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="" class="space-y-4">
            {{ $this->form }}
        </form>

        @if (!$this->selectedSale)
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-6 py-8 text-center dark:border-gray-600 dark:bg-gray-800">
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="mx-auto h-10 w-10 text-gray-400" />
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">ابدأ بإدخال رقم الفاتورة للبحث عن عملية الشراء</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
