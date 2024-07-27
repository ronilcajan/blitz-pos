<script setup>
import { ref,computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	supplier: Object,
    purchases: Object,
    deliveries: Object
});

const formatNumberWithCommas = (number) => {
    if (!number) return
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const formatDateForDisplay = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

const totalOrders = computed(() => {
    const total = props.purchases.reduce((qty, order) => qty + parseFloat(order.quantity), 0);
    return formatNumberWithCommas(total);
})



const totalDeliveries = computed(() => {
    const total = props.deliveries.reduce((amount, order) => amount + parseFloat(order.quantity), 0);
    return formatNumberWithCommas(total);
})

const totalAmountDelivered = computed(() => {
    const total = props.deliveries.reduce((qty, order) => qty + parseFloat(order.total), 0).toFixed(2);
    return formatNumberWithCommas(total);
})

</script>
<template>
     <Head :title="title" />
     <TitleContainer :title="title">
        <EditIconBtnLink class="flex items-center gap-1 btn btn-sm btn-success" 
        :href="`/suppliers/${supplier.id}/edit`" :title="`Edit ${supplier.name}`">
        <span>Edit</span>
        </EditIconBtnLink>
    </TitleContainer>

    <div class="flex-grow">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="card bg-base-100 w-full sm:w-1/3 shadow">
                <div class="card-body">
                    <div class="flex justify-start" v-if="supplier.logo">
                        <img :src="supplier.logo" alt="avatar" class="w-full rounded">
                    </div>
                    <h1 class="font-bold text-2xl">{{ supplier.name }}</h1>

                    <div class="text-sm flex justify-start items-center gap-2 ">

                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-user-square"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 10a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M6 21v-1a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v1" /><path d="M3 5a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v14a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-14z" /></svg>
                            <span class="text-md">
                                {{ supplier.contact_person }}
                            </span>
                       
                    </div>

                    <div class="text-sm">
                        <a :href="`mailto:${supplier.email}`" class="flex justify-start items-center gap-2 ">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mail"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                            <span class="text-md">
                                {{ supplier.email }}
                            </span>
                        </a>
                       
                    </div>

                    <div class="text-sm">
                        <a :href="`tel:${supplier.phone}`" class="flex justify-start items-center gap-2 ">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-phone"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>

                            <span class="text-md">
                                {{ supplier.phone }}
                            </span>
                        </a>
                    </div>
                    
                    <div class="flex justify-start items-center gap-2 text-sm">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>

                        <span class="text-md">
                            {{ supplier.address }}
                        </span>
                    </div>

                </div>

            </div>

            <div class="w-full sm:w-2/3">
                <div class="stats stats-vertical lg:stats-horizontal shadow-sm w-full">
                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="30"  height="30"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17h-11v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /></svg>
                        </div>
                        <div class="stat-title">Total Purchases</div>
                        <div class="stat-value">{{ totalOrders ?? 0 }}</div>
                        <!-- <div class="stat-desc">Jan 1st - Feb 1st</div> -->
                    </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="30"  height="30"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-truck-delivery"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" /><path d="M3 9l4 0" /></svg>
                    </div>
                    <div class="stat-title">Total Deliveries</div>
                    <div class="stat-value">
                        {{ totalDeliveries ?? 0.00 }}</div>
                    <!-- <div class="stat-desc">↗︎ 400 (22%)</div> -->
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="30"  height="30"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-moneybag"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9.5 3h5a1.5 1.5 0 0 1 1.5 1.5a3.5 3.5 0 0 1 -3.5 3.5h-1a3.5 3.5 0 0 1 -3.5 -3.5a1.5 1.5 0 0 1 1.5 -1.5z" /><path d="M4 17v-1a8 8 0 1 1 16 0v1a4 4 0 0 1 -4 4h-8a4 4 0 0 1 -4 -4z" /></svg>
                    </div>
                    <div class="stat-title">Total Amount</div>
                    <div class="stat-value">{{ totalAmountDelivered ?? 0 }}</div>
                    <!-- <div class="stat-desc">↘︎ 90 (14%)</div> -->
                </div>
                </div>

                <div role="tablist" class="tabs tabs-bordered mt-3 w-full ">
                    <input type="radio" name="my_tabs_1" role="tab" class="tab font-bold text-primary text-xs" aria-label="PURCHASES" checked="checked" />
                    <div role="tabpanel" class="tab-content shadow-sm overflow-hidden ">
                        <div class="card bg-base-100 rounded-none ">
                            <div class="card-body p-6">
                                <div class="text-md font-semibold">
                                    Purchases
                                </div>
                               
                            </div>
                        </div>
                        <Table class="bg-base-100">
                            <template #table-header>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>TX no</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Date</TableHead>
                                    </TableRow>
                                </TableHeader>
                            </template>
                            <template #table-body>
                                <TableBody>
                                    <TableRow v-for="purchase in purchases" :key="purchase.id">
                                        <TableCell>
                                            <a class="text-primary font-semibold" :href="`/sales/${purchase.id}`">
                                                {{ purchase.tx_no }}
                                            </a>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(purchase.quantity) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ $page.props.auth.user.currency }}
                                            {{ formatNumberWithCommas(purchase.total) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="badge badge-sm"
                                                :class="purchase.status === 'complete' ? 'badge-primary' : 'badge-warning'">
                                                {{ purchase.status }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatDateForDisplay(purchase.created_at) }}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="purchases == 0">
                                        <TableCell :colspan="6" class="text-center">
                                            No purchases yet for this supplier!
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </template>
                        </Table>
                    </div>

                    <input type="radio" name="my_tabs_1" role="tab" class="tab font-bold text-primary text-xs" aria-label="DELIVERIES"/>
                    <div role="tabpanel" class="tab-content shadow-sm overflow-hidden ">
                        <div class="card bg-base-100 rounded-none ">
                            <div class="card-body p-6">
                                <div class="text-md font-semibold">
                                    Deliveries
                                </div>
                               
                            </div>
                        </div>
                        <Table class="bg-base-100">
                            <template #table-header>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>TX no</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Date</TableHead>
                                    </TableRow>
                                </TableHeader>
                            </template>
                            <template #table-body>
                                <TableBody>
                                    <TableRow v-for="delivery in deliveries" :key="delivery.id">
                                        <TableCell>
                                            <a class="text-primary font-semibold" :href="`/sales/${delivery.id}`">
                                                {{ delivery.tx_no }}
                                            </a>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(delivery.quantity) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ $page.props.auth.user.currency }}
                                            {{ formatNumberWithCommas(delivery.total) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="badge badge-sm"
                                                :class="delivery.status === 'complete' ? 'badge-primary' : 'badge-warning'">
                                                {{ delivery.status }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatDateForDisplay(delivery.created_at) }}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="deliveries == 0">
                                        <TableCell :colspan="6" class="text-center">
                                            No deliveries yet for this supplier!
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </template>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>