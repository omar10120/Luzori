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
                            <td>Wallet:</td>
                            <td>{{ $user_wallet->id }}</td>
                        </tr>
                        <tr>
                            <td>Date:</td>
                            <td>{{ my_date($user_wallet->created_at) }}</td>
                        </tr>
                        <tr>
                            <td>Customer:</td>
                            <td>{{ $user->first_name . ' ' . $user->last_name }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{ '+971' . $user->phone }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>{{ $user->email }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td colspan="2">Wallet</td>
            </tr>
            <tr>
                <td>VAT: </td>
                <td>{{ get_currency() . get_num_format($user_wallet->invoiced_amount * 0.05) }}</td>
            </tr>
            <tr>
                <td>Subtotal: </td>
                <td>{{ get_currency() . get_num_format($user_wallet->invoiced_amount - $user_wallet->invoiced_amount * 0.05) }}
                </td>
            </tr>
            <tr class="details">
                <td>Total:</td>
                <td>{{ get_currency() . get_num_format($user_wallet->invoiced_amount) }}</td>
            </tr>
        </table>
    </div>
</body>

</html>
