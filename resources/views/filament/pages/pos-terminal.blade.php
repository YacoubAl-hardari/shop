<x-filament-panels::page>
    <form wire:submit="completeSale" class="space-y-4">
        {{ $this->form }}

        @if (! $this->canCompleteSale())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400">
                {{ $this->getCompleteSaleBlockReason() }}
            </div>
        @endif

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
    </form>
</x-filament-panels::page>
