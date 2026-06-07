<div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex flex-wrap items-end gap-4">
        <div class="min-w-[10rem] flex-1">
            <label for="statementDateFrom" class="mb-1 block text-sm text-gray-500">من تاريخ</label>
            <input
                id="statementDateFrom"
                type="date"
                wire:model.live="statementDateFrom"
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10"
            />
        </div>

        <div class="min-w-[10rem] flex-1">
            <label for="statementDateTo" class="mb-1 block text-sm text-gray-500">إلى تاريخ</label>
            <input
                id="statementDateTo"
                type="date"
                wire:model.live="statementDateTo"
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10"
            />
        </div>

        <div class="min-w-[10rem] flex-1">
            <label for="statementSortDirection" class="mb-1 block text-sm text-gray-500">ترتيب حسب التاريخ</label>
            <select
                id="statementSortDirection"
                wire:model.live="statementSortDirection"
                class="fi-input block w-full rounded-lg border-none bg-white px-3 py-2 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10"
            >
                <option value="desc">الأحدث أولاً</option>
                <option value="asc">الأقدم أولاً</option>
            </select>
        </div>

        <div class="flex gap-2">
            <x-filament::button
                wire:click="resetStatementFilters"
                color="gray"
                icon="heroicon-o-x-mark"
            >
                إعادة ضبط
            </x-filament::button>
        </div>
    </div>
</div>
