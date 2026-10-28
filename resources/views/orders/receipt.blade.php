<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $order->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef0f6; font-family: "Courier New", monospace; color: #000; }
        .receipt { width: 302px; margin: 20px auto; background: #fff; padding: 16px 14px; font-size: 12px; line-height: 1.45; }
        .center { text-align: center; }
        .brand { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        hr { border: 0; border-top: 1px dashed #000; margin: 8px 0; }
        .total { font-size: 14px; font-weight: bold; }
        .actions { text-align: center; margin: 16px; }
        .actions button, .actions a { font: inherit; padding: 8px 16px; margin: 0 4px; border: 1px solid #555; background: #fff; border-radius: 6px; cursor: pointer; text-decoration: none; color: #000; }
        @media print {
            body { background: #fff; }
            .receipt { margin: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="brand">æ-WASH</div>
            <div>Automated Efficient Washing System</div>
            <div>"I Wish You Wash Here"</div>
        </div>
        <hr>
        <div class="row"><span>No. Invoice</span><span>{{ $order->invoice_number }}</span></div>
        <div class="row"><span>Tanggal</span><span>{{ $order->order_date->format('d/m/Y') }}</span></div>
        <div class="row"><span>Customer</span><span>{{ $order->customer->name }}</span></div>
        <div class="row"><span>Estimasi</span><span>{{ $order->estimated_finish_date?->format('d/m/Y') ?? '-' }}</span></div>
        <hr>
        @foreach ($order->items as $item)
            <div>{{ $item->service->name }} - {{ $item->fabric_type }}</div>
            <div class="row">
                <span>{{ rtrim(rtrim(number_format($item->weight_kg, 2, ',', '.'), '0'), ',') }} kg x {{ number_format($item->price_per_kg, 0, ',', '.') }}</span>
                <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach
        <hr>
        <div class="row"><span>Total berat</span><span>{{ rtrim(rtrim(number_format($order->total_weight, 2, ',', '.'), '0'), ',') }} kg</span></div>
        <div class="row total"><span>TOTAL</span><span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span></div>
        <div class="row"><span>Pembayaran</span><span>{{ $order->isPaid() ? 'LUNAS' : 'BELUM BAYAR' }}</span></div>
        <hr>
        <div class="center">
            Status: {{ $order->status_label }}<br>
            Terima kasih telah mencuci di æ-WASH
        </div>
    </div>

    <div class="actions no-print">
        <button onclick="window.print()">Cetak</button>
        <a href="{{ route('orders.show', $order) }}">Kembali</a>
    </div>
</body>
</html>
