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

const url = '/suppliers';
let search = ref(props.filter.search);
const supplierDataLength = props.suppliers.data.length;

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const importModal = ref(false);

let supplierIds = ref([]);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({suppliers_id: []});
const importForm = useForm({import_file: ''});

const deleteSupplierForm = (user_id) => {
	deleteModal.value = true;
	deleteForm.id = user_id
}

const submitDeleteForm = () => {
	deleteForm.delete(`/suppliers/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			deleteForm.clearErrors()
            deleteModal.value = false;
            deleteForm.reset();
			useToast().error('Supplier has been deleted!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.suppliers_id = supplierIds.value
    deleteSelectedForm.post(route('suppliers.bulkDelete'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            supplierIds.value = [];
            deleteAllSelectedModal.value = false;
            useToast().error('Selected suppliers has been deleted!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
            deleteSelectedForm.reset();
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

const submitImportProducts = () => {
    importForm.post(route('suppliers.import'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            importModal.value=false
            useToast().success('Suppliers has been imported successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            router.reload({only: ['suppliers']})
        },
        onError: () => {
            useToast().error(importForm.errors.error, {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        }
    })
}

</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <div v-if="supplierDataLength !== 0">
            <CreateBtn href="suppliers/create" >New supplier</CreateBtn>
            <ActionDropdown
                :dataIds="supplierIds"
                :exportPDFRoute="route('suppliers.export_pdf')"
                :exportExcelRoute="route('suppliers.export_excel')"
                :withImportBtn="true"
                @open-import-modal="importModal = true"
                @delete-all-selected="deleteAllSelectedModal = true"/>
        </div>
    </TitleContainer>

    <EmptyContainer :title="title" v-if="supplierDataLength === 0">
       

        <CreateBtn href="suppliers/create">New supplier</CreateBtn>
    </EmptyContainer> 

    <div class="flex-grow" v-if="supplierDataLength > 0">
        <section class="col-span-12 overflow-hidden  bg-base-100 shadow-sm rounded">
            <div class="card-body grow-0">
                <div class="flex justify-end  gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <div class="w-full">
                            <SearchInput v-model="search"  @clear-search="search = ''" :url="url"/>
                        </div>
                    </div>
                </div>
            </div>

            <Table>
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
                            <TableCell class="flex gap-2">
                                <div class="flex items-center gap-2">
                                    <EditIconBtn :href="`/suppliers/${supplier.id}/edit`"/>
                                    <DeleteIcon @modal-show="deleteSupplierForm(supplier.id)"/>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="suppliers.data == 0">
                            <TableCell :colspan="5" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
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
    <Modal :show="deleteModal" @close="deleteModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete supplier
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteModal = false">Cancel</SecondaryButton>
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
    <Modal :show="deleteAllSelectedModal" @close="deleteAllSelectedModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ supplierIds.length }} suppliers
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteAllSelectedModal = false">Cancel</SecondaryButton>
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

    <Modal :show="importModal" @close="importModal = false" maxWidth="md">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Import Suppliers
            </h1>
            <p>Please upload your suppliers data using an Excel file.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitImportProducts">

                <div class="mb-3">
                    <InputLabel value="Select an Excel file" />
                    <input type="file" class="file-input file-input-bordered file-input-sm w-full" @input="importForm.import_file = $event.target.files[0]" accept=".xlsx, .xls"
                    required />
                    <progress class="progress" v-if="importForm.progress" :value="importForm.progress.percentage" max="100">
                    {{ importForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="importForm.errors.import_file" />

                    <div>
                        <small>Note: Before uploading, make sure you use this <a :href="route('suppliers.donwloadTemplate')" class="text-primary">template</a>.</small>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="importModal = false">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': importForm.processing }"
                        :disabled="importForm.processing"
                    >
                        <span v-if="importForm.processing" class="loading loading-spinner"></span>
                        Import Now
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
