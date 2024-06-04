<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>

    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div style="padding: 16px; #eee;font-size: 16px;line-height: 24px;font-family: 'Inter', sans-serif;color: #555;">
        <table style="font-size: 12px; line-height: 20px; width:100%">
            <thead>
                <tr>
                    <td style="padding: 20px 16px 18px 16px;">
                        <h1 style="font-weight: 700; color: #1A1C21;"> Delivery Details #0{{ $delivery->id }}</h1>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: right !important; padding: 0 16px 18px 16px;">
                        <h3 style="font-weight: 700; color: #1A1C21;"></h3>
                        <h3 style="font-weight: 700; color: #1A1C21;">{{ $delivery->store->name }}</h3>
                        <p style="color: #5E6470;">{{ $delivery->store->address }}</p>
                        <p style="color: #5E6470;">Delivery Tx No: {{ $delivery->tx_no }}</p>
                        <p style="color: #5E6470;">Date: {{ date('F d, Y', strtotime($delivery->created_at)) }}</p>
                        @if ($delivery->purchase?->tx_no)
                            <p style="color: #5E6470;">Purchase Order: {{ $delivery->purchase?->tx_no }}</p>
                        @endif
                        <p style="color: #5E6470;">Status: {{ $delivery->status }}</p>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 16px 18px 16px;">
                        <h3 style="font-weight: 700; color: #1A1C21;">Supplier</h3>
                        <p style="font-weight: 700; color: #1A1C21;">{{ $delivery->supplier->name }}</p>
                        <p style="font-weight: 700; color: #1A1C21;">
                            {{ $delivery->supplier->contact_person }}</p>
                        <p style="color: #5E6470;">{{ $delivery->supplier->address }}</p>
                        <p style="color: #5E6470;">{{ $delivery->supplier->email }}</p>
                        <p style="color: #5E6470;">{{ $delivery->supplier->phone }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 16px 18px 16px;">
                        <h4 style="font-weight: 700; color: #1A1C21;">Delivered items</h4>
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="font-size: 12px; line-height: 20px; width:100%; padding: 0 16px 18px 16px;">
            <thead>
                <tr>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align:left !important; width:20px">#
                    </th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: left !important">Item details
                    </th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: center">Qty
                    </th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: center;">
                        Price</th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: right !important;">
                        Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($delivery_items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="display:flex !important; padding-block: 12px;">
                            <p style="font-weight: 700; color: #1A1C21;">{{ $item['name'] }}</p>
                            <p style="color: #5E6470;"> {{ $item['size'] }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: center">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ $item['qty'] }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: center;">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ $item['price'] }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: right!important;">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ $item['total'] }}</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="padding: 12px 0; border-top:1px solid #D7DAE0;">
                        Notes: {{ $delivery->notes }}
                    </td>
                    <td style="padding: 12px 0; border-top:1px solid #D7DAE0;">

                    </td>
                    <td style="border-top:1px solid #D7DAE0;" colspan="4">
                        <table style="width: 100%;border-spacing: 0;">
                            <tbody>
                                <tr>
                                    <th style="padding-top: 12px; text-align: left !important; color: #1A1C21;">
                                        Items</th>
                                    <td style="padding-top: 12px;text-align: right !important;; color: #1A1C21;">
                                        {{ $delivery->quantity }}</td>
                                </tr>
                                <tr>
                                    <th style="padding-top: 12px;text-align: left !important;; color: #1A1C21;">
                                        Subtotal</th>
                                    <td style="padding-top: 12px;text-align: right !important;; color: #1A1C21;">
                                        {{ $delivery->total }}</td>
                                </tr>
                                <tr>
                                    <th style="padding: 12px 0;text-align: left !important; color: #1A1C21;">
                                        Discounts</th>
                                    <td style="padding: 12px 0;text-align: right !important; color: #1A1C21;">
                                        {{ $delivery->discount }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th
                                        style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: left !important;">
                                        Total Price</th>
                                    <th
                                        style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: right !important;">
                                        {{ $delivery->total }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>

</html>
