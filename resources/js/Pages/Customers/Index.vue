<script setup>
import { ref,computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	customers: Object,
    stores: Object,
	filter: Object
});

let search = ref(props.filter.search);
const url = '/customers';

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let customerIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({customers_id: ''});

const deleteCustomerForm = (user_id) => {
	deleteModal.value = true;
	deleteForm.id = user_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const submitDeleteForm = () => {
	deleteForm.delete(`/customers/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().error('Customer has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.customers_id = customerIds.value

    deleteSelectedForm.post(route('customers.bulkDelete'),{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            customerIds.value = [];
            closeModal();
            useToast().error('Selected customers has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false
        },
    })
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        customerIds.value = props.customers.data.map(customer => customer.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        customerIds.value = false;
      }
}

const customersDataLength = computed(() => {
    if(route().params) {
        return props.customers.data.length + 1
    }
    return props.customers.data.length
})

const appliedFilters = [
    { title: 'search', value: search },
]
const clearFilters = (filter) => {
    if (filter.title == 'category') {
        category.value = '';
    } else if (filter.title == 'search') {
        search.value = '';
    } else if (filter.title == 'usage type') {
        type.value = '';
    }
}
</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <div v-if="customersDataLength !== 0" class="flex items-center gap-2">
            <CreateBtnLink href="customers/create" >New supplier</CreateBtnLink>
            <ActionDropdown
                :dataIds="customerIds"
                :exportPDFRoute="false"
                :exportExcelRoute="false"
                :withImportBtn="false"
                @open-import-modal="importModal = false"
                @delete-all-selected="deleteAllSelectedModal = true"/>
        </div>
    </TitleContainer>

    <EmptyContainer :title="title" v-if="customersDataLength == 0">
        <CreateBtnLink href="/customers/create">New product</CreateBtnLink>
    </EmptyContainer>

    <div class="flex-grow" v-if="customersDataLength > 0">
        <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
            <div class="card-body grow-0">
                <div class="flex justify-end gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <div class="w-full">
                            <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
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
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Phone</TableHead>
                            <TableHead>Address</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="customer in customers.data" :key="customer.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="customer.id" v-model="customerIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <Link :href=" `/customers/${customer.id}`" class="text-blue-800">
                                    <div class="flex items-center gap-2">
                                        <Avatar :src="customer.logo" />
                                        <div class="flex flex-col font-semibold">
                                            {{ customer.name }}
                                            <p class="text-xs opacity-50">
                                                {{ customer.email }}
                                            </p>
                                        </div>
                                    </div>
                                </Link>
                                
                            </TableCell>
                            <TableCell>{{ customer.phone }}</TableCell>
                            <TableCell>{{ customer.address }}</TableCell>
                            <TableCell class="flex gap-2">
                                <div class="flex items-center gap-2">
                                    <EditIconBtnLink  :href="`/customers/${customer.id}/edit` "/>
                                    <DeleteIcon @modal-show="deleteCustomerForm(customer.id)"/>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="customers.data == 0">
                            <TableCell :colspan="4" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="customers" />
            <PaginationControlList :url="url" />
            <Pagination :links="customers.links" />
        </div>
    </div>
    
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete customer
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
                Delete {{ customerIds.length }} customers
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
