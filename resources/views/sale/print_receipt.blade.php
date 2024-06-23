<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .receipt {
            width: 80mm;
            margin: auto;
            padding: 10px;
            border: 1px solid #000;
        }

        .header,
        .item-list,
        .total,
        .footer {
            margin-bottom: 10px;
        }

        .header,
        .footer {
            text-align: center;
        }

        .item-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-list th,
        .item-list td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .total {
            text-align: right;
        }

        .print-btn {
            display: none;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        <div class="header">
            <h2>Receipt</h2>
        </div>
        <div class="item-list">
            <table>
                @foreach ($items as $item)
                    <tr>
                        <th>{{ $item->product->name }}</th>
                        <th>{{ $item->quantity }}</th>
                        <th>{{ $item->price }}</th>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="total">
            <strong>Total:</strong> $45.00
        </div>
        <div class="footer">
            Thank you for your purchase!
        </div>
    </div>
    <script>
        window.print()
    </script>
</body>

</html>
