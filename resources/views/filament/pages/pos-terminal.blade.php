<x-filament-panels::page>
    <form wire:submit="completeSale" class="space-y-4">
        {{ $this->form }}

        @if ($this->canCompleteSale())
            <div class="sticky bottom-4 z-10 flex justify-end xl:hidden">
                <x-filament::button type="submit" size="xl" class="w-full shadow-lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                    <span>إتمام البيع</span>
                </x-filament::button>
            </div>

            <div class="hidden justify-end xl:flex">
                <x-filament::button type="submit" size="xl">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                    <span>إتمام البيع</span>
                </x-filament::button>
            </div>
        @else
            <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">
                {{ $this->getCompleteSaleBlockReason() }}
            </div>
        @endif
    </form>
</x-filament-panels::page>
