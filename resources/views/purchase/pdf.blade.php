<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Document</title>

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
                        <h1 style="font-weight: 700; color: #1A1C21;"> Purchase Order #0{{ $purchase->id }}</h1>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: right !important; padding: 0 16px 18px 16px;">
                        <h3 style="font-weight: 700; color: #1A1C21;"></h3>
                        <h3 style="font-weight: 700; color: #1A1C21;">{{ $purchase->store->name }}</h3>
                        <p style="color: #5E6470;">{{ $purchase->store->address }}</p>
                        <p style="color: #5E6470;">Date: {{ date('F d, Y', strtotime($purchase->created_at)) }}</p>
                        <p style="color: #5E6470;">Status: {{ $purchase->status }}</p>

                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 16px 18px 16px;">
                        <h3 style="font-weight: 700; color: #1A1C21;">Supplier</h3>
                        <p style="font-weight: 700; color: #1A1C21;">{{ $purchase->supplier->name }}</p>
                        <p style="font-weight: 700; color: #1A1C21;">
                            {{ $purchase->supplier->contact_person }}</p>
                        <p style="color: #5E6470;">{{ $purchase->supplier->address }}</p>
                        <p style="color: #5E6470;">{{ $purchase->supplier->email }}</p>
                        <p style="color: #5E6470;">{{ $purchase->supplier->phone }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0 16px 18px 16px;">
                        <h4 style="font-weight: 700; color: #1A1C21;">Purchase items</h4>
                    </td>
                </tr>
            </tbody>
        </table>
        <table style="font-size: 12px; line-height: 20px; width:100%; padding: 0 16px 18px 16px;">
            <thead>
                <tr>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align:left !important; width:20px">#
                    </th>
                    <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: left !important">Item Detail
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
                @foreach ($purchase_items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="display:flex !important; padding-block: 12px;">
                            {{-- <img src="{{ $item['image'] }}" class="h-10 w-10 shrink-0 rounded-btn" width="56"
                                height="56" alt="Product"> --}}
                            <p style="font-weight: 700; color: #1A1C21;">{{ $item['name'] }}</p>
                            <p style="color: #5E6470;"> {{ $item['size'] }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: center">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ Number::format($item['qty']) }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: center;">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ Number::format($item['price'], precision: 2) }}</p>
                        </td>
                        <td style="padding-block: 12px; text-align: right!important;">
                            <p style="font-weight: 700; color: #1A1C21;">
                                {{ Number::format($item['price'] * $item['qty'], precision: 2) }}</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="padding: 12px 0; border-top:1px solid #D7DAE0;">
                        Notes: {{ $purchase->notes }}
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
                                        {{ Number::format($purchase->quantity) }}</td>
                                </tr>
                                <tr>
                                    <th style="padding-top: 12px;text-align: left !important;; color: #1A1C21;">
                                        Subtotal</th>
                                    <td style="padding-top: 12px;text-align: right !important;; color: #1A1C21;">
                                        {{ Number::format($purchase->total, precision: 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="padding: 12px 0;text-align: left !important; color: #1A1C21;">
                                        Discounts</th>
                                    <td style="padding: 12px 0;text-align: right !important; color: #1A1C21;">
                                        {{ Number::format($purchase->discount) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th
                                        style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: left !important;">
                                        Total Price</th>
                                    <th
                                        style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: right !important;">
                                        {{ Number::format($purchase->total, precision: 2) }}</th>
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
