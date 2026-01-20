<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ __('locale.sales_report') }}</title>

    <style>
        * {
            padding: 0;
            margin: 0;
        }

        body {
            padding: 5px;
            margin: 0;
            font-family: sans-serif;
        }

        table {
            border-collapse: collapse;
            margin-right: auto;
            margin-left: auto;
        }

        table td,
        table th {
            border: 1px solid #000000;
            font-size: 13px !important;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center">{{ __('locale.sales_report') }}</h3>
    {!! $template !!}
</body>

</html>
