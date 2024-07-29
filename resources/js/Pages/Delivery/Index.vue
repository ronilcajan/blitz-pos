<script setup>
import { reactive, ref, watch,computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	deliveries: Object,
    stores: Object,
    suppliers: Object,
	filter: Object
});

const page = usePage();
let search = ref(props.filter.search);
let supplier = ref('');
let type = ref('');
const url = '/deliveries';

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let deliveryIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({delivery_id: ''});

const deleteDeliveryForm = (delivery_id) => {
	deleteModal.value = true;
	deleteForm.id = delivery_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const date_range = reactive({
    from_date: '',
    to_date: ''
});

const submitDeleteForm = () => {
	deleteForm.delete(`/deliveries/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Product has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['deliveries'],
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.delivery_id =  deliveryIds.value
    deleteSelectedForm.post(route('delivery.bulkDelete'),{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            deliveryIds.value = [];
            closeModal();
            useToast().error('Selected deliveries has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        deliveryIds.value = props.deliveries.data.map(delivery => delivery.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        deliveryIds.value = [];
      }
}

const deliveryDataLength = computed(() => {
    if (Object.keys(route().params).length > 0) {
        return props.deliveries.data.length + 1;
    }
    
    return props.deliveries.data.length;
})

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
        <div v-if="deliveryDataLength !== 0" class="flex items-center gap-2 flex-wrap justify-end">
            <CreateBtnLink href="deliveries/create" >New delivery</CreateBtnLink>
            <FilterDate :dateRange="date_range" :url="url"/>
            <ActionDropdown
                :dataIds="deliveryIds"
                :exportPDFRoute="false"
                :exportExcelRoute="false"
                :withImportBtn="false"
                @open-import-modal="importModal = false"
                @delete-all-selected="deleteAllSelectedModal = true"/>
        </div>
    </TitleContainer>

    <EmptyContainer :title="title" v-if="deliveryDataLength === 0">
        <CreateBtnLink href="deliveries/create">New delivery</CreateBtnLink>
    </EmptyContainer> 

    <div v-if="deliveryDataLength > 0" class="flex-grow">

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
                            <TableHead>Purchase</TableHead>
                            <TableHead>Supplier</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="delivery in deliveries.data" :key="delivery.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="delivery.id" v-model="deliveryIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <Link 
                                    :href="`/deliveries/${delivery.id}`" 
                                    class=" text-blue-700">
                                    <div class="flex flex-col font-semibold">
                                        {{ delivery.tx_no }}
                                        <p class="text-xs opacity-50">
                                            {{ delivery.created_at }}</p>
                                    </div>
                                </Link>
                            </TableCell>
                            <TableCell>{{ $page.props.auth.user.currency }} {{ delivery.amount }}</TableCell>
                            <TableCell>{{ delivery.quantity }} Item/s</TableCell>
                            <TableCell>
                                <div class="badge gap-2 badge-warning"
                                    v-if="delivery.status === 'pending' || delivery.status === 'partial'">
                                    {{ delivery.status }}
                                    </div>
                                    <div class="badge gap-2 badge-success" v-if="delivery.status === 'completed' ||
                                    delivery.status === 'full'">
                                    {{ delivery.status }}
                                    </div>
                                    <div class="badge gap-2 badge-error" v-if="delivery.status === 'cancelled'">
                                    {{ delivery.status }}
                                </div>
                            </TableCell>
                            <TableCell>{{ delivery.purchase }}</TableCell>
                            <TableCell>{{ delivery.supplier }}</TableCell>

                            <TableCell>
                                <div class="flex items-center space-x-2 justify-center">

                                    <Link v-if="delivery.status !== 'completed' && delivery.status !== 'partial' && delivery.status !== 'full'" :href="`/deliveries/${delivery.id}/edit`"
                                    class="hover:text-green-500">
                                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
                                        </svg>
                                    </Link>

                                    <a :href="route('delivery.downloadPDF', delivery.id)"
                                    class="hover:text-primary" target="_blank">
                                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"/>
                                        </svg>
                                    </a>
                                    <DeleteIcon @modal-show="deleteDeliveryForm(delivery.id)"/>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="deliveries.data == 0">
                            <TableCell :colspan="10" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>

        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="deliveries" />
            <PaginationControlList :url="url" />
            <Pagination :links="deliveries.links" />
        </div>

    </div>

   
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete delivery
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
                Delete {{ deliveryIds.length }} products
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
