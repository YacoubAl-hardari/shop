<x-filament-panels::page>
    @if (($share = $this->getStatementShare()) && ($customer = $this->getSharedCustomer()))
        @include('filament.pages.partials.shared-customer-statement-detail', [
            'share' => $share,
            'customer' => $customer,
            'lines' => $this->getStatementLines(),
        ])
    @endif
</x-filament-panels::page>
