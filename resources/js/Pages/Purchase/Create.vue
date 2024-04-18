<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch,watchEffect  } from 'vue';
import CounterInput from './partials/CounterInput.vue';
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

const orders = reactive([]);
const barcode = ref(props.filter.barcode);
const search = ref(props.filter.search);

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
            const foundProduct = findProductById(props.search_products.id, orders)
            if (foundProduct) {
                updateOrderQuantity(foundProduct)
            } else {
                const newOrder = createOrderFromProduct(props.search_products);
                addToOrders(newOrder);
            }
           
            barcode.value = '';
        }, })
})

const findProductById = (product_id, search_products) => {
    return search_products.find(product => product.id === product_id);
}
const createOrderFromProduct = (product) => {
    return {
        id: product.id,
        product: product.name,
        details: product.size,
        qty: 1,
        price: parseFloat(product.stock?.unit_price ?? 0).toFixed(2),
        image: product.image,
        get total() {
            return (this.qty * this.price).toFixed(2);
        },
    };
}
const updateOrderQuantity = (order) => {
    order.qty++;
}
const deleteOrder = (order_id) => {
    orders.splice(orders.findIndex(order => order.id === order_id), 1);
}

const addToOrders = (products) => {
    orders.push(products);
}

</script>

<template>
    <Head :title="title" />
    
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full md:w-2/3">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                  
                    <div class="flex justify-between gap-2 flex-col sm:flex-row">
                        <span class="label-text text-lg font-bold mb-5">Products to purchase</span>
                <div class="flex gap-2 flex-col sm:flex-row">

                   
                    <div class="w-full mb-3">
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative sm:w-60 w-full">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input placeholder="Search for barcode" v-model="barcode" class="input pl-8 input-bordered input-sm w-full max-w-xs"/>
                            <button type="button" v-if="barcode" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="barcode = ''">
                                <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                </svg>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
                <div class="overflow-x-auto"> 
                    <table class="table table-sm">
                                <thead class="uppercase">
                                    <tr>
                                        <th>
                                            <div class="font-bold">NO</div>
                                        </th>
                                        <th>
                                            <div class="font-bold">Products</div>
                                        </th>
                                        <th>
                                            <div class="font-bold">QTY</div>
                                        </th>
                                        <th class="hidden sm:table-cell">
                                            <div class="font-bold ">Sub-total</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(order, index) in orders" :key="order.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>
                                            <div class="flex gap-2 grow flex-col md:flex-row">
                                                <img v-if="order.image" class="h-10 w-10 shrink-0 rounded-btn" width="56" height="56" :src="order.image" alt="Product">
                                                <div class="flex flex-col gap-1">
                                                    <div class="text-sm">{{ order.product }}</div>
                                                <div class="text-xs text-base-content/50">{{ order.details }}</div>
                                                <div class="w-2">
                                                    <input type="input w-10">
                                                </div>
                                                    
                                                    </div>
                                            </div>
                                        </td>
                                        <td>
                                            <CounterInput v-model="order.qty" @increase-by="(n) => order.qty += n" @decreased-by="(n) => order.qty > 1 ? order.qty -= n : 0"/>
                                            <div class="block md:hidden ">
                                                <p class="mt-5">Sub-total <br>P {{ order.price * order.qty }}</p>
                                                <button @click="deleteOrder(order.id)" class="text-orange-900 hover:text-orange-600 mt-5">
                                                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M13.7535 2.47502H11.5879V1.9969C11.5879 1.15315 10.9129 0.478149 10.0691 0.478149H7.90352C7.05977 0.478149 6.38477 1.15315 6.38477 1.9969V2.47502H4.21914C3.40352 2.47502 2.72852 3.15002 2.72852 3.96565V4.8094C2.72852 5.42815 3.09414 5.9344 3.62852 6.1594L4.07852 15.4688C4.13477 16.6219 5.09102 17.5219 6.24414 17.5219H11.7004C12.8535 17.5219 13.8098 16.6219 13.866 15.4688L14.3441 6.13127C14.8785 5.90627 15.2441 5.3719 15.2441 4.78127V3.93752C15.2441 3.15002 14.5691 2.47502 13.7535 2.47502ZM7.67852 1.9969C7.67852 1.85627 7.79102 1.74377 7.93164 1.74377H10.0973C10.2379 1.74377 10.3504 1.85627 10.3504 1.9969V2.47502H7.70664V1.9969H7.67852ZM4.02227 3.96565C4.02227 3.85315 4.10664 3.74065 4.24727 3.74065H13.7535C13.866 3.74065 13.9785 3.82502 13.9785 3.96565V4.8094C13.9785 4.9219 13.8941 5.0344 13.7535 5.0344H4.24727C4.13477 5.0344 4.02227 4.95002 4.02227 4.8094V3.96565ZM11.7285 16.2563H6.27227C5.79414 16.2563 5.40039 15.8906 5.37227 15.3844L4.95039 6.2719H13.0785L12.6566 15.3844C12.6004 15.8625 12.2066 16.2563 11.7285 16.2563Z" fill=""></path>
                                                        <path d="M9.00039 9.11255C8.66289 9.11255 8.35352 9.3938 8.35352 9.75942V13.3313C8.35352 13.6688 8.63477 13.9782 9.00039 13.9782C9.33789 13.9782 9.64727 13.6969 9.64727 13.3313V9.75942C9.64727 9.3938 9.33789 9.11255 9.00039 9.11255Z" fill=""></path>
                                                        <path d="M11.2502 9.67504C10.8846 9.64692 10.6033 9.90004 10.5752 10.2657L10.4064 12.7407C10.3783 13.0782 10.6314 13.3875 10.9971 13.4157C11.0252 13.4157 11.0252 13.4157 11.0533 13.4157C11.3908 13.4157 11.6721 13.1625 11.6721 12.825L11.8408 10.35C11.8408 9.98442 11.5877 9.70317 11.2502 9.67504Z" fill=""></path>
                                                        <path d="M6.72245 9.67504C6.38495 9.70317 6.1037 10.0125 6.13182 10.35L6.3287 12.825C6.35683 13.1625 6.63808 13.4157 6.94745 13.4157C6.97558 13.4157 6.97558 13.4157 7.0037 13.4157C7.3412 13.3875 7.62245 13.0782 7.59433 12.7407L7.39745 10.2657C7.39745 9.90004 7.08808 9.64692 6.72245 9.67504Z" fill=""></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        
                                        </td>
                                        <td class="hidden sm:table-cell">
                                            <span class="">
                                                P {{ order.total }}</span>
                                        </td>
                                        <td class="hidden sm:table-cell">
                                            <button @click="deleteOrder(order.id)" class="text-orange-900 hover:text-orange-600 mt-5">
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
                    
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                        <div class="flex gap-2">
                            <div class="w-full justify-end  mb-3">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                    </div>
                                    <input placeholder="Search for products" v-model="search" class="input pl-8 input-bordered input-sm w-full"/>
                                    <button type="button" v-if="search" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="search = ''">
                                        <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                        </svg>
                                    </button>
                                </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto"> 
                        <table class="table table-sm">
                            <thead class="uppercase">
                                <tr>
                                    <th>
                                        <div class="font-bold">Products</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">QTY</div>
                                    </th>
                                    <th class="hidden sm:table-cell">
                                        <div class="font-bold ">Unit Price</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in products.data" :key="product.id" class="cursor-pointer hover">
                                    <td>
                                        <div class="flex gap-2 grow flex-col md:flex-row">
                                            <img v-if="product.image" class="h-10 w-10 shrink-0 rounded-btn" width="56" height="56" :src="product.image" alt="Product">
                                            <div class="flex flex-col gap-1">
                                                <div class="text-sm">
                                                    {{ product.name }}
                                                </div>
                                                <div class="text-xs text-base-content/50">
                                                    {{ product.size }}
                                                </div>
                                                <div class="text-xs text-base-content/50">
                                                    {{ product.barcode }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="">
                                            {{ product.stocks +' '+product.unit }}</span>
                                    
                                    </td>
                                    <td class="hidden sm:table-cell">
                                        <span class="">
                                            P {{ product.unit_price }}</span>
                                    </td>
                                </tr> 
                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
