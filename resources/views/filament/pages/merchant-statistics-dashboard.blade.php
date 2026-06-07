<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                نظرة عامة على أداء الفرع
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                مبيعات، مخزون، عملاء، ومحاسبة — إحصائيات محدّثة لفرعك التجاري
            </p>
        </div>

        <x-filament-widgets::widgets :widgets="$this->getWidgets()" :columns="$this->getColumns()" />
    </div>
</x-filament-panels::page>
