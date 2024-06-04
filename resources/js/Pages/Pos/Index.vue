<script setup>
import { reactive, ref } from 'vue';
import POSLayout from '@/Layouts/POSLayout.vue';
import CounterInput from './partials/CounterInput.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: POSLayout })

const props = defineProps({
    title: String,
	customers: Object,
    stores: Object,
    products: Object,
	filter: Object
});

const page = usePage();

const customer = ref('');
const purchase = reactive([]);

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<template>
    <Head :title="title" />
    <div class="grid gap-4 md:grid-cols-5">
        <div class="col-span-5 md:col-span-3">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-6">
                    <div class="flex justify-between gap-2">
                        <div class="w-full">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <input placeholder="Search product name or barcode" class="input input-bordered input-sm w-full"/>

                                <!-- <SearchBar v-model="search" />
                                <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow mt-4 w-full">
                                    <li class="p-2 px-5 border-b">
                                        <button @click="createProductModal = true" type="button" class="font-semibold text-primary">
                                            + Create product
                                        </button>
                                    </li>
                                    <ProductDropdownItems
                                        v-for="product in products.data"
                                        :product="product"
                                        :key="product.id"
                                        @add-products="newPurchase(product)"
                                    />
                                </ul> -->
                            </div>

                        </div>
                <div class="flex gap-2">
                    <PrimaryButton class="btn-sm tooltip" data-tip="Add products" type="button">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </PrimaryButton>
                    <PrimaryButton class="btn-sm tooltip" data-tip="Barcode scan" type="button">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
                    </PrimaryButton>
                </div>
                    </div>
                    <!-- products -->
                    <div class="hidden md:flex flex-wrap gap-3 justify-start mt-4 overflow-x-auto overflow-y-auto" style="height:750px">
                        <div v-for="product in products.data" :key="product.id" class="card w-44 bg-base-100 shadow">
                            <figure class="h-24">
                                <img class="object-fill" v-if="product.image" :src="product.image" alt="Shoes" loading="lazy" />
                            </figure>
                            <div class="p-3">
                                <h1 class="font-bold">
                                    {{ product.name }}
                                </h1>
                                <div class="flex flex-col text-sm">
                                    <div>{{ product.barcode }}</div>
                                    {{ product.size }}
                                    <div>
                                        {{ product.stocks }} {{ product.unit }}
                                    </div>
                                </div>
                                <div class="card-actions justify-end">
                                <button class="btn btn-sm btn-primary mt-2">
                                    {{ $page.props.auth.user.currency }}
                                    {{ formatNumberWithCommas(product.price) }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-span-5 md:col-span-2">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-6">
                    <div class="w-full">
                        <select v-model="customer" class="select select-bordered select-sm w-full">
                            <option selected value="">Select customer</option>
                            <option v-for="customer in customers" :value="customer.name" :key="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>
                    <div style="height:530px" class="mt-4 overflow-x-auto overflow-y-auto">

                        <div class="border border-gray-100 p-2 rounded flex justify-between items-center">
                            <div class="flex gap-3 justify-center items-center">
                                <div class="avatar">
                                    <div class="w-16 rounded-full">
                                        <img src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                                    </div>
                                </div>
                                <div class="">
                                    <div class="font-bold">
                                    Product 1
                                    </div>
                                    <div class="text-sm">
                                    Size 2
                                    </div>
                                </div>
                            </div>
                            <div class="font-bold flex justify-center items-center gap-2">

                                <button type="button" @click="deleteOrder(purchase.id)" class="text-primary hover:text-blue-500">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-circle-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path fill-rule="evenodd" d="M4.929 4.929A10 10 0 1 1 19.07 19.07 10 10 0 0 1 4.93 4.93ZM13 9a1 1 0 1 0 -2 0v2H9a1 1 0 1 0 0 2h2v2a1 1 0 1 0 2 0v-2h2a1 1 0 1 0 0 -2h-2V9Z" clip-rule="evenodd" /></svg>
                                    </button>
                                    100 pcs
                            </div>
                            <div class="flex flex-col items-end">
                                <p class="font-bold mb-2">PHP 2,3423.00</p>
                                <div class="flex gap-2">



                                    <button type="button" @click="deleteOrder(purchase.id)" class="text-orange-900 hover:text-orange-600">
                                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.7535 2.47502H11.5879V1.9969C11.5879 1.15315 10.9129 0.478149 10.0691 0.478149H7.90352C7.05977 0.478149 6.38477 1.15315 6.38477 1.9969V2.47502H4.21914C3.40352 2.47502 2.72852 3.15002 2.72852 3.96565V4.8094C2.72852 5.42815 3.09414 5.9344 3.62852 6.1594L4.07852 15.4688C4.13477 16.6219 5.09102 17.5219 6.24414 17.5219H11.7004C12.8535 17.5219 13.8098 16.6219 13.866 15.4688L14.3441 6.13127C14.8785 5.90627 15.2441 5.3719 15.2441 4.78127V3.93752C15.2441 3.15002 14.5691 2.47502 13.7535 2.47502ZM7.67852 1.9969C7.67852 1.85627 7.79102 1.74377 7.93164 1.74377H10.0973C10.2379 1.74377 10.3504 1.85627 10.3504 1.9969V2.47502H7.70664V1.9969H7.67852ZM4.02227 3.96565C4.02227 3.85315 4.10664 3.74065 4.24727 3.74065H13.7535C13.866 3.74065 13.9785 3.82502 13.9785 3.96565V4.8094C13.9785 4.9219 13.8941 5.0344 13.7535 5.0344H4.24727C4.13477 5.0344 4.02227 4.95002 4.02227 4.8094V3.96565ZM11.7285 16.2563H6.27227C5.79414 16.2563 5.40039 15.8906 5.37227 15.3844L4.95039 6.2719H13.0785L12.6566 15.3844C12.6004 15.8625 12.2066 16.2563 11.7285 16.2563Z" fill=""></path>
                                                    <path d="M9.00039 9.11255C8.66289 9.11255 8.35352 9.3938 8.35352 9.75942V13.3313C8.35352 13.6688 8.63477 13.9782 9.00039 13.9782C9.33789 13.9782 9.64727 13.6969 9.64727 13.3313V9.75942C9.64727 9.3938 9.33789 9.11255 9.00039 9.11255Z" fill=""></path>
                                                    <path d="M11.2502 9.67504C10.8846 9.64692 10.6033 9.90004 10.5752 10.2657L10.4064 12.7407C10.3783 13.0782 10.6314 13.3875 10.9971 13.4157C11.0252 13.4157 11.0252 13.4157 11.0533 13.4157C11.3908 13.4157 11.6721 13.1625 11.6721 12.825L11.8408 10.35C11.8408 9.98442 11.5877 9.70317 11.2502 9.67504Z" fill=""></path>
                                                    <path d="M6.72245 9.67504C6.38495 9.70317 6.1037 10.0125 6.13182 10.35L6.3287 12.825C6.35683 13.1625 6.63808 13.4157 6.94745 13.4157C6.97558 13.4157 6.97558 13.4157 7.0037 13.4157C7.3412 13.3875 7.62245 13.0782 7.59433 12.7407L7.39745 10.2657C7.39745 9.90004 7.08808 9.64692 6.72245 9.67504Z" fill=""></path>
                                                </svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="border p-3 rounded border-gray-100 bg-base-200">
                        <div class="flex justify-between  uppercase">
                            <div>Items</div>
                            <div>2</div>
                        </div>
                        <div class="flex justify-between  uppercase">
                            <div>Sub-total</div>
                            <div>2000</div>
                        </div>
                        <div class="flex justify-between  uppercase">
                            <div>Discount</div>
                            <div>2000</div>
                        </div>
                        <div class="flex justify-between uppercase">
                            <div class="text-2xl font-bold">Grand Total</div>
                            <div class="text-2xl font-bold">2000</div>
                        </div>
                    </div>

                    <div class="flex p-2 gap-3 justify-end">
                        <div>
                            <button class="btn lg:btn-lg">Cancel</button>
                        </div>
                        <div>
                            <button class="btn lg:btn-lg btn-primary">Finish</button>

                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
