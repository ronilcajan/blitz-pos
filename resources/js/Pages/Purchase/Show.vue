<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { reactive, ref, computed, onMounted  } from 'vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    categories: Object,
    suppliers: Object,
    stores: Object,
    units: Object,
    products: Object,
    purchase: Object,
    purchase_items: Object,
    search_products: Object,
    filter: Object
});

const purchases = reactive([]);
const discount = ref(0);

const addToOrders = (products) => {
    purchases.push(products);
}
onMounted(() => {
    const items = props.purchase_items;
    items.map(item => {
        const product = {
            id: item.id,
            product: item.name,
            details: item.size,
            qty: parseFloat(item.qty),
            unit: item.unit,
            stocks: parseFloat(item.stocks),
            price: parseFloat(item.price).toFixed(2),
            image: item.image,
            get total() {
                return (this.qty * parseFloat(this.price)).toFixed(2);
            },
        };
        addToOrders(product);
    });
})
const calculateSubTotal = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    return formatNumberWithCommas(subTotal);
})
const calculateQty = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.qty), 0);
    return formatNumberWithCommas(subTotal);
})
const calculateDiscount = computed(() => {
    return formatNumberWithCommas(discount.value.toFixed(2));
})
const calculateTotal = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    const discountValue = parseFloat(discount.value);
    const total = subTotal - discountValue;
    return formatNumberWithCommas(total.toFixed(2));
});
const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const formatDate = (dateString) => {
    let date = new Date(dateString);
    let options = { year: 'numeric', month: 'long', day: '2-digit'};
    return date.toLocaleString('en-US', options);
}
</script>

<template>
    <Head :title="title" />
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div id="purchaseContainer" ref="purchaseContainer">
                        <div>
                            <h1 class="text-3xl font-bold mb-4 ">
                                <span>Purchase Order #0{{ purchase.id }}</span>
                            </h1>
                        </div>
                        <div class="flex justify-start sm:justify-end gap-2">
                            <div class="flex flex-col mb-3 items-start sm:items-end">
                                <h1 class="text-xl font-bold">
                                    <span>Store 1 </span>
                                </h1>
                                <p>Locc Proper Plaridel Misamis occidental</p>
                                <p>Date: {{ formatDate(purchase.created_at) }}</p>
                                <p>Status: {{ purchase.status }}</p>
                            </div>
                        </div>
                        <h2 class="font-bold text-xl">Supplier </h2>
                        <div class="flex justify-start gap-2 flex-col sm:flex-row">
                            <div class="">
                                <p class="font-semibold">{{ purchase.supplier.name }}</p>
                                <p class="font-semibold">{{ purchase.supplier.contact_person }}</p>
                                <p class="text-gray-500">{{ purchase.supplier.address }}</p>
                                <p class="text-gray-500">{{ purchase.supplier.email }}</p>
                                <p class="text-gray-500">{{ purchase.supplier.phone }}</p>
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
                                    <tr v-for="(purchase, index) in purchases" :key="purchase.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>
                                            <div class="flex gap-2 grow flex-col md:flex-row">
                                                <img v-if="purchase.image" class="h-10 w-10 shrink-0 rounded-btn" width="56" height="56" :src="purchase.image" alt="Product">
                                                <div class="flex flex-col gap-1">
                                                    <div class="text-sm">
                                                        {{ purchase.product }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pl-5 pt-2">{{ purchase.qty }}</span>
                                        </td>
                                        <td>
                                            <span class="ml-1 pt-2">{{ purchase.price + ' ' +purchase.unit }}</span>
                                        </td>
                                        <td>
                                            <span class="mr-3">
                                                {{ formatNumberWithCommas(purchase.total) }}</span>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="flex gap-4 justify-between mt-5 flex-col-reverse sm:flex-row">
                            <div class="w-full sm:w-1/2">
                                <InputLabel class="label" value="Notes" />
                                <textarea v-model="purchase.notes" class="textarea textarea-bordered w-full max-w-md" placeholder="Type here" ></textarea>
                            </div>
                            <div class="w-full sm:w-1/2 flex justify-end">
                                <div class="bg-base-200 w-full rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                    <div class="flex justify-between mb-2">
                                        <span>Items:</span>
                                        <span>{{ calculateQty }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>{{ calculateSubTotal }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <button class="font-semibold" type="button" @click="addDiscountModal = true">
                                            Discount(+/-):
                                                </button>
                                        <span class="text-red-500">{{ calculateDiscount }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total:</span>
                                        <span>{{ calculateTotal }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 flex-col lg:flex-row mt-10">
                        <div class="flex justify-end gap-3 flex-col md:flex-row">
                            <CancelButton href="/purchase" />
                            <DownloadButton :href="route('purchase.downloadPDF', purchase.id)"> Download PDF </DownloadButton>
                            <PrintButton :id="'purchaseContainer'" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
