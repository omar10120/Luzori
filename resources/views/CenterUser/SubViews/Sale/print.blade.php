<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Sale Receipt</title>
    <style>
        @page {
            margin: 15px;
        }

        .invoice-box {
            max-width: 100%;
            margin: auto;
            font-size: 14px;
            font-weight: bolder;
            line-height: 20px;
            font-family: 'Courier-Bold', sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
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
                        @if ($sale->payment_type)
                            <tr>
                                <td>Payment Method:</td>
                                <td>{{ $sale->payment_type }}</td>
                            </tr>
                        @endif
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
                        <tr>
                            <td colspan="3">Subtotal:</td>
                            <td>{{ number_format($sale->subtotal, 2) }} {{ get_currency() }}</td>
                        </tr>
                        @if($sale->tax > 0)
                            <tr>
                                <td colspan="3">Tax:</td>
                                <td>{{ number_format($sale->tax, 2) }} {{ get_currency() }}</td>
                            </tr>
                        @endif
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
</body>

</html>

