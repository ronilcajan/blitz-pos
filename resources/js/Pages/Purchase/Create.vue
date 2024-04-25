<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductDropdownItems from './partials/ProductDropdownItems.vue';
import CounterInput from './partials/CounterInput.vue';
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch,watchEffect, computed  } from 'vue';
import debounce from "lodash/debounce";

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    categories: Object,
    suppliers: Object,
    products: Object,
    search_products: Object,
    filter: Object
});

const purchases = reactive([]);
const barcode = ref(props.filter.barcode);
const search = ref(props.filter.search);
const discount = ref(0);

watch(search, debounce(function (value) {
	router.get('/purchase/create',
	{ search: value },
	{ preserveState: true, replace:true, only: ['products'] }
   )
}, 500)) ;

watchEffect(async () => {
    router.get('/purchase/create',
	{ barcode: barcode.value },
	{ preserveState: true, replace:true,
        onSuccess: () => {
            if (!barcode.value) {
                return;
            }
            if (!props.search_products) {
                useToast().error('Product not found!', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });
                return;
            }
            const foundProduct = findProductById(props.search_products.id, purchase)
            if (foundProduct) {
                updateOrderQuantity(foundProduct)
            } else {
                const newOrder = createOrderFromProduct(props.search_products);
                addToOrders(newOrder);
            }

            barcode.value = '';
        }, })
})

const newPurchase = (product) => {
    const foundProduct = findProductById(product.id, purchases)
    if (foundProduct) {
        updateOrderQuantity(foundProduct)
    } else {
        const newOrder = createOrderFromProduct(product);
        addToOrders(newOrder);
    }

}

const findProductById = (product_id, search_products) => {
    return search_products.find(product => product.id === product_id);
}
const createOrderFromProduct = (product) => {
    return {
        id: product.id,
        product: product.name,
        details: product.size,
        qty: 1,
        unit: product.unit,
        stocks: product.stocks,
        price: parseFloat(product.price ?? 0.00).toFixed(2),
        image: product.image,
        get total() {
            return (this.qty * parseFloat(this.price)).toFixed(2);
        },
    };
}
const updateOrderQuantity = (purchase) => {
    purchase.qty++;
}
const deleteOrder = (order_id) => {
    purchases.splice(purchases.findIndex(order => order.id === order_id), 1);
}

const addToOrders = (products) => {
    purchases.push(products);
}

const calculateSubTotal = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    return formatNumberWithCommas(subTotal);
})
const calculateQty = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.qty), 0);
    return formatNumberWithCommas(subTotal);
})
const calculateDiscount = computed(() => {
    return discount.value.toFixed(2);
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
</script>

<template>
    <Head :title="title" />

    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div class="flex justify-between gap-2 flex-col sm:flex-row">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Purchase details</span>
                            </h2>
                        </div>
                    </div>
                    <div class="flex gap-4 flex-col md:flex-row md:w-1/2">
                        <div class="w-full md:w-1/2">
                            <InputLabel for="date" class="label" value="Supplier" />
                            <select class="select select-bordered w-full select-sm" id="supplier">
                                <option value="">Select supplier</option>
                                <option v-for="supplier in suppliers" :key="supplier.id">
                                    {{ supplier.name }}</option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/2">
                                <InputLabel for="date" class="label" value="Transaction Date" />
                                <TextInput type="date" class="input input-sm w-full"/>
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 flex-col sm:flex-row mb-2 mt-4">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Purchase items</span>
                            </h2>
                        </div>
                    </div>
                    <div class="dropdown mb-5 p-3 bg-base-300 rounded">
                        <div class="flex justify-between gap-2">
                            <div class="w-full">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                    </div>
                                    <input placeholder="Search product name or barcode" v-model="search" class="input pl-8 input-bordered input-sm w-full"/>
                                    <button type="button" v-if="search" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="search = ''">
                                        <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                        </svg>
                                    </button>
                                    <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow mt-4 w-full">
                                        <li class="p-2 px-5 border-b">
                                            <button class="font-semibold text-primary">
                                                + Create product
                                            </button>
                                        </li>
                                        <ProductDropdownItems
                                            v-for="product in products.data"
                                            :product="product"
                                            :key="product.id"
                                            @add-products="newPurchase(product)"
                                        />
                                    </ul>
                                </div>

                            </div>
                            <div class="hidden sm:flex">
                                <PrimaryButton class="btn-sm">Browse</PrimaryButton>
                            </div>
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
                                    <th class="text-right">
                                        <div class="font-bold mr-7">Quantity</div>
                                    </th>
                                    <th class="text-left">
                                        <div class="font-bold">Price</div>
                                    </th>
                                    <th class="text-right">
                                        <div class="font-bold mr-10">Sub-total</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(purchase, index) in purchases" :key="purchase.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="flex gap-2 grow flex-col md:flex-row">
                                            <img v-if="purchase.image" class="h-12 w-12 shrink-0 rounded-btn" width="56" height="56" :src="purchase.image" alt="Product">
                                            <div class="flex flex-col gap-1">
                                                <div class="text-sm">
                                                    {{ purchase.product }}
                                                </div>
                                                <div class="text-xs text-base-content/50" v-if="purchase.stocks > 10">
                                                    In stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                                <div class="text-xs text-red-500" v-if="purchase.stocks < 5">
                                                    Out of stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                                <div class="text-xs text-yellow-500" v-if="purchase.stocks > 5 && purchase.stocks < 10">
                                                    Low stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end">
                                            <CounterInput v-model="purchase.qty" @increase-by="(n) => purchase.qty += n" @decreased-by="(n) => purchase.qty > 1 ? purchase.qty -= n : 0"/>
                                        <span class="pl-5 pt-2">x</span>
                                        </div>

                                    </td>
                                    <td class="text-left">
                                        <div class="flex justify-start">
                                        <NumberInput type="number" min="1" v-model="purchase.price" class="input input-sm w-24" />
                                        <span class="ml-1 pt-2">{{ purchase.unit }}</span>
                                    </div>

                                    </td>
                                    <td class="sm:table-cell text-right">
                                        <span class="mr-3">
                                            {{ formatNumberWithCommas(purchase.total) }}</span>
                                            <button @click="deleteOrder(purchase.id)" class="text-orange-900 hover:text-orange-600 mt-5">
                                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.7535 2.47502H11.5879V1.9969C11.5879 1.15315 10.9129 0.478149 10.0691 0.478149H7.90352C7.05977 0.478149 6.38477 1.15315 6.38477 1.9969V2.47502H4.21914C3.40352 2.47502 2.72852 3.15002 2.72852 3.96565V4.8094C2.72852 5.42815 3.09414 5.9344 3.62852 6.1594L4.07852 15.4688C4.13477 16.6219 5.09102 17.5219 6.24414 17.5219H11.7004C12.8535 17.5219 13.8098 16.6219 13.866 15.4688L14.3441 6.13127C14.8785 5.90627 15.2441 5.3719 15.2441 4.78127V3.93752C15.2441 3.15002 14.5691 2.47502 13.7535 2.47502ZM7.67852 1.9969C7.67852 1.85627 7.79102 1.74377 7.93164 1.74377H10.0973C10.2379 1.74377 10.3504 1.85627 10.3504 1.9969V2.47502H7.70664V1.9969H7.67852ZM4.02227 3.96565C4.02227 3.85315 4.10664 3.74065 4.24727 3.74065H13.7535C13.866 3.74065 13.9785 3.82502 13.9785 3.96565V4.8094C13.9785 4.9219 13.8941 5.0344 13.7535 5.0344H4.24727C4.13477 5.0344 4.02227 4.95002 4.02227 4.8094V3.96565ZM11.7285 16.2563H6.27227C5.79414 16.2563 5.40039 15.8906 5.37227 15.3844L4.95039 6.2719H13.0785L12.6566 15.3844C12.6004 15.8625 12.2066 16.2563 11.7285 16.2563Z" fill=""></path>
                                                    <path d="M9.00039 9.11255C8.66289 9.11255 8.35352 9.3938 8.35352 9.75942V13.3313C8.35352 13.6688 8.63477 13.9782 9.00039 13.9782C9.33789 13.9782 9.64727 13.6969 9.64727 13.3313V9.75942C9.64727 9.3938 9.33789 9.11255 9.00039 9.11255Z" fill=""></path>
                                                    <path d="M11.2502 9.67504C10.8846 9.64692 10.6033 9.90004 10.5752 10.2657L10.4064 12.7407C10.3783 13.0782 10.6314 13.3875 10.9971 13.4157C11.0252 13.4157 11.0252 13.4157 11.0533 13.4157C11.3908 13.4157 11.6721 13.1625 11.6721 12.825L11.8408 10.35C11.8408 9.98442 11.5877 9.70317 11.2502 9.67504Z" fill=""></path>
                                                    <path d="M6.72245 9.67504C6.38495 9.70317 6.1037 10.0125 6.13182 10.35L6.3287 12.825C6.35683 13.1625 6.63808 13.4157 6.94745 13.4157C6.97558 13.4157 6.97558 13.4157 7.0037 13.4157C7.3412 13.3875 7.62245 13.0782 7.59433 12.7407L7.39745 10.2657C7.39745 9.90004 7.08808 9.64692 6.72245 9.67504Z" fill=""></path>
                                                </svg>
                                            </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-4 justify-between flex-col md:flex-row mt-5">
                        <div class="w-full md:w-1/2">
                            <InputLabel class="label" value="Notes" />
                            <textarea class="textarea textarea-bordered w-full max-w-xs" placeholder="Type here"></textarea>
                        </div>
                        <div class="flex w-full md:w-1/2 justify-end">
                            <div class="bg-base-200 w-full md:w-2/3 rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                <div class="flex justify-between mb-2">
                                    <span>Quantity:</span>
                                    <span>{{ calculateQty }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>{{ calculateSubTotal }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <button class="font-semibold text-primary">
                                        Discount(+/-):
                                            </button>
                                    <span>{{ calculateDiscount }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold">
                                    <span>Total:</span>
                                    <span>{{ calculateTotal }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full justify-center mb-10">
                        <InputLabel class="label" value="Status" />
                        <div class="join">
                            <input class="join-item btn btn-sm" type="radio" name="options" aria-label="Pending" checked/>
                            <input class="join-item btn btn-sm" type="radio" name="options" aria-label="Completed" />
                            <input class="join-item btn btn-sm" type="radio" name="options" aria-label="Cancelled" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 flex-col lg:flex-row">
                            <div class="flex justify-end gap-3 flex-col md:flex-row">
                                <NavLink href="/purchase" class="btn btn-sm">
                                    <svg class="w-5 h-5 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                    </svg>
                                    Cancel</NavLink>
                                <PrimaryButton type="submit"
                                    class="btn btn-sm"
                                >

                                    Create Purchase
                                </PrimaryButton>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</template>
