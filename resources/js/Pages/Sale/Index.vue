<script setup>
import { computed, reactive, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import StatsCard from './partials/StatsCard.vue';
import InvoiceButton from './partials/InvoiceButton.vue';
import ReceiptButton from './partials/ReceiptButton.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    sales: Object,
    dailySalesTotal: String,
    weeklySalesTotal: String,
    monthlySalesTotal: String,
    yearlySalesTotal: String,
    customers: Object,
    stores: Object,
	filters: Object
});

const page = usePage();

let customer = ref('');
let search = ref(props.filters.search);
let store = ref('');
const url = '/sales';

const editModal = ref(false);
const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let salesId = ref([]);
let selectAllCheckbox = ref(false);
const date_range = reactive({
    from_date: '',
    to_date: ''
});

const editForm = useForm({id: '', name: ''});
const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({sales_id: ''});

const deleteSalesForm = (sale_id) => {
	deleteModal.value = true;
	deleteForm.id = sale_id
}

const closeModal = () => {
	deleteForm.clearErrors()
	editForm.clearErrors()
    deleteModal.value = false;
    editModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
    editForm.reset();
};

const isSuperAdmin = computed(() =>
    page.props.auth.user.isSuperAdmin ? true : false
)
const canDelete = page.props.auth.user.canDelete

const submitDeleteForm = () => {
	deleteForm.delete(`/sales/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Sale information has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.sales_id = salesId.value
    deleteSelectedForm.post(route('sales.bulkDelete'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            salesId.value = [];
            closeModal();
            useToast().success('Multiple sales has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = 0;
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        salesId.value = props.sales.data.map(unit => unit.id);
    } else {
        // If "Select All" checkbox is unchecked, deselect all users
        salesId.value = [];
    }
}

const statusChange = (saleID, selectedStatus) => {
    router.post(route('sales.updateStatus'), { id: saleID, status: selectedStatus },
	{
        preserveState: true,
        replace:true,
        preserveScroll: true,
        onSuccess: () => {
			closeModal();
			useToast().success('Sale status been updated successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        // only: ['sales','userSummary']
    })
}

watch(customer, value => {
	router.get('/sales',
	{ customer: value },
	{ preserveState: true, replace:true })
})

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
        <FilterDate :dateRange="date_range" :url="url"/>
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
    <Modal :show="editModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Edit product units
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitUpdateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Product unit" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="editForm.name"
                        required
                        placeholder="product unit"
                    />
                    <InputError class="mt-2" :message="editForm.errors.name" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <SuccessButton
                        class="ms-3"
                        :class="{ 'opacity-25': editForm.processing }"
                        :disabled="editForm.processing"
                    >
                        <span v-if="editForm.processing" class="loading loading-spinner"></span>
                        Update
                    </SuccessButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete sales
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing"
                    >
                        <span v-if="deleteForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- delete all selected modal -->
    <Modal :show="deleteAllSelectedModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ salesId.length }} sales information
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deleteSelectedForm.processing }"
                        :disabled="deleteSelectedForm.processing"
                    >
                        <span v-if="deleteSelectedForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
