<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Acknowledgement</title>

    @vite(['resources/css/app.css'])
    <style>
        *,
        body {
            font-size: 12px;
            width: 80mm;
            margin: 0;
            padding: 0;
        }

        @page {
            size: 80mm auto;
            margin: 5mm;
        }

        @media print {

            .hidden-print,
            .hidden-print * {
                display: none !important;
            }

            @page {
                size: 80mm auto;
                margin: 5mm;
            }

            body {
                max-width: 80mm;
                height: auto;
                /* Ensure height adjusts to content */
            }
        }
    </style>
</head>

<body class="m-2">
    <div class="ticket">
        <div class="flex justify-center">
            <img class="w-20" src="{{ $sale->store->avatar }}" alt="Logo">
        </div>
        <div class="text-center mt-3 mb-2">
            <p class="font-bold text-xl">{{ $sale->store->name }}</p>
            <p class="text-sm">Address: {{ $sale->store->address }}</p>
            <p class="text-sm">Email: {{ $sale->store->email }}</p>
            <p class="text-sm">Contact: {{ $sale->store->contact }}</p>
        </div>
        <hr>
        <div class="text-center mt-2 mb-3">
            <p class="font-bold">ACKNOWLEDGEMENT RECEIPT </p>
            <p class="text-sm">Invoice No: {{ $sale->tx_no }}</p>
            <p class="text-sm">Date: {{ date('m/d/Y h:i A', strtotime($sale->created_at)) }}</p>
            <p class="text-sm">Served By: {{ $sale->user->name }}</p>

        </div>
        <hr>
        <p class="mt-3" style="font-weight:bold"> Purchase item/s: {{ number_format($sale->quantity) }}</p>

        <div class="my-2">
            <div class="flex justify-between font-bold uppercase">
                <p>Description</p>
                <p class="text-right ">Amount</p>
            </div>
            @foreach ($items as $item)
                <div class="flex justify-between">
                    <p>{{ $item->product->name }} {{ $item->quantity }} x {{ $item->price }}</p>
                    <p class="text-right ">₱ {{ $item->price }}</p>
                </div>
            @endforeach
        </div>

        <hr style="border-top: 1px dashed black">
        <div class="my-2">
            <div class="flex justify-between">
                <p>Subtotal</p>
                <p class="text-right">₱ {{ number_format($sale->sub_total, 2) ?? null }}</p>
            </div>
            <div class="flex justify-between">
                <p>Discount</p>
                <p class="text-right ">₱ -{{ number_format($sale->discount, 2) ?? null }}</p>
            </div>
            <div class="flex justify-between font-bold uppercase ">
                <p class="text-2xl">Grand Total</p>
                <p class="text-right text-2xl">₱ {{ number_format($sale->total, 2) ?? null }}</p>
            </div>

        </div>

        <hr style="border-top: 1px dashed black">
        <div class="flex justify-between mt-2">
            <p class="">Amount Tendered</p>
            <p class="text-right">₱ {{ number_format($sale->payment_tender, 2) ?? null }}</p>
        </div>
        <div class="flex justify-between font-bold uppercase ">
            <p class="text-lg">Changed</p>
            <p class="text-right text-lg">₱ {{ number_format($sale->payment_changed, 2) ?? null }}</p>
        </div>
        <div class="my-2">
            <p class="text-sm">Payment method: {{ $sale->payment_method }}</p>
            @if ($sale->referrence)
                <p class="text-sm">Referrence: {{ $sale->referrence }}</p>
            @endif
            <p class="text-sm">Powered by: POSblend.</p>
        </div>
        <div class="flex justify-center my-3">
            @php
                echo DNS1D::getBarcodeHTML($sale->tx_no, 'C39', 1.4, 60);
            @endphp
        </div>
        <hr>

        <p class="text-center my-4">Thank you and come again!</p>
    </div>
    <script>
        window.print();
        setTimeout(window.close, 2000);
    </script>
</body>

</html>
