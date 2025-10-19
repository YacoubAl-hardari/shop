<div
    class="p-4 rounded-lg {{ $type === 'danger' ? 'bg-red-50 dark:bg-red-900/20' : ($type === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-green-50 dark:bg-green-900/20') }}">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            @if ($type === 'danger')
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            @elseif($type === 'warning')
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            @else
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            @endif
        </div>
        <div class="flex-1">
            <p
                class="text-sm font-medium {{ $type === 'danger' ? 'text-red-800 dark:text-red-200' : ($type === 'warning' ? 'text-yellow-800 dark:text-yellow-200' : 'text-green-800 dark:text-green-200') }}">
                {{ $message }}
            </p>
            @if (isset($debtRatio))
                <div
                    class="mt-2 text-xs {{ $type === 'danger' ? 'text-red-700 dark:text-red-300' : ($type === 'warning' ? 'text-yellow-700 dark:text-yellow-300' : 'text-green-700 dark:text-green-300') }}">
                    <div class="flex justify-between">
                        <span>الديون الحالية: <strong>{{ $currentDebt }} ريال</strong></span>
                        <span>الراتب: <strong>{{ $salary }} ريال</strong></span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
