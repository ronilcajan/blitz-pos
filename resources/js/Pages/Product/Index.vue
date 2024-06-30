<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import StatusFilter from './partials/StatusFilter.vue';
import ImportButton from './partials/ImportButton.vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	products: Object,
    stores: Object,
    product_categories: Object,
	filter: Object
});

const page = usePage();
let search = ref(props.filter.search);
let store = ref('');
let category = ref('');
let type = ref('');
const url = '/products';

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const importModal = ref(false);

let productIds = ref([]);
let selectAllCheckbox = ref(false);

const importForm = useForm({import_file: ''});
const deleteForm = useForm({id: ''});

const deleteProductForm = (product_id) => {
	deleteModal.value = true;
	deleteForm.id = product_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();

    importModal.value=false
};

const submitDeleteForm = () => {
	deleteForm.delete(`/products/${deleteForm.id}`,{
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
        only: ['products'],
	})
}

const submitBulkDeleteForm = () => {
    router.post(route('products.bulkDelete'),
    {
        products_id: productIds.value
    },
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            productIds.value = [];
            closeModal();
            useToast().success('Multiple products has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            selectAllCheckbox.value = false;
        },
    })
}

const submitImportProducts = () => {
    importForm.post(route('products.import'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            useToast().success('Products has been imported successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            router.reload({only: ['products']})
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

const selectAll = () => {
	if (selectAllCheckbox.value) {
        // If "Select All" checkbox is checked, select all users
        productIds.value = props.products.data.map(product => product.id);
      } else {
        // If "Select All" checkbox is unchecked, deselect all users
        productIds.value = [];
      }
}

const isVisible = (id,event) => {
	router.patch(route('products.change_status',id), {
            status: event.checked,
        },
	    { preserveState: true, replace:true,
            onSuccess: () => {
            useToast().success('Product visibility has been changed!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        },  only: ['products'], })
}

watch(category, value => {
	router.get('/products',
	{ category: value },
	{ preserveState: true, replace:true })
})
watch(type, value => {
	router.get('/products',
	{ type: value },
	{ preserveState: true, replace:true })
})

const isSuperAdmin = page.props.auth.user.isSuperAdmin
const canDelete = page.props.auth.user.canDelete

const showRefresh = computed(() => {
    return store.value !== '' || category.value !== '' || type.value !== '';
})

</script>

<template>
    <Head :title="title" />
    <div class="flex justify-end items-center mb-5 gap-3 flex-wrap">
        <CreateButtonLink href="/products/create">New product</CreateButtonLink>
        <DownloadButton :href="route('products.export')">Export Excel</DownloadButton>
        <ImportButton @click="importModal=true">Import Excel</ImportButton>
        <StatusFilter v-model="type" />
    </div>
    <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                <div class="flex gap-2 flex-col sm:flex-row">
                    <FilterByStoreDropdown v-model="store" :stores="stores" :url="url"/>
                    <div class="w-full">
                        <select v-model="category" class="select select-bordered select-sm w-full">
                            <option selected value="">Filter by categories</option>
                            <option v-for="category in product_categories" :value="category.name" :key="category.id">
                                {{ category.name }}
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
            <div>
                <DeleteButton v-if="canDelete" v-show="productIds.length > 0" @click="deleteAllSelectedModal = true">
                    Delete
                </DeleteButton>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead class="uppercase">
                    <tr>
                        <th v-if="canDelete">
                            <input @change="selectAll" v-model="selectAllCheckbox" type="checkbox" class="checkbox checkbox-sm">
                        </th>
                        <th>
                            <div class="font-bold">Name</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Size</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Brand</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Category</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Product Type</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Unit</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Price</div>
                        </th>
                        <th class="hidden sm:table-cell">
                            <div class="font-bold">Visible</div>
                        </th>
                        <th class="hidden sm:table-cell" v-show="isSuperAdmin">
                            <div class="font-bold">Store</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="w-0" v-if="canDelete">
                            <input :value="product.id" v-model="productIds" type="checkbox" class="checkbox checkbox-sm">
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder" v-show="!product.image">
                                    <div class="w-10 bg-neutral text-neutral-content rounded-full">
                                        <span class="text-xl">{{ product.name[0] }}</span>
                                    </div>
                                </div>
                                <div class="avatar" v-show="product.image">
                                    <div class="mask mask-squircle h-10 w-10">
                                        <img :src="product.image" alt="logo">
                                    </div>
                                </div>
                                <div>
                                    <div class="flex text-sm font-bold gap-2">
                                        {{ product.name }}
                                    </div>
                                    <div class="text-xs opacity-50">
                                        {{ product.barcode }}
                                    </div>
                                    <div class="sm:hidden">
                                        <div class="text-xs opacity-50">{{ product.phone }}</div>
                                        <div class="text-xs opacity-50">{{ product.address }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden sm:table-cell">
                            {{ product.size }}</td>
                        <td class="hidden sm:table-cell">
                            {{ product.brand }}</td>
                        <td class="hidden sm:table-cell">
                                {{ product.category }}
                        </td>
                        <td class="hidden sm:table-cell">
                            <div class="badge" :class="product.product_type === 'sellable' ? 'badge-primary' : 'badge-neutral'">
                                {{ product.product_type }}</div>
                        </td>
                        <td class="hidden sm:table-cell">
                            {{ product.unit }}</td>
                        <td class="hidden sm:table-cell">
                            {{ product.price }}</td>
                        <td class="hidden sm:table-cell">
                            <input @change="isVisible(product.id, $event.target)" type="checkbox" class="toggle toggle-sm toggle-success" :checked="product.visible" />
                        </td>
                        <td class="hidden sm:table-cell" v-show="isSuperAdmin">
                            {{ product.store }}</td>
                        <td>
                            <div class="flex items-center space-x-2 justify-center">
                                <EditIconBtn :href="`/products/${product.id}/edit`"/>
                                <DeleteIcon @modal-show="deleteProductForm(product.id)" />
                            </div>
                        </td>
                    </tr>
                    <tr v-if="products.data.length  <= 0">
                        <td colspan="12" class="text-center">
                            No data found
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
    </section>
    <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
        <PaginationResultRange :data="products" />
        <PaginationControlList :url="url" />
        <Pagination :links="products.links" />
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete products
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
                Delete {{ productIds.length }} products
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

    <Modal :show="importModal" @close="closeModal" maxWidth="md">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Import Products
            </h1>
            <p>Please upload your product data using an Excel file.</p>
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
                        <small>Note: Before uploading, make sure you use this <a :href="route('products.donwloadTemplate')" class="text-primary">template</a>.</small>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
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
