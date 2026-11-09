<div style="text-align: center; color: #000; margin-bottom: 12px;">
    @php
        // Browser/HTML print needs a public URL; mPDF prefers a local filesystem path.
        $isPdf = request()->query('format') === 'pdf';
        $logoPath = $isPdf
            ? public_path('assets/img/bg-logo.png')
            : asset('assets/img/bg-logo.png');
        $centerLogo = $center?->getFirstMedia('Center');

        if ($centerLogo) {
            if ($isPdf && file_exists($centerLogo->getPath())) {
                $logoPath = $centerLogo->getPath();
            } elseif (!$isPdf) {
                $logoPath = $centerLogo->getUrl();
            }
        }
    @endphp
    <img src="{{ $logoPath }}" style="width: 80px; height: auto;" alt="logo" />

    @if (isset($center) || isset($invoiceSettings))
        <div style="margin-top: 8px; line-height: 1.55; font-size: 14px; color: #000; font-weight: 600;">
            @if (!empty($center?->name))
                <div style="font-size: 16px; font-weight: 700; color: #000; margin-bottom: 2px;">{{ $center->name }}</div>
            @endif

            @php
                $phones = collect([
                    $invoiceSettings?->phone_number_1,
                    $invoiceSettings?->phone_number_2,
                    $invoiceSettings?->phone_number_3,
                ])->filter()->implode(', ');
            @endphp

            @if ($phones)
                <div style="color: #000; font-weight: 600;">{{ $phones }}</div>
            @endif

            <div style="color: #000; font-weight: 600;">United Arab Emirates</div>

            @if (!empty($invoiceSettings?->emirate))
                <div style="color: #000; font-weight: 600;">{{ $invoiceSettings->emirate }}</div>
            @endif

            <div style="color: #000; font-weight: 700; margin-top: 2px;">Tax Invoice</div>

            @if (!empty($invoiceSettings?->tax_number))
                <div style="color: #000; font-weight: 600;">TRN {{ $invoiceSettings->tax_number }}</div>
            @endif
        </div>
    @endif

    @php
        $invoiceInfoPlain = trim(html_entity_decode(strip_tags((string) ($invoice_info ?? ''))));
        $showInvoiceInfo = $invoiceInfoPlain !== '' && strcasecmp($invoiceInfoPlain, 'text') !== 0;
    @endphp
    @if ($showInvoiceInfo)
        <div style="margin-top: 8px; color: #000; font-weight: 600;">
            {!! $invoice_info !!}
        </div>
    @endif
</div>
