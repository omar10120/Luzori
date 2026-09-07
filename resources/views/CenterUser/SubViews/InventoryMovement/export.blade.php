<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: cairo, sans-serif; font-size: 10px; }
        h2 { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; }
        th { background: #eeeeee; }
    </style>
</head>
<body>
    <h2>{{ $title }}</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('field.product_name') }}</th>
                <th>{{ __('field.barcode') }}</th>
                <th>{{ __('field.created_at') }}</th>
                <th>{{ __('field.branch') }}</th>
                <th>{{ __('field.movement_type') }}</th>
                <th>{{ __('field.quantity') }}</th>
                <th>{{ __('field.reference') }}</th>
                <th>{{ __('field.notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($movements as $movement)
                <tr>
                    <td>{{ $movement->id }}</td>
                    <td>{{ $movement->product?->name ?? '-' }}</td>
                    <td>{{ $movement->product?->barcode ?? '-' }}</td>
                    <td>{{ optional($movement->created_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->branch?->name ?? '-' }}</td>
                    <td>{{ __('field.movement_' . $movement->movement_type) }}</td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->reference_type && $movement->reference_id ? $movement->reference_type . ' #' . $movement->reference_id : '-' }}</td>
                    <td>{{ $movement->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
