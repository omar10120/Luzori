<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ __('locale.expense_report') }}</title>

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
            width: 100%;
        }

        table td,
        table th {
            border: 1px solid #000000;
            font-size: 10px !important;
            padding: 5px;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center">{{ __('locale.expense_report') }}</h3>
    {!! $template !!}
</body>

</html>
