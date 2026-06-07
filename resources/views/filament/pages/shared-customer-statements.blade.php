<x-filament-panels::page>
    <div class="space-y-4">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                كشوف الحساب المشتركة معك
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                هذه الكشوف شاركها التجار معك. عند إغلاق المشاركة لن تتمكن من الاطلاع عليها.
            </p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
