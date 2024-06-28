<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <style>
        *,
        body {
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        @page {
            width: 227pt;
            height: auto;
            margin: auto;
        }

        @media print {

            .hidden-print,
            .hidden-print * {
                display: none !important;
            }

            @page {
                width: 227pt;
                /* Fixed width in points */
                height: auto;
                margin: auto;
            }


            body {
                width: 227pt;
                height: auto;
                margin: auto;
                /* Ensure height adjusts to content */
            }
        }
    </style>
</head>

<body>
    <div style="padding: 16px; #eee;font-size: 16px;line-height: 24px;font-family: 'Inter', sans-serif;color: #555; ">
        <table style="font-size: 12px; line-height: 15px; width:100%; margin:0">
            <thead>
                <tr>
                    <td style="text-align:left; padding: 5px 16px; text-align:center">
                        <img width="120" src="{{ public_path('storage/' . $sale->store->avatar) }}">
                        <h1 style="font-size: 20px; font-weight: 700; color: #1A1C21; margin: 10px 0">
                            {{ $sale->store->name }}
                        </h1>
                        <p style="color: #5E6470; margin:0">
                            Email: {{ $sale->store->email }}
                        </p>
                        <p style="color: #5E6470; margin:0">
                            Contact: {{ $sale->store->contact }}
                        </p>
                        <p style="color: #5E6470; margin:0">
                            Address: {{ $sale->store->address }}
                        </p>
                    </td>
                </tr>
            </thead>
        </table>
        <hr>
        <table style="font-size: 12px; line-height: 15px; width:100%;">
            <thead>
                <tr>
                    <td style="padding: 5px 16px; text-align:center">
                        <h2 style="font-weight: 700; color: #1A1C21; margin:0"> SALES ACKNOWLEDGEMENT</h2>
                        <p style="color: #5E6470; margin:0">
                            Invoice ID: {{ $sale->tx_no }}
                        </p>
                        <p style="color: #5E6470; margin:0">
                            Date: {{ date('F d, Y h:i A', strtotime($sale->created_at)) }}
                        </p>
                        <p style="color: #5E6470; margin:0">
                            Status: {{ $sale->status->getLabelText() }}
                        </p>
                        <p style="color: #5E6470; margin:0">
                            Cashier: {{ $sale->user->name }}
                        </p>
                    </td>
                </tr>
            </thead>
        </table>
        <hr>
        <p class="mt-3" style="font-weight:bold"> Purchase item/s: {{ number_format($sale->quantity) }}</p>

        <table style="width:100%; line-height: 15px; margin-top:10px" class="products">
            <thead>
                <tr>
                    <th style="padding: 2px 0; border-top:1px solid #D7DAE0; text-align:left !important;">
                        DESCRIPTION
                    </th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: right !important">
                        AMOUNT
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td style="padding: 2px 0; border-bottom:1px dashed #D7DAE0">
                            <p style="margin: 0">{{ $item->product->name }} {{ $item->quantity }} x
                                {{ Number::format($item->price, precision: 2) }}</p>
                        </td>
                        <td style="padding: 2px 0; text-align: right; border-bottom:1px dashed #D7DAE0">
                            <p style="margin: 0">
                                {{ $sale->store->currency . ' ' . Number::format($item->price * $item->quantity, precision: 2) }}
                            </p>

                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td style="padding: 2px 0; ">
                        <p style="margin-top:5px">Subtotal</p>
                    </td>
                    <td style="padding: 2px 0; text-align: right;">
                        <p style="margin-top:5px">{{ $sale->sub_total }}</p>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; margin-top:20px">
                        <p style="margin: 0">Discount</p>
                    </td>
                    <td style="padding: 2px 0; text-align: right;">
                        <p style="margin: 0">{{ $sale->discount }}</p>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; margin-top:20px; border-bottom:1px dashed #D7DAE0">
                        <p style="font-size: 15px; margin: 5px 0; font-weight:bold">GRAND TOTAL</p>
                    </td>
                    <td style="padding: 2px 0; text-align: right; border-bottom:1px dashed #D7DAE0">
                        <p style="font-size: 15px; margin: 5px 0; font-weight:bold">{{ $sale->total }}</p>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 2px 0; margin-top:20px">
                        <p style="margin: 0">Amount Tendered</p>
                    </td>
                    <td style="padding: 2px 0; text-align: right;">
                        <p style="margin: 0">
                            {{ $sale->store->currency . ' ' . Number::format($sale->payment_tender, precision: 2) }}
                        </p>

                    </td>
                </tr>
                <tr style="font-weight:bold">
                    <td style="padding: 2px 0; margin-top:20px;">
                        <p style="margin: 0; font-size:14px">CHANGED</p>
                    </td>
                    <td style="padding: 2px 0; text-align: right;">
                        <p style="margin: 0;font-size:14px">
                            {{ $sale->store->currency . ' ' . Number::format($sale->payment_changed, precision: 2) }}
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div style="margin-bottom: 10px">
            <div>Payment method: {{ $sale->payment_method }}</div>
        </div>
        <hr>
        <table style="width:100%; line-height: 12px; margin-top:10px" class="products">
            <tbody>
                <tr style="font-weight:bold">
                    <td style="padding: 2px 0; margin-top:20px;">
                        <p style="margin: 0; font-size:14px">CUSTOMER: {{ $sale->customer->name ?? 'Walk-in' }}</p>
                    </td>
                </tr>
                @if ($sale->customer?->name)
                    <tr>
                        <td style="padding: 0;">
                            <p style="margin: 0">Address: {{ $sale->customer?->address }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 0;">
                            <p style="margin: 0">Contact: {{ $sale->customer?->phone }}</p>
                        </td>
                    </tr>
                @endif

            </tbody>
        </table>
    </div>

    <div style="margin: 0 60px 5px 16px;">
        <div style="text-align: center;margin-left: 22mm">
            @php
                echo DNS2D::getBarcodeHTML($sale->tx_no, 'QRCODE', 5, 5);
            @endphp
        </div>

    </div>

    <div class="footer margin-top" style="margin: 8px 16px; text-align: center">
        <div style="margin: 2px 0">Thank you for doing business with us and come again!</div>
        <div>{{ date('Y') }} &copy; BizRoon Inventory</div>
    </div>
</body>

</html>
