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
                text-align: center;
            }
            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
        /** RTL **/
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
                            <td>{{ $result->id }}</td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ date('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>Served By:</td>
                            <td>{{ $result->created_by_user->name ?? '-' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Product</td>
                <td>Price</td>
            </tr>
            @php
                $product_price = 0;
            @endphp
            @if (!$result->details->isEmpty())
                @foreach ($result->details as $detail)
                    @php
                        $product_price += $detail->price;
                    @endphp
                    <tr class="item">
                        <td>
                            @if (!empty($detail->product))
                                {{ $detail->product->name }}
                            @endif
                        </td>
                        <td>{{ get_currency() }} {{ get_num_format($detail->price) }}</td>
                    </tr>
                @endforeach
            @endif
            <tr>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Subtotal: </td>
                <td>{{ get_currency() }} {{ get_num_format($product_price - $product_price * 0.05) }}</td>
            </tr>
            <tr>
                <td>VAT: </td>
                <td>{{ get_currency() }} {{ get_num_format($product_price * 0.05) }}</td>
            </tr>
            <tr class="total">
                <td>Total: </td>
                <td>{{ get_currency() }} {{ get_num_format($product_price) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
