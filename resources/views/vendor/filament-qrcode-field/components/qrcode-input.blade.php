@php
    use function Filament\Support\prepare_inherited_attributes;
    $fieldWrapperView = $getFieldWrapperView();
    $datalistOptions = $getDatalistOptions();
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $extraAttributeBag = $getExtraAttributeBag();
    $hasInlineLabel = $hasInlineLabel();
    $id = $getId();
    $isConcealed = $isConcealed();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $placeholder = $getPlaceholder();

    $inputAttributes = $getExtraInputAttributeBag()
        ->merge($extraAlpineAttributes, escape: false)
        ->merge(
            [
                'autofocus' => $isAutofocused(),
                'disabled' => $isDisabled,
                'id' => $id,
                'inputmode' => $getInputMode(),
                'list' => $datalistOptions ? $id . '-list' : null,
                'max' => !$isConcealed ? $getMaxValue() : null,
                'maxlength' => !$isConcealed ? $getMaxLength() : null,
                'min' => !$isConcealed ? $getMinValue() : null,
                'minlength' => !$isConcealed ? $getMinLength() : null,
                'placeholder' => filled($placeholder) ? e($placeholder) : null,
                'readonly' => $isReadOnly(),
                'required' => $isRequired() && !$isConcealed,
                'type' => 'text',
                $applyStateBindingModifiers('wire:model') => $statePath,
            ],
            escape: false,
        )
        ->class(['w-full pr-10']);
@endphp
<x-dynamic-component :component="$fieldWrapperView" :field="$field" :has-inline-label="$hasInlineLabel" class="fi-fo-text-input-wrp">
    <div xmlns:x-filament="http://www.w3.org/1999/html" x-load-js="['https://unpkg.com/html5-qrcode']"
        x-data="{
            html5QrcodeScanner: null,
            stopScanning() {
                if (!this.html5QrcodeScanner) {
                    return;
                }
                this.html5QrcodeScanner.pause();
                this.html5QrcodeScanner.clear();
                this.html5QrcodeScanner = null;
            },
            openScannerModal() {
                $dispatch('open-modal', { id: 'qrcode-scanner-modal-{{ $getName() }}' });
                this.startCamera();
            },
            closeScannerModal() {
                $dispatch('close-modal', { id: 'qrcode-scanner-modal-{{ $getName() }}' });
                this.stopScanning();
            },
            onScanSuccess(decodedText, decodedResult) {
                $wire.set('{{ $getStatePath() }}', decodedText);
                $dispatch('close-modal', { id: 'qrcode-scanner-modal-{{ $getName() }}' });
                this.stopScanning();
            },
            startCamera() {
                this.html5QrcodeScanner = new Html5QrcodeScanner('reader-{{ $getName() }}', { fps: 10, qrbox: { width: 250, height: 250 } }, false);
                this.html5QrcodeScanner.render(this.onScanSuccess);
            }
        }">
        <div class="grid gap-y-2">
            <x-filament::input.wrapper :disabled="$isDisabled" :valid="!$errors->has($statePath)" :attributes="prepare_inherited_attributes($extraAttributeBag)->class(['fi-fo-text-input'])">
                <input {{ $inputAttributes->class(['fi-input']) }} />

                <x-slot name="suffix">
                    <!-- Trigger Button for Filament Modal -->
                    <button type="button" @click="openScannerModal()" class="flex items-center focus:outline-hidden"
                        aria-label="Scan QrCode">
                        @php
                            $customIcon = $getExtraAttributes()['icon'] ?? null;
                            $iconAttributeBag = null;

                            if ($customIcon) {
                                $iconAttributeBag = new \Illuminate\View\ComponentAttributeBag();
                                $iconAttributeBag = $iconAttributeBag->class(['fi-input-wrp-icon', 'w-5', 'h-5']);
                            }
                        @endphp

                        @if ($customIcon)
                            {{ \Filament\Support\generate_icon_html($customIcon, null, $iconAttributeBag) }}
                        @else
                            <svg class="fi-input-wrp-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" version="1.1"
                                viewBox="0 0 16 16" fill="currentColor">
                                <path fill="currentColor" d="M6 0h-6v6h6v-6zM5 5h-4v-4h4v4z"></path>
                                <path fill="currentColor" d="M2 2h2v2h-2v-2z"></path>
                                <path fill="currentColor" d="M0 16h6v-6h-6v6zM1 11h4v4h-4v-4z"></path>
                                <path fill="currentColor" d="M2 12h2v2h-2v-2z"></path>
                                <path fill="currentColor" d="M10 0v6h6v-6h-6zM15 5h-4v-4h4v4z"></path>
                                <path fill="currentColor" d="M12 2h2v2h-2v-2z"></path>
                                <path fill="currentColor" d="M2 7h-2v2h3v-1h-1z"></path>
                                <path fill="currentColor" d="M7 9h2v2h-2v-2z"></path>
                                <path fill="currentColor" d="M3 7h2v1h-2v-1z"></path>
                                <path fill="currentColor" d="M9 12h-2v1h1v1h1v-1z"></path>
                                <path fill="currentColor" d="M6 7v1h-1v1h2v-2z"></path>
                                <path fill="currentColor" d="M8 4h1v2h-1v-2z"></path>
                                <path fill="currentColor" d="M9 8v1h2v-2h-3v1z"></path>
                                <path fill="currentColor" d="M7 6h1v1h-1v-1z"></path>
                                <path fill="currentColor" d="M9 14h2v2h-2v-2z"></path>
                                <path fill="currentColor" d="M7 14h1v2h-1v-2z"></path>
                                <path fill="currentColor" d="M9 11h1v1h-1v-1z"></path>
                                <path fill="currentColor" d="M9 3v-2h-1v-1h-1v4h1v-1z"></path>
                                <path fill="currentColor" d="M12 14h1v2h-1v-2z"></path>
                                <path fill="currentColor" d="M12 12h2v1h-2v-1z"></path>
                                <path fill="currentColor" d="M11 13h1v1h-1v-1z"></path>
                                <path fill="currentColor" d="M10 12h1v1h-1v-1z"></path>
                                <path fill="currentColor" d="M14 10v1h1v1h1v-2h-1z"></path>
                                <path fill="currentColor" d="M15 13h-1v3h2v-2h-1z"></path>
                                <path fill="currentColor" d="M10 10v1h3v-2h-2v1z"></path>
                                <path fill="currentColor" d="M12 7v1h2v1h2v-2h-2z"></path>
                            </svg>
                        @endif
                    </button>
                </x-slot>
            </x-filament::input.wrapper>

        </div>

        <!-- Filament Modal for QrCode Scanner -->
        <x-filament::modal id="qrcode-scanner-modal-{{ $getName() }}" width="lg" :close-by-clicking-away="false">
            <x-slot name="header">
                <h2 class="text-lg font-semibold">
                    Scan {{ $getLabel() ?? 'QrCode' }}
                </h2>
            </x-slot>

            <div class="p-4">
                <div id="scanner-container">
                    <div id="reader-{{ $getName() }}" width="600px" height="600px"></div>
                </div>
            </div>

            <x-slot name="footer">
                <x-filament::button @click="closeScannerModal()" color="danger">
                    Close
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>
</x-dynamic-component>
