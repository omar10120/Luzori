<div style="text-align: center;">
    @php
        $logoPath = public_path('assets/img/bg-logo.png');
        $centerLogo = $center?->getFirstMedia('Center');

        if ($centerLogo && file_exists($centerLogo->getPath())) {
            $logoPath = $centerLogo->getPath();
        }
    @endphp
    <img src="{{ $logoPath }}" style="width: 80px; height: auto;" alt="logo" />

    @if (isset($center) || isset($invoiceSettings))
        <div style="margin-top: 8px; line-height: 1.5; font-size: 13px;">
            @if (!empty($center?->name))
                <div>{{ $center->name }}</div>
            @endif

            @php
                $phones = collect([
                    $invoiceSettings?->phone_number_1,
                    $invoiceSettings?->phone_number_2,
                    $invoiceSettings?->phone_number_3,
                ])->filter()->implode(', ');
            @endphp

            @if ($phones)
                <div>{{ $phones }}</div>
            @endif

            <div>United Arab Emirates</div>

            @if (!empty($invoiceSettings?->emirate))
                <div>{{ $invoiceSettings->emirate }}</div>
            @endif

            <div>Tax Invoice</div>

            @if (!empty($invoiceSettings?->tax_number))
                <div>TRN {{ $invoiceSettings->tax_number }}</div>
            @endif
        </div>
    @endif

    @if (!empty($invoice_info))
        <div style="margin-top: 8px;">
            {!! $invoice_info !!}
        </div>
    @endif
</div>
