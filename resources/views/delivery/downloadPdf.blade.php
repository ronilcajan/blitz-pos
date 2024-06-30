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
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            margin: 5;
            padding: 0;
        }

        h4 {
            margin: 0;
        }

        .w-full {
            width: 100%;
        }

        .w-half {
            width: 50%;
        }

        .margin-top {
            margin-top: 1.25rem;
        }

        .footer {
            font-size: 0.875rem;
            padding: 1rem;
            background-color: rgb(241 245 249);
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table.products {
            font-size: 0.875rem;
        }

        table.products tr {
            background-color: rgb(96 165 250);
        }

        table.products th {
            color: #ffffff;
            padding: 0.5rem;
        }

        table tr.items {
            background-color: rgb(241 245 249);
        }

        table tr.items td {
            padding: 0.5rem;
        }

        table tr.itemfooter {
            background-color: #fff;
        }

        table tr.itemfooter td {
            padding: 2px 2px;
        }
    </style>
</head>

<body>

    <body>
        <div
            style="padding: 16px; #eee;font-size: 16px;line-height: 24px;font-family: 'Inter', sans-serif;color: #555; ">
            <table style="font-size: 12px; line-height: 20px; width:100%; background-color: #dfe6fc">
                <thead>
                    <tr>
                        <td style="text-align:left; padding:12px; width:20px">
                            <img width="100" src="{{ public_path('storage/' . $delivery->store->avatar) }}">

                        </td>
                        <td>
                            <h2 style="font-weight: 700; color: #1A1C21;"> {{ $delivery->store->name }}
                            </h2>
                            <p style="color: #5E6470;">Email: {{ $delivery->store->email }}</p>
                            <p style="color: #5E6470;">Contact: {{ $delivery->store->contact }}</p>
                            <p style="color: #5E6470;">Address: {{ $delivery->store->address }}</p>
                        </td>
                        <td style="text-align:right; padding: 35px 16px">
                            <p style="color: #5E6470;">Delivery ID: {{ $delivery->tx_no }}</p>
                            <p style="color: #5E6470;">Date: {{ date('F d, Y', strtotime($delivery->created_at)) }}</p>
                            <p style="color: #5E6470;">Status: {{ $delivery->status }}</p>
                        </td>
                    </tr>
                </thead>
            </table>
            <table style="font-size: 12px; line-height: 20px; width:100%">
                <thead>
                    <tr>
                        <td style="padding: 20px 16px 18px 16px;">
                            <h1 style="font-weight: 700; color: #1A1C21;"> Delivery Details</h1>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0 16px 18px 16px;">
                            <p style="font-weight: 700; color: #1A1C21;">{{ $delivery->supplier->name }}</p>
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ $delivery->supplier->contact_person }}</p>
                            <p style="color: #5E6470;">{{ $delivery->supplier->address }}</p>
                            <p style="color: #5E6470;">{{ $delivery->supplier->email }}</p>
                            <p style="color: #5E6470;">{{ $delivery->supplier->phone }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 10px ;">
                            <h4 style="font-weight: 700; color: #1A1C21;">Purchase items</h4>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="" class="products">
                <thead>
                    <tr>
                        <th
                            style="padding: 8px 5px; border-top:1px solid #D7DAE0; text-align:left !important; width:20px">
                            #
                        </th>
                        <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: left !important">Item
                            Detail
                        </th>
                        <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: center">Qty
                        </th>
                        <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: center;">
                            Price</th>
                        <th style="padding: 8px 5px; border-top:1px solid #D7DAE0; text-align: right !important;">
                            Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($delivery_items as $index => $item)
                        <tr class="items">
                            <td>{{ $index + 1 }}</td>
                            <td style="display:flex !important; padding-block: 12px;">

                                <span style="font-weight: 700; color: #1A1C21;">{{ $item['name'] }}</span>
                                <span style="color: #5E6470;"> {{ $item['size'] }}</span>
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
                    <tr class="itemfooter">
                        <td style="text-align: right" colspan="4">
                            Items:
                        </td>
                        <td style="text-align: right">
                            <p style="text-align: right !important; font-weight:700">
                            <p style=" text-align: right !important; ">
                                {{ Number::format($delivery->quantity, precision: 2) }}
                            </p>

                    </tr>
                    <tr class="itemfooter">
                        <td style="text-align: right" colspan="4">
                            Subtotal:
                        </td>
                        <td style="text-align: right">
                            <p style="text-align: right !important; font-weight:700">
                            <p style=" text-align: right !important; ">
                                {{ $delivery->total }}
                            </p>

                    </tr>
                    <tr class="itemfooter">
                        <td style="text-align: right" colspan="4">
                            Discounts:
                        </td>
                        <td style="text-align: right">
                            <p style="text-align: right !important; font-weight:700">
                            <p style=" text-align: right !important; ">
                                {{ $delivery->discount }}
                            </p>

                    </tr>
                    <tr class="itemfooter" style="font-size:16px;font-weight:700">
                        <td style="text-align: right" colspan="4">
                            TOTAL PRICE:
                        </td>
                        <td style="text-align: right">
                            <p style="text-align: right !important; font-weight:700">
                            <p style=" text-align: right !important; ">
                                {{ $delivery->total }}
                            </p>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="margin-top" style="margin: 0 16px">
            <div>Notes: {{ $delivery->notes }}</div>
        </div>
        <div class="footer margin-top" style="margin: 8px 16px">
            <div>Thank you for doing business with us!</div>
            <div>{{ date('Y') }} &copy; BizRoon Inventory</div>
        </div>
    </body>

</body>

</html>
