<script setup>
import { router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    products: String,
    delivery: String,
    expenses: String,
    sales: String,
    sales_recent: String,
    sales_current_year: Object,
    sales_previous_year: Object,
    expenses_data_chart: Object,
    stock_alert: Object
})

const bar_data =  {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
  datasets: [
    {
      label: 'Current',
      backgroundColor: '#2B49FF',
      data: props.sales_current_year
    },
    {
      label: 'Previous Year',
      backgroundColor: '#292D34',
      data: props.sales_previous_year
    }
  ],
  chartOptions: {
        responsive: true
      }
}

const doughnut_data = {
  labels: ['Approved', 'Pending','Rejected'],
  datasets: [
    {
      backgroundColor: ['#41B883', '#E46651', '#DD1B16'],
      data: props.expenses_data_chart
    }
  ]
}

// onMounted(() => {
//   setInterval(() => {
//     router.reload({ only: ['sales_current_year', 'sales_previous_year'] })
//   }, 3000)
// })
</script>

<template>
    <Head title="Dashboard" />
    <div class="flex justify-end">
        <div class="dropdown dropdown-bottom dropdown-end">
            <button class="btn btn-xs btn-primary mb-4">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-month"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg>
                Last 30 days
            </button>
            <div tabindex="0" class="dropdown-content z-[9] card card-compact w-44 p-1 shadow-lg mt-1 bg-base-100">
                <div class="card-body">
                    <div class="flex">
                        <p class="font-bold">Select Date Range</p>
                    </div>
                    <div>
                        <InputLabel>From date</InputLabel>
                    </div>
                    <div>
                        <InputLabel>To date</InputLabel>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <!-- stats -->
	<section class="stats stats-vertical col-span-12 w-full mb-5 shadow-sm xl:stats-horizontal">
		<div class="stat flex flex-col gap-3">
			<div class="stat-title uppercase text-sm">Total Products</div>
			<div class="stat-value flex items-center gap-2">
                <span class="p-2 bg-primary rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-box text-white"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                </span>

                {{ products }}
            </div>
			<!-- <div class="stat-desc">21% more than last month</div> -->
		</div>

		<div class="stat">
			<div class="stat-title uppercase text-sm">Total Deliveries</div>
            <div class="stat-value flex items-center gap-2">
                <span class="p-2 bg-orange-500 rounded-full">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-truck-delivery text-white"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M5 17h-2v-4m-1 -8h11v12m-4 0h6m4 0h2v-6h-8m0 -5h5l3 5" /><path d="M3 9l4 0" /></svg>
                </span>
                {{ delivery }}
            </div>
		</div>
        <div class="stat">
			<div class="stat-title uppercase text-sm">Total Expenses</div>
            <div class="stat-value flex items-center gap-2">
                <span class="p-2 bg-yellow-300 rounded-full">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-wallet text-white"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                </span>
                {{ expenses }}
            </div>
		</div>

        <div class="stat">
			<div class="stat-title uppercase text-sm">Total Sales</div>
            <div class="stat-value flex items-center gap-2">
                <span class="p-2 bg-green-500 rounded-full">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-coins text-white"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14c0 1.657 2.686 3 6 3s6 -1.343 6 -3s-2.686 -3 -6 -3s-6 1.343 -6 3z" /><path d="M9 14v4c0 1.656 2.686 3 6 3s6 -1.344 6 -3v-4" /><path d="M3 6c0 1.072 1.144 2.062 3 2.598s4.144 .536 6 0c1.856 -.536 3 -1.526 3 -2.598c0 -1.072 -1.144 -2.062 -3 -2.598s-4.144 -.536 -6 0c-1.856 .536 -3 1.526 -3 2.598z" /><path d="M3 6v10c0 .888 .772 1.45 2 2" /><path d="M3 11c0 .888 .772 1.45 2 2" /></svg>
                </span>
                {{ sales }}
            </div>
		</div>
	</section>
	<!-- /stats -->
	<!-- card -->

    <div class="flex gap-4 md:flex-row flex-col">
        <div class="w-full md:w-2/3">
            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="font-bold">Sales Overview</h2>
                    <p class="text-xs">This chart represents the total sales from January to December for the current year and previous year.</p>
                    <Bar :data="bar_data" />
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="font-bold">Expenses Breakdown</h2>
                    <p class="text-xs">This chart represents the total expenses categorized by their status: approved, pending, and rejected.</p>
                    <Doughnut :data="doughnut_data" />
                </div>
            </div>
        </div>
    </div>

    <div class="flex gap-4 md:flex-row flex-col">
        <div class="w-full md:w-2/3">
            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="font-bold">Recent Sales</h2>
                    <p class="text-xs">Most recent sales transactions, providing insights into recent business activities.</p>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead class="uppercase">
                                <tr>
                                    <th>
                                        <div class="font-bold">Transaction</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">Amount</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">Payment Method</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">Items</div>
                                    </th>

                                    <th>
                                        <div class="font-bold">Status</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">Customer</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sale in sales_recent" :key="sale.id">
                                    <td>
                                        <div class="text-sm font-bold">
                                            {{ sale.tx_no }}
                                        </div>
                                        <div>
                                            <div class="text-xs opacity-50">
                                                {{ sale.created_at }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="sm:table-cell">{{ sale.total }}</td>
                                    <td class="sm:table-cell">{{ sale.payment_method }}</td>
                                    <td class="sm:table-cell">{{ sale.quantity }}</td>
                                    <td class="sm:table-cell">
                                        <div class="badge" :class="sale.status == 'complete' ? 'bg-green-600 text-white' : 'bg-orange-600 text-white'">{{ sale.status }}</div>
                                    </td>
                                    <td class="sm:table-cell">{{ sale.customer }}</td>
                                    <td>
                                        <div class="flex items-center space-x-2">
                                            <!-- <InvoiceButton :href="route('sales.downloadSalesInvoice', sale.id)" />
                                            <ReceiptButton :href="`/sales/${sale.id}`" />
                                            <DeleteIcon @modal-show="deleteSalesForm(sale.id)" /> -->
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="font-bold">Stock Alert</h2>
                    <p class="text-xs">Lists of products that have low stock levels compared to their minimum required quantity, indicating the need for restocking.</p>

                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead class="uppercase">
                                <tr>
                                    <th>
                                        <div class="font-bold">Products</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">In Warehouse</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">In Store</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in stock_alert" :key="item.id">
                                    <td class="sm:table-cell">{{ item.name }}</td>
                                    <td class="sm:table-cell">{{ item.stock.in_warehouse }}</td>
                                    <td class="sm:table-cell">{{ item.stock.in_store }}</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
