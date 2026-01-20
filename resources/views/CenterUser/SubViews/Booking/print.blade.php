<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Invoice</title>
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

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align:center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align:center;
            }
        }

        .invoice-box.rtl {
            direction: rtl;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .itemsTable {
            padding-top: 20px;
            padding-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        {!! $template !!}

        <table cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="2">
                    <table class="itemsTable">
                        <tr>
                            <td>Receipt:</td>
                            <td>{{ $booking->id }}</td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ $booking->booking_date }}</td>
                        </tr>
                        <tr>
                            <td>Served By:</td>
                            <td>{{ $booking->created_by_user->name ?? '-' }}</td>
                        </tr>
                        @if (!empty($booking->payment_type))
                            <tr>
                                <td>Payment Method:</td>
                                <td>
                                    {{ get_payment_types($booking->payment_type) }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>Customer:</td>
                            <td>{{ $booking->full_name }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{ $booking->mobile }}</td>
                        </tr>
                        @if (!empty($booking->wallet))
                            <tr>
                                <td>Wallet</td>
                                <td>{{ $booking->wallet->code }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <table>
                        <tr class="heading">
                            @if (!empty($booking->details->first()->service))
                                <td>Service</td>
                            @endif
                            <td>Date and Time</td>
                            @if (!empty($booking->details->first()->price))
                                <td>Price</td>
                            @endif
                            @if (!empty($booking->details->first()->tip))
                                <td>Tips</td>
                            @endif
                        </tr>
                        @php
                            $service_price = 0;
                        @endphp
                        @if (!$booking->details->isEmpty())
                            @foreach ($booking->details as $detail)
                                <tr class="item">
                                    @if (!empty($detail->service) && isset($detail->service->name))
                                        <td>{{ $detail->service->name }}</td>
                                    @endif
                                    <td>{{ $detail->_date . ' ' . $detail->from_time . ' - ' . $detail->to_time }}</td>
                                    @if ($detail->is_free == 1)
                                        <td>Free</td>
                                    @else
                                        @if (!empty($detail->price))
                                            <td>AED {{ number_format($detail->price, 2, '.', '') }}</td>
                                        @endif
                                    @endif
                                    @if (!empty($detail->tip) && isset($detail->tip))
                                        <td>{{ $detail->tip }}</td>
                                    @endif
                                </tr>
                                @php
                                    if ($detail->is_free != 1) {
                                        $service_price += $detail->price;
                                    }
                                @endphp
                            @endforeach
                            @php
                                $discount_amount = 0;
                                $card_amount = 0;
                            @endphp
                            @if (!empty($booking->discount_code))
                                <tr class="heading">
                                    <td colspan="2">Discount Code</td>
                                    <td>Amount</td>
                                </tr>
                                <tr class="details">
                                    <td colspan="2">{{ $booking->discount_code->code }}</td>
                                    <td>
                                        @if ($booking->discount_code->type != 'fixed')
                                            @php
                                                $discount_amount =
                                                    ($service_price * $booking->discount_code->amount) / 100;
                                            @endphp
                                            AED
                                            {{ get_num_format(($service_price * $booking->discount_code->amount) / 100) }}
                                        @else
                                            @php
                                                $discount_amount = $booking->discount_code->amount;
                                            @endphp
                                            AED {{ get_num_format($booking->discount_code->amount) }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if (!empty($booking->card))
                                <tr class="heading">
                                    <td colspan="2">Member Ship Card</td>
                                    <td>Amount</td>
                                </tr>
                                <tr class="details">
                                    <td colspan="2">{{ $booking->card->code }}</td>
                                    <td>
                                        @php
                                            $card_amount = ($service_price * $booking->card->amount) / 100;
                                        @endphp
                                        {{ number_format($booking->card->amount) }}%
                                    </td>
                                </tr>
                            @endif
                            @php
                                $total = $service_price - $discount_amount - $card_amount;
                            @endphp
                            @if ($total > 0)
                                <tr>
                                    <td>VAT:</td>
                                    <td colspan="2">AED {{ number_format($total * 0.05, 2, '.', '') }}</td>
                                </tr>
                                <tr>
                                    <td>Subtotal:</td>
                                    <td colspan="2">AED {{ number_format($total - $total * 0.05, 2, '.', '') }}</td>
                                </tr>
                            @endif
                            <tr class="total">
                                <td>Total:</td>
                                <td colspan="2">AED {{ number_format($total, 2, '.', '') }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
