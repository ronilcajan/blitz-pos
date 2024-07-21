<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    units: Object,
	filters: Object
});

let search = ref(props.filters.search);
const url = '/product_units';

const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let unitsId = ref([]);
let selectAllCheckbox = ref(false);

const createForm = useForm({name: ''});

const editForm = useForm({id: '', name: ''});

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({units_id: ''});

const editModalForm = (unit) => {
	editForm.clearErrors()
	editForm.id = unit.id;
    editForm.name = unit.category;
	editModal.value = true;
};


const deleteCatForm = (unit_id) => {
	deleteModal.value = true;
	deleteForm.id = unit_id
}

const closeModal = () => {
    createForm.clearErrors()
	deleteForm.clearErrors()
	editForm.clearErrors()
    createModal.value = false;
    deleteModal.value = false;
    editModal.value = false;
    deleteAllSelectedModal.value = false;
    createForm.reset();
    deleteForm.reset();
    editForm.reset();
};

const submitCreateForm = () => {
	createForm.post('/product_units',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Produt unit has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitUpdateForm = () => {
	editForm.post('product_units/update',
	{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Produt unit has been updated successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitDeleteForm = () => {
	deleteForm.delete(`/product_units/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success('Produt unit has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.units_id = unitsId.value
    deleteSelectedForm.post(route('product_unit.bulkDelete'),{
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            unitsId.value = [];
            deleteAllSelectedModal.value = false;
            useToast().error('Selected units has been deleted successfully!', {
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
        unitsId.value = props.units.data.map(unit => unit.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        unitsId.value = [];
      }
}

const unitDataLength = computed(() => {
    if (!route().params.search) {
        return props.units.data.length 
    }
     return props.units.data.length  + 1;
});
const appliedFilters = [
    { title: 'search', value: search },
]
const clearFilters = (filter) => {
    if (filter.title == 'search') {
        search.value = '';
    } 
}
</script>

<template>
    <Head :title="title" />

    <TitleContainer :title="title">
        <div class="flex items-center gap-2" v-if="unitDataLength > 0">
            <CreateBtn href="" as="button" @click="createModal = true">
                New unit
            </CreateBtn>
            <ActionDropdown :dataIds="unitsId" :exportPDFRoute="false" 
            :withImportBtn="false" @delete-all-selected="deleteAllSelectedModal = true" />
        </div>

    </TitleContainer>

    <EmptyContainer :title="title" v-show="unitDataLength === 0">
        <CreateBtn as="button" @click="createModal = true">New unit</CreateBtn>
    </EmptyContainer>

    <div class="flex-grow" v-show="unitDataLength > 0 ">
        <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
            <div class="card-body grow-0 ">
                <div class="flex w-full justify-between items-center gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2">
                        <ClearFilters :filters="appliedFilters" @clear-filters="clearFilters" />
                    </div>
                    <div class="flex gap-2 flex-col w-full justify-end sm:flex-row">
                            <SearchInput v-model="search" :url="url" />
                    </div>
                </div>
            </div>
            <Table>
                <template #table-header>
                    <TableHeader>
                        <TableRow>
                            <TableHead v-if="$page.props.auth.user.canDelete">
                                <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox"
                                    class="checkbox checkbox-sm">
                            </TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>CREATED ON</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="unit in units.data" :key="unit.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="unit.id" v-model="unitsId" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>{{ unit.name }}</TableCell>
                            <TableCell>{{ unit.created_at }}</TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <EditIconBtn @click="editModalForm(unit)" />
                                    <DeleteIcon @click="deleteCatForm(unit.id)" />
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="units.data == 0">
                            <TableCell :colspan="3" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="units" />
            <PaginationControlList :url="url" />
            <Pagination :links="units.links" />
        </div>
    </div>
    <!-- create modal -->
    <Modal :show="createModal" @close="createModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new product unit
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCreateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Product unit" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="createForm.name"
                        required
                        placeholder="product unit"
                    />
                    <InputError class="mt-2" :message="createForm.errors.name" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="createModal = false">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': createForm.processing }"
                        :disabled="createForm.processing"
                    >
                        <span v-if="createForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
    <!-- edit modal -->
    <Modal :show="editModal" @close="editModal = false">
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
                    <SecondaryButton class="btn" @click="editModal = false">Cancel</SecondaryButton>
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
    <Modal :show="deleteModal" @close="deleteModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete product units
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
                Delete {{ unitsId.length }} product units
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
</template>
