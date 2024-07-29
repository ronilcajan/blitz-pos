<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    product_categories: Object,
    stores: Object,
	filters: Object
});

const page = usePage();
let search = ref(props.filters.search);
let store = ref('');
const url = '/product_categories';
const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);

let categoryIds = ref([]);
let selectAllCheckbox = ref(false);

const createForm = useForm({name: '',description: ''});
const editForm = useForm({id: '', name: '',description: ''});
const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({categories_id: ''});

const editModalForm = (category) => {
	editForm.clearErrors()
	editForm.id = category.id;
    editForm.name = category.name;
    editForm.description = category.description;
	editModal.value = true;
};


const deleteCatForm = (category_id) => {
	deleteModal.value = true;
	deleteForm.id = category_id
}

const closeModal = () => {
    createForm.clearErrors()
	deleteForm.clearErrors()
	editForm.clearErrors()
    createModal.value = false;
    deleteModal.value = false;
    editModal.value = false;
    createForm.reset();
    deleteForm.reset();
    editForm.reset();
};

const submitCreateForm = () => {
	createForm.post('/product_categories',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Expenses category has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['product_categories']
	})
}

const submitUpdateForm = () => {
	editForm.post('product_categories/update',
	{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
            closeModal();
			useToast().success(`Product category has been updated successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitDeleteForm = () => {
	deleteForm.delete(`/product_categories/${deleteForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().error('Product category has been deleted successfully!', {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitBulkDeleteForm = () => {
    deleteSelectedForm.categories_id = categoryIds.value;
    deleteSelectedForm.post(route('product_categories.bulkDelete'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            categoryIds.value = [];
            deleteSelectedForm.reset();
            deleteAllSelectedModal.value = false;
            useToast().error('Selected categories has been deleted successfully!', {
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
        categoryIds.value = props.product_categories.data.map(product_category => product_category.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        categoryIds.value = [];
      }
}

const productCategoriesDataLength = computed(() => {
     if (Object.keys(route().params).length > 0) {
        return props.product_categories.data.length + 1;
    }
    
    return props.product_categories.data.length;
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
        <div class="flex items-center gap-2" v-if="productCategoriesDataLength > 0">
            <CreateBtn href="" as="button" @click="createModal = true">New category</CreateBtn>
            <ActionDropdown :dataIds="categoryIds" :exportPDFRoute="false" 
            :withImportBtn="false" @delete-all-selected="deleteAllSelectedModal = true" />
        </div>

    </TitleContainer>

    <EmptyContainer :title="title" v-show="productCategoriesDataLength === 0">
        <CreateBtn as="button" @click="createModal = true">New category</CreateBtn>
    </EmptyContainer>

    <div class="flex-grow" v-show="productCategoriesDataLength > 0 ">
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
                            <TableHead>DESCRIPTION</TableHead>
                            <TableHead>CREATED ON</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="category in product_categories.data" :key="category.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="category.id" v-model="categoryIds" type="checkbox" class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>{{ category.name }}</TableCell>
                            <TableCell>{{ category.description }}</TableCell>
                            <TableCell>{{ category.created_at }}</TableCell>

                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <EditIconBtn @click="editModalForm(category)" />
                                    <DeleteIcon @click="deleteCatForm(category.id)" />
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="product_categories.data == 0">
                            <TableCell :colspan="10" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="product_categories" />
            <PaginationControlList :url="url" />
            <Pagination :links="product_categories.links" />
        </div>
    </div>
    <!-- create modal -->
    <Modal :show="createModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new category
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCreateForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Category name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="createForm.name"
                        required
                        placeholder="category name"
                    />
                    <InputError class="mt-2" :message="createForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Description" />
                    <textarea v-model="createForm.description" class="textarea w-full textarea-bordered" placeholder="Description"></textarea>
                    <InputError class="mt-2" :message="createForm.errors.description" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
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
    <Modal :show="editModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Edit category
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitUpdateForm">

                <div class="mb-3">
                    <InputLabel for="name" value="Category name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="editForm.name"
                        required
                        placeholder="category name"
                    />
                    <InputError class="mt-2" :message="editForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Description" />
                    <textarea v-model="editForm.description" class="textarea w-full textarea-bordered" placeholder="Description"></textarea>
                    <InputError class="mt-2" :message="editForm.errors.description" />
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
                Delete category
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
    <Modal :show="deleteAllSelectedModal" @close="deleteAllSelectedModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete {{ categoryIds.length }} categories
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
