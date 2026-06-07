<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-success-100 dark:bg-success-900/30">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-5 w-5 text-success-600 dark:text-success-400" />
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        كشوف حساب مشتركة معك
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        شارك معك {{ $this->getShares()->count() }} تاجر كشف حسابك. اضغط للاطلاع.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($this->getShares() as $share)
                    <x-filament::button
                        tag="a"
                        :href="$this->viewUrl($share->uuid)"
                        color="success"
                        size="sm"
                        icon="heroicon-o-eye"
                    >
                        {{ $share->team?->name }}
                    </x-filament::button>
                @endforeach

                <x-filament::button
                    tag="a"
                    :href="$this->listUrl()"
                    color="gray"
                    size="sm"
                    outlined
                >
                    عرض الكل
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
