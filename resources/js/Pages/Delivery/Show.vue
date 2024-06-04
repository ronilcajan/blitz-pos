<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    delivery: Object,
    delivery_items: Object,
});

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
                                <span>Delivery Details #0{{ delivery.id }}</span>
                            </h1>
                        </div>
                        <div class="flex justify-start sm:justify-end gap-2">
                            <div class="flex flex-col mb-3 items-start sm:items-end">
                                <h1 class="text-xl font-bold">
                                    <span>{{ delivery.store.name }}</span>
                                </h1>
                                <p>{{ delivery.store.address }}</p>
                                <p>Delivery Tx No: {{ delivery.tx_no }}</p>
                                <p v-if="delivery.purchase?.tx_no">Purchase Order: {{ delivery.purchase?.tx_no }}</p>
                                <p>Date: {{ delivery.date }}</p>
                                <p>Status: {{ delivery.status }}</p>
                            </div>
                        </div>
                        <h2 class="font-bold text-xl">Supplier </h2>
                        <div class="flex justify-start gap-2 flex-col sm:flex-row">
                            <div class="">
                                <p class="font-semibold">{{ delivery.supplier.name }}</p>
                                <p class="font-semibold">{{ delivery.supplier.contact_person }}</p>
                                <p class="text-gray-500">{{ delivery.supplier.address }}</p>
                                <p class="text-gray-500">{{ delivery.supplier.email }}</p>
                                <p class="text-gray-500">{{ delivery.supplier.phone }}</p>
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
                                    <tr v-for="(purchase, index) in delivery_items" :key="purchase.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>
                                            <div class="flex gap-2 grow flex-col sm:flex-row">
                                                <img v-if="purchase.image" class="h-12 w-12 shrink-0 rounded-btn" width="56" height="56" :src="purchase.image" alt="Product">
                                                <div class="flex flex-col gap-1">
                                                    <div class="text-sm">
                                                        {{ purchase.name }}
                                                    </div>
                                                    {{ purchase.size }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="pl-5 pt-2">{{ purchase.qty }}</span>
                                        </td>
                                        <td>
                                            <span class="ml-1 pt-2">{{ purchase.price }}</span>
                                        </td>
                                        <td>
                                            <span class="mr-3">
                                                {{ purchase.total }}</span>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <div class="flex gap-4 page-break justify-between mt-5 flex-col-reverse sm:flex-row">
                            <div class="w-full sm:w-1/2">
                                <InputLabel class="label" value="Notes" />
                                <textarea v-model="delivery.notes" class="textarea textarea-bordered w-full max-w-md" placeholder="Type here" ></textarea>
                            </div>
                            <div class="w-full sm:w-1/2 flex justify-end">
                                <div class="bg-base-200 w-full rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                    <div class="flex justify-between mb-2">
                                        <span>Items:</span>
                                        <span>{{ delivery.quantity }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <span>Subtotal:</span>
                                        <span>{{ delivery.amount }}</span>
                                    </div>
                                    <div class="flex justify-between mb-2">
                                        <button class="font-semibold" type="button" @click="addDiscountModal = true">
                                            Discount(+/-):
                                                </button>
                                        <span class="text-red-500">{{ delivery.discount }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>Total:</span>
                                        <span>{{ delivery.total }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 flex-col lg:flex-row mt-10">
                        <div class="flex justify-end gap-3 flex-col md:flex-row">
                            <CancelButton href="/deliveries" />
                            <DownloadButton :href="route('delivery.downloadPDF', delivery.id)"> Download PDF </DownloadButton>
                            <PrintButton :id="'purchaseContainer'" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
@media print {
  .page-break {
    page-break-after: always;
  }
}
</style>
