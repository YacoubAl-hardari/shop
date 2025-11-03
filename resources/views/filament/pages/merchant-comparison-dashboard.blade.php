<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                مقارنة شاملة بين التجار حسب المنتجات
            </h2>
            <p class="text-gray-600 dark:text-gray-300">
                هذه اللوحة تعرض مقارنة مفصلة بين التجار المختلفين بناءً على المنتجات والأسعار والمبيعات
            </p>
        </div>

        <x-filament-widgets::widgets :widgets="$this->getWidgets()" :columns="$this->getColumns()" />
    </div>
</x-filament-panels::page>
