<!DOCTYPE html>

<html>
    <head>
        <meta charset="utf-8" />
        <title>Invoice</title>
        <style>
            @page {
                margin: 15px;
            }
            .data-table{
                width: 50%;
            }
            .data-table table td{
                padding-left: 10px;
            }
            .invoice-box {
                max-width: 100%;
                margin: auto;
                font-size: 14px;
                font-weight: bolder;
                line-height: 20px;
                font-family: 'Courier-Bold', sans-serif;
                color: #555;
                padding-bottom: 50px;
            }
            .invoice-box table {
                width: 100%;
                line-height: inherit;
                text-align: left;
            }
            .invoice-box table td {
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
        <h3>@yield('title')</h3>
        <div class="invoice-box">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2" align="center" class="title">
                        <a href="{{ route('admin.cp') }}" class="text-center mb-4">
                        <span class="">
                                @if ($configData['style'] === 'light')
                            @include('_partials.macros', ['height' => 20])
                        @else
                            @include('_partials.macros_light', ['height' => 20])
                        @endif
                        </span>
                        {{-- <span class="app-brand-text demo menu-text fw-bold">{{ $brand }}</span> --}}
                        </a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">EMIRATES CENTER FOR MEN'S CARE</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">02 888 2111, 02 555 7271, 02 874 0999</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">UNAITED ARAB EMIRATS</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">ABUDHABI</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">TAX INVOICE</td>
                </tr>
                <tr>
                    <td colspan="2" align="center">TRN 100255481200003</td>
                </tr>

            </table>
        </div>
        <div class="invoice-box data-table">
               @yield('data')
        </div>
    </body>
</html>

