<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Sale Receipt</title>
    <style>
        @page {
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: auto;
            background: #fff;
            color: #000;
            text-align: center;
        }

        .invoice-box {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 0;
            display: inline-block;
            text-align: left;
            font-size: 14px;
            font-weight: 700;
            line-height: 20px;
            font-family: 'Cairo', DejaVu Sans, sans-serif;
            color: #000;
        }

        @media print {
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                text-align: center !important;
            }
            .invoice-box {
                margin: 0 auto !important;
                padding: 0;
            }
            .no-print { display: none !important; }
        }

        .invoice-box table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 2px;
            vertical-align: top;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        {!! $template !!}

        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td>Sale ID:</td>
                            <td>#{{ $sale->id }}</td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ is_string($sale->created_at) ? substr($sale->created_at, 0, 16) : ($sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-') }}</td>
                        </tr>
                        <tr>
                            <td>Served By:</td>
                            <td>{{ $sale->created_by_user->name ?? '-' }}</td>
                        </tr>
                        @if ($sale->worker)
                            <tr>
                                <td>Worker:</td>
                                <td>{{ $sale->worker->name }}</td>
                            </tr>
                        @endif
                        @if ($sale->client)
                            <tr>
                                <td>Client:</td>
                                <td>{{ $sale->client->name }}</td>
                            </tr>
                        @else
                            <tr>
                                <td>Client:</td>
                                <td>Walk-in</td>
                            </tr>
                        @endif
                        <tr>
                            <td>Payment Method:</td>
                            <td>{{ $paymentMethod ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table>
                        <tr class="heading">
                            <td>Item</td>
                            <td>Details</td>
                            <td>Qty</td>
                            <td>Price</td>
                        </tr>
                        @foreach ($sale->saleItems as $saleItem)
                            <tr class="item">
                                <td>
                                    {{ $saleItem->item_type === 'booking' ? 'Service' : 'Product' }}
                                </td>
                                <td>
                                    @if($saleItem->item_type === 'booking')
                                        @php
                                            $booking = $saleItem->itemable;
                                            $service = $booking->details->first()->service ?? null;
                                        @endphp
                                        {{ $service?->name ?? '-' }}
                                        @if($booking->details->first())
                                            <br>{{ $booking->details->first()->_date }} {{ $booking->details->first()->from_time }}-{{ $booking->details->first()->to_time }}
                                        @endif
                                    @elseif($saleItem->item_type === 'buy_product')
                                        @php
                                            $buyProduct = $saleItem->itemable;
                                            $products = $buyProduct->details->map(function($detail) {
                                                return $detail->product?->name;
                                            })->filter()->implode(', ');
                                        @endphp
                                        {{ $products ?: '-' }}
                                    @endif
                                </td>
                                <td>{{ $saleItem->quantity }}</td>
                                <td>{{ number_format($saleItem->price, 2) }} {{ get_currency() }}</td>
                            </tr>
                        @endforeach
                        @php
                            $vatSplit = $sale->vatBreakdown();
                        @endphp
                        <tr>
                            <td colspan="3">Subtotal:</td>
                            <td>{{ number_format($vatSplit['subtotal'], 2) }} {{ get_currency() }}</td>
                        </tr>
                        <tr>
                            <td colspan="3">VAT (5%):</td>
                            <td>{{ number_format($vatSplit['vat'], 2) }} {{ get_currency() }}</td>
                        </tr>
                        @if($sale->tip > 0)
                            <tr>
                                <td colspan="3">Tip ({{ $sale->worker?->name }}):</td>
                                <td>{{ number_format($sale->tip, 2) }} {{ get_currency() }}</td>
                            </tr>
                        @endif
                        <tr class="total">
                            <td colspan="3">Total:</td>
                            <td>{{ number_format($sale->total, 2) }} {{ get_currency() }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @if (!empty($autoPrint))
    <script>
        (function () {
            var printed = false;
            function closeSoon() {
                setTimeout(function () {
                    try { window.close(); } catch (e) {}
                }, 400);
            }
            function doPrint() {
                if (printed) return;
                printed = true;
                try {
                    window.focus();
                    window.print();
                } catch (e) {}
            }
            window.addEventListener('afterprint', closeSoon);
            setTimeout(closeSoon, 4000);
            if (document.readyState === 'complete') {
                setTimeout(doPrint, 200);
            } else {
                window.addEventListener('load', function () { setTimeout(doPrint, 200); });
            }
        })();
    </script>
    @endif
</body>

</html>

