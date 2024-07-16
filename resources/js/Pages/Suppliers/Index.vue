<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	suppliers: Object,
	filter: Object
});

let search = ref(props.filter.search);
const url = '/suppliers';

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let supplierIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});

const deleteSupplierForm = (user_id) => {
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
	deleteForm.delete(`/suppliers/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Supplier has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('suppliers.bulkDelete'),
    {
        suppliers_id: supplierIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            supplierIds.value = [];
            closeModal();
            useToast().success('Multiple suppliers has been deleted successfully!', {
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
        supplierIds.value = props.suppliers.data.map(supplier => supplier.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        supplierIds.value = [];
      }
}

const activeDropdown = ref(null);
const toggleDropdown = (supplierId) => { 
      if (activeDropdown.value === supplierId) {
        activeDropdown.value = null;
      } else {
        activeDropdown.value = supplierId;
      }
}

</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <CreateButtonLink v-if="suppliers.data.length > 0" href="/suppliers/create">New supplier</CreateButtonLink>
    </TitleContainer>

    <div class="flex-grow p-2 lg:gap-x-12 lg:p-4 lg:pt-2 flex flex-col justify-center items-center border-dashed border-2" v-if="suppliers.data.length == 0">
        <div class="flex flex-col gap-3 justify-center items-center w-2/3">
            <p class="text-lg font-semibold">
                No {{ title.toLocaleLowerCase() }} found!
            </p>
            <p class="text-center text-muted text-sm mb-5">
                Your {{ title.toLocaleLowerCase() }} will be displayed here.</p>

            <CreateButtonLink href="/suppliers/create">New supplier</CreateButtonLink>

        </div>
    </div>

    <div class="flex-grow" v-if="suppliers.data.length > 0">
        <section class="col-span-12 bg-base-100 shadow-sm rounded">
            <div class="card-body grow-0">
                <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2">
                        <DeleteButton v-if="$page.props.auth.user.canDelete" v-show="supplierIds.length > 0" @click="deleteAllSelectedModal = true">
                            Delete
                        </DeleteButton>
                    </div>
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <div class="w-full">
                            <SearchInput v-model="search" @clear-search="search = ''" :url="url"/>
                        </div>
                    </div>
                </div>

              
            </div>

            <Table >
                <template #table-header>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="$page.props.auth.user.canDelete">
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Contact Person</TableHead>
                            <TableHead>Phone</TableHead>
                            <TableHead>Address</TableHead>
                            <TableHead>Created on</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="supplier in suppliers.data" :key="supplier.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="supplier.id" v-model="supplierIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <Avatar :src="supplier.logo" />
                                    {{ supplier.name }}
                                </div>
                            </TableCell>
                            <TableCell>{{ supplier.contact_person }}</TableCell>
                            <TableCell>{{ supplier.phone }}</TableCell>
                            <TableCell>{{ supplier.address }}</TableCell>
                            <TableCell>{{ supplier.created_at }}</TableCell>
                            <TableCell class="flex gap-2">
                                <EditIconBtn :href="`/suppliers/${supplier.id}/edit`"/>
                                <DeleteIcon @modal-show="deleteSupplierForm(supplier.id)"/>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
           
        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="suppliers" />
            <PaginationControlList :url="url" />
            <Pagination :links="suppliers.links" />
        </div>
    </div>

    
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete supplier
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
                Delete {{ supplierIds.length }} suppliers
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': submitBulkDeleteForm.processing }"
                        :disabled="submitBulkDeleteForm.processing"
                    >
                        <span v-if="submitBulkDeleteForm.processing" class="loading loading-spinner"></span>
                        Delete
                    </DangerButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
