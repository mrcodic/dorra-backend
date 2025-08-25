{{-- resources/views/pdf/invoice.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $model->invoice_number }}</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf.css') }}">
</head>
<body>
<h2>Invoice #{{ $model->invoice_number }}</h2>
<p><strong>Date:</strong> {{ $model->issued_date }}</p>
<p><strong>Customer:</strong> {{ $model->order->user?->first_name ?? $model->order?->guest?->first_name }}</p>

<table width="100%" border="1" cellspacing="0" cellpadding="6">
    <thead>
    <tr>
        <th>Item</th>
        <th width="15%">Qty</th>
        <th width="20%">Price</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($model->order?->orderItems ?? [] as $item)
        <tr>
            <td>{{ $item->product->name ?? 'N/A' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>${{ number_format($item->sub_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3>Total: ${{ $model->total_price }}</h3>
</body>
</html>
