<script setup>
import { ref,computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	customer: Object,
    sales: Object,
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
    const total = props.sales.data.reduce((qty, order) => qty + parseFloat(order.quantity), 0);
    return formatNumberWithCommas(total);
})

const totalSales = computed(() => {
    const total = props.sales.data.reduce((amount, order) => amount + parseFloat(order.total), 0).toFixed(2);
    return formatNumberWithCommas(total);
})

console.log(props.sales);
</script>
<template>
     <Head :title="title" />
     <TitleContainer :title="title">
        <EditIconBtnLink class="flex items-center gap-1 btn btn-sm btn-success" 
        :href="`/customers/${customer.id}/edit`" :title="`Edit ${customer.name}`">
        <span>Edit</span>
        </EditIconBtnLink>
    </TitleContainer>

    <div class="flex-grow">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="card bg-base-100 w-full sm:w-1/3 shadow">
                <div class="card-body">
                    <div class="flex justify-start" v-if="customer.logo">
                        <img :src="customer.logo" alt="avatar" class="w-full rounded">
                    </div>
                    <h1 class="font-bold text-2xl">{{ customer.name }}</h1>
                    <div class="text-sm">
                        <a :href="`mailto:${customer.email}`" class="flex justify-start items-center gap-2 ">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mail"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg>
                            <span class="text-md">
                                {{ customer.email }}
                            </span>
                        </a>
                       
                    </div>

                    <div class="text-sm">
                        <a :href="`tel:${customer.phone}`" class="flex justify-start items-center gap-2 ">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-phone"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg>

                            <span class="text-md">
                                {{ customer.phone }}
                            </span>
                        </a>
                    </div>
                    
                    <div class="flex justify-start items-center gap-2 text-sm">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>

                        <span class="text-md">
                            {{ customer.address }}
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
                        <div class="stat-title">Total Orders</div>
                        <div class="stat-value">{{ totalOrders ?? 0 }}</div>
                        <!-- <div class="stat-desc">Jan 1st - Feb 1st</div> -->
                    </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="30"  height="30"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-coins"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg>
                    </div>
                    <div class="stat-title">Total Sales</div>
                    <div class="stat-value">
                        {{ $page.props.auth.user.currency }}  {{ totalSales }}</div>
                    <!-- <div class="stat-desc">↗︎ 400 (22%)</div> -->
                </div>

                <div class="stat">
                    <div class="stat-figure text-secondary">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        class="inline-block h-8 w-8 stroke-current">
                        <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    </div>
                    <div class="stat-title">Loyalty Points</div>
                    <div class="stat-value">{{ totalOrders ?? 0 }}</div>
                    <!-- <div class="stat-desc">↘︎ 90 (14%)</div> -->
                </div>
                </div>

                <div role="tablist" class="tabs tabs-bordered mt-3 w-full ">
                    <input type="radio" name="my_tabs_1" role="tab" class="tab font-bold text-primary text-xs" aria-label="ORDERS" checked="checked" />
                    <div role="tabpanel" class="tab-content shadow-sm overflow-hidden ">
                        <div class="card bg-base-100 rounded-none ">
                            <div class="card-body p-6">
                                <div class="text-md font-semibold">
                                    Order History
                                </div>
                               
                            </div>
                        </div>
                        <Table class="bg-base-100">
                            <template #table-header>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Invoice</TableHead>
                                        <TableHead>Qty</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Date</TableHead>
                                    </TableRow>
                                </TableHeader>
                            </template>
                            <template #table-body>
                                <TableBody>
                                    <TableRow v-for="sale in sales.data" :key="sale.id">
                                        <TableCell>
                                            <a class="text-primary font-semibold" :href="`/sales/${sale.id}`">
                                                {{ sale.tx_no }}
                                            </a>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(sale.quantity) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ $page.props.auth.user.currency }}
                                            {{ formatNumberWithCommas(sale.total) }}
                                        </TableCell>
                                        <TableCell>
                                            <div class="badge badge-sm"
                                                :class="sale.status === 'complete' ? 'badge-primary' : 'badge-warning'">
                                                {{ sale.status }}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {{ formatDateForDisplay(sale.created_at) }}
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="sales == 0">
                                        <TableCell :colspan="6" class="text-center">
                                            No sales yet for this customer!
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