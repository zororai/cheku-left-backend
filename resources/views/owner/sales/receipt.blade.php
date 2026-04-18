<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $sale->sale_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 300px;
            margin: 0 auto;
            padding: 8px;
            color: #000;
        }
        h2 { text-align: center; margin: 4px 0; font-size: 15px; }
        p  { margin: 2px 0; }
        hr { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 2px 0; vertical-align: top; }
        .right  { text-align: right; }
        .center { text-align: center; }
        .bold   { font-weight: bold; }
        .small  { font-size: 10px; }
        .total-row td { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 4px; }
        @media print {
            .no-print { display: none; }
            body { width: 100%; }
        }
    </style>
</head>
<body>

    <h2>{{ $sale->butcherShop->name ?? 'Chekuleft' }}</h2>
    <p class="center small">Receipt</p>

    <hr>

    <p>No: <strong>{{ $sale->sale_number }}</strong></p>
    <p>Date: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</p>
    <p>Cashier: {{ $sale->user->name ?? 'N/A' }}</p>
    <p>Payment: {{ ucfirst($sale->payment_method) }}</p>

    <hr>

    <table>
        <thead>
            <tr>
                <td class="bold">Product</td>
                <td class="right bold">Kg</td>
                <td class="right bold">/kg</td>
                <td class="right bold">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product_name ?? $item->product?->name }}</td>
                <td class="right">{{ number_format($item->weight_grams / 1000, 3) }}</td>
                <td class="right">{{ number_format($item->price_per_kg, 2) }}</td>
                <td class="right">{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="right">TOTAL</td>
                <td class="right">{{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <hr>

    <p class="center small">Thank you for your purchase!</p>

    <br class="no-print">
    <div class="no-print center" style="margin-top: 12px;">
        <button onclick="window.print()" style="padding: 8px 20px; font-size: 14px; cursor: pointer;">
            Print
        </button>
        &nbsp;
        <a href="{{ route('owner.sales.pdf', $sale) }}"
           style="padding: 8px 20px; font-size: 14px; text-decoration: none; border: 1px solid #000;">
            Download PDF
        </a>
    </div>

</body>
</html>
