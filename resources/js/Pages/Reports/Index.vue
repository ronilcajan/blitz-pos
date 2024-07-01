<script setup>
import { computed, reactive, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    // sales: Object,
    // dailySalesTotal: String,
    // weeklySalesTotal: String,
    // monthlySalesTotal: String,
    // yearlySalesTotal: String,
    // customers: Object,
    // stores: Object,
	// filters: Object
});

</script>

<template>
    <Head :title="title" />
    <div class="flex justify-end items-center mb-5 gap-3 flex-wrap">
        <ActionGroupDropdown
            :dataIds="salesId"
            :exportExcelRoute="route('sales.export_excel', { from_date: date_range.from_date, to_date: date_range.to_date})"
            :exportPDFRoute="route('sales.export_pdf', { from_date: date_range.from_date, to_date: date_range.to_date})"
            @delete-all-selected="deleteAllSelectedModal = true"
        />

        <FilterDate :dateRange="date_range"
        />
    </div>
    <section class="stats stats-vertical col-span-12 mb-5 w-full shadow-sm xl:stats-horizontal">
		<StatsCard title="Daily Sales" :sales="dailySalesTotal">
            Track your daily sales performance
        </StatsCard>
		<StatsCard title="Weekly Sales" :sales="weeklySalesTotal">
            Track your weekly sales performance
        </StatsCard>
		<StatsCard title="Monthly Sales" :sales="monthlySalesTotal">
            Track your monthly sales performance
        </StatsCard>
		<StatsCard title="Yearly Sales" :sales="yearlySalesTotal">
            Track your yearly sales performance
        </StatsCard>
	</section>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                <div class="flex gap-2 flex-col sm:flex-row">
                    <FilterByStoreDropdown v-model="store" :stores="stores" :url="url"/>
                    <div class="w-full">
                        <select v-model="customer" class="select select-bordered select-sm w-full">
                            <option selected value="">Filter by suppliers</option>
                            <option v-for="customer in customers" :value="customer.name" :key="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 flex-col sm:flex-row">
                    <div class="w-full">
                        <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th>
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th>
                            <div class="font-bold">Transaction</div>
                        </th>
                        <th>
                            <div class="font-bold">Payment Method</div>
                        </th>
                        <th>
                            <div class="font-bold">Items</div>
                        </th>
                        <th>
                            <div class="font-bold">Discount</div>
                        </th>
                        <th>
                            <div class="font-bold">Amount</div>
                        </th>
                        <th>
                            <div class="font-bold">Status</div>
                        </th>
                        <th>
                            <div class="font-bold">Customer</div>
                        </th>
                        <th>
                            <div class="font-bold">User</div>
                        </th>
                        <th class="sm:table-cell" v-if="isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales.data" :key="sale.id">
                        <td class="w-0">
                            <input :value="sale.id" v-model="salesId" type="checkbox" class="checkbox checkbox-sm">
                        </td>
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
                        <td class="sm:table-cell">{{ sale.payment_method }}</td>
                        <td class="sm:table-cell">{{ sale.quantity }}</td>
                        <td class="sm:table-cell">{{ sale.discount }}</td>
                        <td class="sm:table-cell">{{ sale.total }}</td>
                        <td class="sm:table-cell">
                            <select @change="statusChange(sale.id, $event.target.value)" class="select select-xs"  :class="`text-${sale.statusColor}`">
                                <option :selected="sale.status === 'complete'">complete</option>
                                <option class="" :selected="sale.status === 'void'">void</option>
                            </select>
                        </td>
                        <td class="sm:table-cell">{{ sale.customer }}</td>
                        <td class="sm:table-cell">{{ sale.user }}</td>
                        <td class="sm:table-cell">{{ sale.store }}</td>
                        <td>
                            <div class="flex items-center space-x-2">
                                <InvoiceButton :href="route('sales.downloadSalesInvoice', sale.id)" />
                                <ReceiptButton :href="`/sales/${sale.id}`" />
                                <DeleteIcon @modal-show="deleteSalesForm(sale.id)" />
                            </div>
                        </td>

                    </tr>
                    <tr v-if="sales.data.length <= 0">
                        <td colspan="8" class="text-center">
                            No data found
                        </td>

                    </tr>
                </tbody>
            </table>

        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="sales" />
        <PaginationControlList :url="url" />
        <Pagination :links="sales.links" />
    </div>
</template>
