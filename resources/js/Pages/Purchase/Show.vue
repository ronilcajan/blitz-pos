<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductDropdownItems from './partials/ProductDropdownItems.vue';
import CounterInput from './partials/CounterInput.vue';
import { router, useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch, watchEffect, computed, onMounted  } from 'vue';
import debounce from "lodash/debounce";

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

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<template>
    <Head :title="title" />
    <form @submit.prevent="submitPurchaseForm">
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div class="flex justify-center gap-2 flex-col sm:flex-row">
                        <div>
                            <h2 class="text-xl font-bold">
                                <span class="uppercase ">Purchase Summary Details</span>
                            </h2>
                        </div>
                    </div>
                    <div class="flex gap-4 flex-col md:flex-row md:w-1/2">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="date" class="label" value="Supplier"  />
                                    <select class="select select-bordered w-full select-sm" id="supplier" required>
                                        <option value="">Select supplier</option>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <button class="btn btn-square btn-outline btn-primary btn-sm" type="button" @click="createSupplierModal = true">
                                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                                        </svg>
                                 </button>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2">
                                <InputLabel for="date" class="label" value="Transaction Date" />
                                <TextInput type="date" class="input input-sm w-full" required/>
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 flex-col sm:flex-row mb-2 mt-4">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Purchase items</span>
                            </h2>
                        </div>
                    </div>

                    <div class="flex gap-4 justify-between flex-col md:flex-row mt-5">
                        <div class="w-full md:w-1/2">
                            <InputLabel class="label" value="Notes" />
                            <textarea class="textarea textarea-bordered w-full max-w-xs" placeholder="Type here" ></textarea>
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
                                    <button class="font-semibold text-primary" type="button" @click="addDiscountModal = true">
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
            </div>
        </div>
    </div>
    </form>
</template>
