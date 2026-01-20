<!DOCTYPE html>details
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
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
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
                        @php
                            $tipsArray = $result['tips'];
                            $dates = array_keys($tipsArray);
                            $firstDate = reset($dates);
                            $lastDate = end($dates);
                        @endphp
                        <tr>
                            <td>Date:</td>
                            <td>
                                {{ $firstDate }}<br>
                                To<br>
                                {{ $lastDate }}
                            </td>
                        </tr>
                        <tr>
                            <td>Served By:</td>
                            @php
                                $fullName = auth('center_user')->user()->name;
                            @endphp
                            <td>{{ $fullName }}</td>
                        </tr>
                        <tr>
                            <td>Payment Method:</td>
                            <td>
                                {{ 'Tips Visa' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Worker:</td>
                            <td>{{ \App\Models\Worker::where('id', $selected_worker)->value('name') }}</td>
                        </tr>
                        @if (!empty($result))
                            @foreach ($result['tips'] as $date => $payments)
                                @php
                                    $total = 0;
                                @endphp
                                @foreach ($firstusers as $index => $value)
                                    @php
                                        if ($index != 'free') {
                                            $total += $result['tips'][$date][$value->id];
                                        }
                                        $firstusers_last_total[$value->id] += $result['tips'][$date][$value->id];
                                    @endphp
                                @endforeach
                            @endforeach
                        @endif
                    </table>
                </td>
            </tr>
            <tr>
                @php
                    $Total = 0;
                @endphp
                <td colspan="2">
                    <table>
                        <tr class="heading">
                            <td>Date and Time</td>
                            <td>Tips</td>
                        </tr>
                        <tr class="item">
                            <td>
                                {{ $firstDate }}<br>
                                To<br>
                                {{ $lastDate }}
                            </td>
                            @if (!empty($firstusers_last_total) && isset($firstusers_last_total))
                                @foreach ($firstusers_last_total as $total)
                                    @php
                                        $Total += $total;
                                    @endphp
                                    <td colspan="2">AED {{ number_format($total, 2, '.', '') }}</td>
                                @endforeach
                            @endif
                        </tr>
                        <tr class="total">
                            <td>Total:</td>
                            @if (!empty($firstusers_last_total) && isset($firstusers_last_total))
                                @foreach ($firstusers_last_total as $total)
                                    @php
                                        $Total += $total;
                                    @endphp
                                    <td colspan="2">AED {{ number_format($total, 2, '.', '') }}</td>
                                @endforeach
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
