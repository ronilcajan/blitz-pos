{{-- <!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PDF Document</title>
    <link rel="stylesheet" href="{{ asset('css/pdf.css') }}">
    <style>
        /* *,
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
    } */
        @import "https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap";

        * {
            margin: 0 auto;
            padding: 0 auto;
            user-select: none;
        }

        body {
            padding: 20px;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans",
                "Helvetica Neue", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper-invoice {
            display: flex;
            justify-content: center;
        }

        .wrapper-invoice .invoice {
            height: auto;
            background: #fff;
            padding: 5vh;
            margin-top: 5vh;
            max-width: 110vh;
            width: 100%;
            box-sizing: border-box;
        }

        .wrapper-invoice .invoice .invoice-information {
            float: right;
            text-align: right;
        }

        .wrapper-invoice .invoice .invoice-information b {
            color: "#0F172A";
        }

        .wrapper-invoice .invoice .invoice-information p {
            font-size: 2vh;
            color: gray;
        }

        .wrapper-invoice .invoice .invoice-logo-brand h2 {
            text-transform: uppercase;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans",
                "Helvetica Neue", sans-serif;
            font-size: 2.9vh;
            color: "#0F172A";
        }

        .wrapper-invoice .invoice .invoice-logo-brand img {
            max-width: 100px;
            width: 100%;
            object-fit: fill;
        }

        .wrapper-invoice .invoice .invoice-head {
            display: flex;
            margin-top: 8vh;
        }

        .wrapper-invoice .invoice .invoice-head .head {
            width: 100%;
            box-sizing: border-box;
        }

        .wrapper-invoice .invoice .invoice-head .client-info {
            text-align: left;
        }

        .wrapper-invoice .invoice .invoice-head .client-info h2 {
            font-weight: 500;
            letter-spacing: 0.3px;
            font-size: 2vh;
            color: "#0F172A";
        }

        .wrapper-invoice .invoice .invoice-head .client-info p {
            font-size: 2vh;
            color: gray;
        }

        .wrapper-invoice .invoice .invoice-head .client-data {
            text-align: right;
        }

        .wrapper-invoice .invoice .invoice-head .client-data h2 {
            font-weight: 500;
            letter-spacing: 0.3px;
            font-size: 2vh;
            color: "#0F172A";
        }

        .wrapper-invoice .invoice .invoice-head .client-data p {
            font-size: 2vh;
            color: gray;
        }

        .wrapper-invoice .invoice .invoice-body {
            margin-top: 8vh;
        }

        .wrapper-invoice .invoice .invoice-body .table {
            border-collapse: collapse;
            width: 100%;
        }

        .wrapper-invoice .invoice .invoice-body .table thead tr th {
            font-size: 2vh;
            border: 1px solid #dcdcdc;
            text-align: left;
            padding: 1vh;
            background-color: #eeeeee;
        }

        .wrapper-invoice .invoice .invoice-body .table tbody tr td {
            font-size: 2vh;
            border: 1px solid #dcdcdc;
            text-align: left;
            padding: 1vh;
            background-color: #fff;
        }

        .wrapper-invoice .invoice .invoice-body .table tbody tr td:nth-child(2) {
            text-align: right;
        }

        .wrapper-invoice .invoice .invoice-body .flex-table {
            display: flex;
        }

        .wrapper-invoice .invoice .invoice-body .flex-table .flex-column {
            width: 100%;
            box-sizing: border-box;
        }

        .wrapper-invoice .invoice .invoice-body .flex-table .flex-column .table-subtotal {
            border-collapse: collapse;
            box-sizing: border-box;
            width: 100%;
            margin-top: 2vh;
        }

        .wrapper-invoice .invoice .invoice-body .flex-table .flex-column .table-subtotal tbody tr td {
            font-size: 2vh;
            border-bottom: 1px solid #dcdcdc;
            text-align: left;
            padding: 1vh;
            background-color: #fff;
        }

        .wrapper-invoice .invoice .invoice-body .flex-table .flex-column .table-subtotal tbody tr td:nth-child(2) {
            text-align: right;
        }

        .wrapper-invoice .invoice .invoice-body .invoice-total-amount {
            margin-top: 1rem;
        }

        .wrapper-invoice .invoice .invoice-body .invoice-total-amount p {
            font-weight: bold;
            color: "#0F172A";
            text-align: right;
            font-size: 2vh;
        }

        .wrapper-invoice .invoice .invoice-footer {
            margin-top: 4vh;
        }

        .wrapper-invoice .invoice .invoice-footer p {
            font-size: 1.7vh;
            color: gray;
        }

        @media print {
            .table thead tr th {
                -webkit-print-color-adjust: exact;
                background-color: #eeeeee !important;
            }
        }
    </style>
</head>


<body>
    <section class="wrapper-invoice">
        <!-- switch mode rtl by adding class rtl on invoice class -->
        <div class="invoice">
            <div class="invoice-information">
                <p><b>Invoice #</b> : 12345</p>
                <p><b>Created Date </b>: May, 07 2022</p>
                <p><b>Due Date</b> : May, 09 2022</p>
            </div>
            <!-- logo brand invoice -->
            <div class="invoice-logo-brand">
                <!-- <h2>Tampsh.</h2> -->
                <img src="{{ $purchase->store->avatar }}" width="50" alt="Logo" />
            </div>
            <!-- invoice head -->
            <div class="invoice-head">
                <div class="head client-info">
                    <p>Tampsh, Inc.</p>
                    <p>NPWP: 12.345.678.910.111213.1415</p>
                    <p>Bondowoso, Jawa timur</p>
                    <p>Jln. Rengganis 05, Bondowoso</p>
                </div>
                <div class="head client-data">
                    <p>-</p>
                    <p>Mohammad Sahrullah</p>
                    <p>Bondowoso, Jawa timur</p>
                    <p>Jln. Duko Kembang, Bondowoso</p>
                </div>
            </div>
            <!-- invoice body-->
            <div class="invoice-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Template Invoice</td>
                            <td>Rp.75.000</td>
                        </tr>
                        <tr>
                            <td>tax</td>
                            <td>Rp.5.000</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex-table">
                    <div class="flex-column"></div>
                    <div class="flex-column">
                        <table class="table-subtotal">
                            <tbody>
                                <tr>
                                    <td>Subtotal</td>
                                    <td>Rp.80.000</td>
                                </tr>
                                <tr>
                                    <td>PPN 10%</td>
                                    <td>Rp.5.000</td>
                                </tr>
                                <tr>
                                    <td>Credit</td>
                                    <td>Rp.0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- invoice total  -->
                <div class="invoice-total-amount">
                    <p>Total : Rp.80.000</p>
                </div>
            </div>
            <!-- invoice footer -->
            <div class="invoice-footer">
                <p>Thankyou, happy shopping again</p>
            </div>
        </div>
    </section>
</body>

</html> --}}
<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invoice</title>
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
                            <div>

                                <img src="{{ asset('logo.png') }}" width="50" height="50" alt="Product">
                            </div>
                            <h3 style="font-weight: 700; color: #1A1C21;">{{ $purchase->store->name }}</h3>
                            <p style="color: #5E6470;">{{ $purchase->store->address }}</p>
                            <p style="color: #5E6470;">Purchase TX No: {{ $purchase->tx_no }}</p>
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
                        <th
                            style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align:left !important; width:20px">
                            #
                        </th>
                        <th style="padding: 8px 0; border-top:1px solid #D7DAE0; text-align: left !important">Item
                            Detail
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

                                <span style="font-weight: 700; color: #1A1C21;">{{ $item['name'] }}</span>
                                <span style="color: #5E6470;"> {{ $item['size'] }}</span>
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
                                    <tr style="font-size:16px">
                                        <th
                                            style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: left !important;">
                                            Total Price</th>
                                        <th
                                            style="padding: 12px 0 30px 0; color: #1A1C21;border-top:1px solid #D7DAE0; text-align: right !important; ">
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

</body>

</html>
