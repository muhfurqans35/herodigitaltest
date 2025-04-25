<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-size: 10px; font-family: sans-serif; }
        body { margin: 0; padding: 0 5px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
    </style>
</head>
<body>
    <div class="center bold">
        NOTA TRANSAKSI<br>
        Print Online
    </div>

    <div class="line"></div>

    <table>
        <tr>
            <td>No</td><td>: {{ $transaction->id }}</td>
        </tr>
        <tr>
            <td>Tanggal</td><td>: {{ $transaction->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td><td>: {{ $transaction->customer_name ?? '-' }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="bold">Detail Layanan</div>
    <table>
        <tr>
            <td colspan="2">{{ $transaction->items->name }}</td>
            <td style="text-align:right;">{{ number_format($transaction->price_per_unit, 0, ',', '.') }}</td>
        </tr>

        @if (is_array($transaction->fields))
            @foreach ($transaction->fields as $fieldGroup)
                @php
                    $name = $fieldGroup['name'] ?? '';
                    $price = $fieldGroup['price'] ?? 0;
                    $qty = $fieldGroup['quantity'] ?? 1;
                @endphp
                <tr>
                    <td style="padding-left: 10px;">- {{ $name }}</td>
                    <td>x{{ $qty }}</td>
                    <td style="text-align:right;">{{ number_format($price * $qty, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    <div class="line"></div>

    <table>
        <tr class="bold">
            <td>Total</td>
            <td style="text-align:right;">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="center" style="margin-top:10px;">
        Terima Kasih 🙏<br>
        printonline.co.id
    </div>
</body>
</html>
