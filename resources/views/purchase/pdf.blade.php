<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite('resources/css/app.css')

</head>

<body>
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div id="purchaseContainer">
                        <div>
                            <h1 class="text-3xl font-bold">
                                <span>Purchase Order #{{ $purchase->id }}</span>
                            </h1>
                        </div>
                        <div class="flex justify-end gap-2 flex-col sm:flex-row">
                            <div class="text-right">
                                <h1 class="text-xl font-bold">
                                    <span>Store 1 </span>
                                </h1>
                                <p>Locc Proper Plaridel Misamis occidental</p>
                                <p>Date: {{ $purchase->created_at }}</p>
                            </div>
                        </div>
                        <h2 class="font-bold text-xl">Supplier </h2>
                        <div class="flex justify-start gap-2 flex-col sm:flex-row">
                            <div class="">
                                <p class="font-semibold">{{ $purchase->supplier->name }}</p>
                                <p class="font-semibold">{{ $purchase->supplier->contact_person }}</p>
                                <p class="text-gray-500">{{ $purchase->supplier->address }}</p>
                                <p class="text-gray-500">{{ $purchase->supplier->email }}</p>
                                <p class="text-gray-500">{{ $purchase->supplier->phone }}</p>
                            </div>
                        </div>

                        <div class="flex justify-between gap-2 flex-col sm:flex-row mb-2 mt-4">
                            <div>
                                <h2 class="card-title grow text-sm">
                                    <span class="uppercase">Purchase items</span>
                                </h2>
                            </div>
                        </div>

                        <div class="overflow-x-auto border-y-2">
                            <table class="table table-sm">
                                <thead class="uppercase">
                                    <tr>
                                        <th>
                                            <div class="font-bold">#</div>
                                        </th>
                                        <th>
                                            <div class="font-bold">Products</div>
                                        </th>
                                        <th>
                                            <div class="font-bold mr-7">Quantity</div>
                                        </th>
                                        <th>
                                            <div class="font-bold">Price</div>
                                        </th>
                                        <th>
                                            <div class="font-bold mr-10">Sub-total</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase_items as $index => $item)
                                        <tr>
                                            <td>{{ $index }}</td>
                                            <td>
                                                <div class="flex gap-2 grow flex-col md:flex-row">
                                                    <img v-if="purchase.image" class="h-10 w-10 shrink-0 rounded-btn"
                                                        width="56" height="56" src="{{ $item['image'] }}"
                                                        alt="Product">
                                                    <div class="flex flex-col gap-1">
                                                        <div class="text-sm">
                                                            {{ $item['name'] }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $item['qty'] }}</td>
                                            <td>{{ $item['price'] . ' ' . $item['unit'] }}</td>
                                            <td>{{ $item['price'] * $item['qty'] }}</td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>

                        <div class="flex gap-4 justify-between mt-5 flex-col sm:flex-row">
                            <div class="w-full sm:w-1/2">
                                <InputLabel class="label" value="Notes" />
                                <textarea v-model="purchase.notes" class="textarea textarea-bordered w-full max-w-md" placeholder="Type here"></textarea>
                            </div>
                            <div class="w-full sm:w-1/2 flex justify-end">
                                <div class="bg-base-200 w-full rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                    <div class="flex justify-between mb-2">
                                        <span>Items:</span>
                                        <span>{{ $purchase->quantity }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>{{ $purchase->total }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <button class="font-semibold" type="button" @click="addDiscountModal = true">
                                            Discount(+/-):
                                        </button>
                                        <span class="text-red-500">{{ $purchase->discount }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total:</span>
                                        <span>{{ $purchase->total - $purchase->discount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
