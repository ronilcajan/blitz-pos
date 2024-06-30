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

        table.products th {
            padding: 2px;
            text-align: left;
            border: gray solid 1px;
        }

        table.products td {
            padding: 2px;
            text-align: left;
            border: gray solid 1px;
        }

        table tr.items {
            /* background-color: rgb(241 245 249); */
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
                            <img width="100" src="{{ public_path('storage/' . $store->avatar) }}">
                        </td>
                        <td style="text-align:left; padding:12px">
                            <h2 style="font-weight: 700; color: #1A1C21;"> {{ $store->name }}
                            </h2>
                            <p style="color: #5E6470;">Email: {{ $store->email }}</p>
                            <p style="color: #5E6470;">Contact: {{ $store->contact }}</p>
                            <p style="color: #5E6470;">Address: {{ $store->address }}</p>
                        </td>
                    </tr>
                </thead>
            </table>

            <h3 style="font-weight: 700; color: #1A1C21; margin-top:10px; margin-bottom:10px">ITEMS</h3>

            <table class="products" style="font-size: 12px; line-height: 20px; width:100%">
                <thead>
                    <tr style="text-align: left">
                        <th>
                            Name
                        </th>
                        <th>
                            Barcode
                        </th>
                        <th>
                            Size
                        </th>
                        <th>
                            Unit
                        </th>
                        <th>
                            Type
                        </th>
                        <th>
                            Brand
                        </th>
                        <th>
                            Expiration date
                        </th>
                        <th>
                            Description
                        </th>
                        <th>
                            Category
                        </th>
                        <th>
                            SKU
                        </th>
                        <th>
                            In store
                        </th>
                        <th>
                            In Warehouse
                        </th>
                        <th>
                            Base price
                        </th>
                        <th>
                            Markup price
                        </th>
                        <th>
                            Price
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr class="items">
                            <td>
                                {{ $item['name'] }}
                            </td>
                            <td>
                                {{ $item['barcode'] }}
                            </td>
                            <td>
                                {{ $item['size'] }}
                            </td>
                            <td>
                                {{ $item['unit'] }}
                            </td>
                            <td>
                                {{ $item['product_type'] }}
                            </td>
                            <td>
                                {{ $item['brand'] }}
                            </td>
                            <td>
                                {{ $item['expiration_date'] }}
                            </td>
                            <td>
                                {{ $item['description'] }}
                            </td>
                            <td>
                                {{ $item['category'] }}
                            </td>
                            <td>
                                {{ $item['sku'] }}
                            </td>
                            <td>
                                {{ $item['in_store'] }}
                            </td>
                            <td>
                                {{ $item['in_warehouse'] }}
                            </td>
                            <td>
                                {{ $item['base_price'] }}
                            </td>
                            <td>
                                {{ $item['markup_price'] }}
                            </td>
                            <td>
                                {{ $item['price'] }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="footer margin-top" style="margin: 8px 16px">
            <div>{{ date('Y') }} &copy; BizRoon Inventory</div>
        </div>
    </body>

</body>

</html>
