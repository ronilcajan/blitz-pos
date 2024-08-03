<script setup>
import { ref, watch, reactive,computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	orders: Object,
	filter: Object,
    suppliers: Object
});

let search = ref(props.filter.search);
const url = '/purchase';
let supplier = ref('');

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let orderIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({orders_id: ''});

const deletePurchaseForm = (purchase_id) => {
	deleteModal.value = true;
	deleteForm.id = purchase_id
}

const date_range = reactive({
    from_date: '',
    to_date: ''
});

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const submitDeleteForm = () => {
	deleteForm.delete(`/purchase/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Purchase order has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.orders_id = orderIds.value
    deleteSelectedForm.post(route('purchase.bulkDelete'),{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            orderIds.value = [];
            closeModal();
            useToast().danger('Selected orders has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false
        },
        onError: (errors) => {
            closeModal();
            useToast().danger(`Error! ${errors.error}`, {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        }
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        orderIds.value = props.orders.data.map(order => order.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        orderIds.value = [];
      }
}


const purchaseDataLength = computed(() => {
    if (Object.keys(route().params).length > 0) {
        return props.orders.data.length + 1;
    }
    
    return props.orders.data.length;
})

console.log(route().params);

const appliedFilters = [
    { title: 'suppliers', value: supplier },
    { title: 'search', value: search },
]

const clearFilters = (filter) => {
    if (filter.title == 'suppliers') {
        supplier.value = '';
    } else if (filter.title == 'search') {
        search.value = '';
    } 
}

</script>

<template>
    <Head :title="title" />
    <TitleContainer :title="title">
        <div v-if="purchaseDataLength !== 0" class="flex items-center gap-2 flex-wrap justify-end">
            <CreateBtnLink href="purchase/create" >New purchase</CreateBtnLink>
            <FilterDate :dateRange="date_range" :url="url"/>
            <ActionDropdown
                :dataIds="orderIds"
                :exportPDFRoute="false"
                :exportExcelRoute="false"
                :withImportBtn="false"
                @open-import-modal="importModal = false"
                @delete-all-selected="deleteAllSelectedModal = true"/>
        </div>
    </TitleContainer>
    <EmptyContainer :title="title" v-if="purchaseDataLength === 0">
        <CreateBtnLink href="purchase/create">New purchase</CreateBtnLink>
    </EmptyContainer> 

    <div class="flex-grow" v-if="purchaseDataLength > 0">
        <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        
        
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                <div class="flex gap-2 flex-col sm:flex-row">

                    <SelectDropdownFilter v-if="suppliers.length" v-model="supplier" :url="url" :title="`suppliers`"
                        :options="suppliers" />

                </div>
                <div class="flex gap-2 flex-col sm:flex-row">
                    <div class="w-full">
                        <SearchInput v-model="search" :url="url" />
                    </div>
                </div>
            </div>
            <ClearFilters :filters="appliedFilters" @clear-filters="clearFilters" />
        </div>
        <Table>
                <template #table-header>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="$page.props.auth.user.canDelete">
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox"
                                    class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Transaction</TableHead>
                            <TableHead>Amount</TableHead>
                            <TableHead>Quantity</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Supplier</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="order in orders.data" :key="order.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="order.id" v-model="orderIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <Link 
                                    :href="`/purchase/${order.id}`" 
                                    class=" text-blue-700">
                                    <div class="flex flex-col font-semibold">
                                        {{ order.order_no }}
                                        <p class="text-xs opacity-50">
                                            {{ order.created_at }}</p>
                                    </div>
                                </Link>
                            </TableCell>
                            <TableCell>{{ $page.props.auth.user.currency }}  {{ order.amount }}</TableCell>
                            <TableCell>{{ order.quantity }} Item/s</TableCell>
                            <TableCell>
                                <div class="badge gap-2 badge-warning badge-sm" v-if="order.status === 'pending'">
                                {{ order.status }}
                                </div>
                                <div class="badge gap-2 badge-success badge-sm" v-if="order.status === 'completed'">
                                {{ order.status }}
                                </div>
                                <div class="badge gap-2 badge-error badge-sm" v-if="order.status === 'cancelled'">
                                {{ order.status }}
                                </div>
                            </TableCell>
                            <TableCell>{{ order.supplier }}</TableCell>

                            <TableCell>
                                <div class="flex items-center space-x-2 justify-center">
                                    <Link v-if="order.status !== 'completed'" :href="`/purchase/${order.id}/edit`"
                                    class="hover:text-green-500">
                                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                        </svg>
                                    </Link>
                                    <a :href="route('purchase.downloadPDF', order.id)"
                                    class="hover:text-primary" target=_blank>
                                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"/>
                                        </svg>
                                    </a>
                                    <DeleteIcon @modal-show="deletePurchaseForm(order.id)"/>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="orders.data == 0">
                            <TableCell :colspan="8" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>

        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="orders" />
            <PaginationControlList :url="url" />
            <Pagination :links="orders.links" />
        </div>
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete purchase order
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
                Delete {{ orderIds.length }} purchases
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
