<div id="print-section">
    @foreach($orders as $order)
        <div style="border:1px solid #ccc;padding:15px;margin-bottom:15px;border-radius:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <h3>Order {{ $order->order_number }}</h3>
                    <p style="margin:5px 0;">
                        <strong>Total Items:</strong>
                        {{ $order->orderItems->sum('quantity') }}
                    </p>
                    <p style="margin:5px 0;">
                        <strong>Reserved Places:</strong>
                        {{ $order->reserved_places ?? 'N/A' }}
                    </p>
                </div>
                <div>
                    <img src="{{ $order->qr_png_url }}"
                         alt="QR"
                         style="width:200px; height:auto; border:1px solid #ddd; border-radius:6px; padding:4px;">
                </div>
            </div>

            <table style="width:100%;margin-top:10px;border-collapse:collapse;">
                <thead>
                <tr>
                    <th style="border:1px solid #ddd;padding:5px;">Item</th>
                    <th style="border:1px solid #ddd;padding:5px;">Qty</th>
                    <th style="border:1px solid #ddd;padding:5px;">Price</th>
                </tr>
                </thead>
                <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td style="border:1px solid #ddd;padding:5px;">{{ $item->orderable?->name }}</td>
                        <td style="border:1px solid #ddd;padding:5px;">{{ $item->quantity }}</td>
                        <td style="border:1px solid #ddd;padding:5px;">{{ number_format($item->sub_total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</div>
