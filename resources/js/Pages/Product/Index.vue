<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';

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
let category = ref('');
let type = ref('');
const url = '/products';
const productsDataLength = props.products.data.length;

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const importModal = ref(false);

let productIds = ref([]);
let selectAllCheckbox = ref(false);

const importForm = useForm({ import_file: '' });
const deleteForm = useForm({ id: '' });
const deleteSelectedForm = useForm({ products_id: [] });

const deleteProductForm = (product_id) => {
    deleteModal.value = true;
    deleteForm.id = product_id
}

const selectAll = () => {
    if (selectAllCheckbox.value) {
        productIds.value = props.products.data.map(product => product.id);
    } else {
        productIds.value = [];
    }
}

const isVisible = (id, event) => {
    router.patch(route('products.change_status', id), {
        status: event.checked,
    },
        {
            preserveState: true, replace: true,
            onSuccess: () => {
                useToast().success('Product visibility has been changed!', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });
            }, only: ['products'],
        })
}


const submitDeleteForm = () => {
    deleteForm.delete(`/products/${deleteForm.id}`, {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            deleteForm.clearErrors()
            deleteForm.reset();
            deleteModal.value = false;
            useToast().error('Product has been deleted!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
        },
        only: ['products'],
    })
}


const submitImportProducts = () => {
    importForm.visit(route('products.import'),
        {
            replace: true,
            preserveScroll: true,
            onSuccess: () => {
                importForm.reset();
                importModal.value = false
                useToast().success('Products has been imported successfully!', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });
                router.reload({ only: ['products'] })
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

const submitBulkDeleteForm = () => {
    deleteSelectedForm.products_id = productIds.value
    deleteSelectedForm.post(route('products.bulkDelete'),
        {
            replace: true,
            preserveScroll: true,
            onSuccess: () => {
                productIds.value = [];
                deleteSelectedForm.reset();
                deleteAllSelectedModal.value = false;
                useToast().error('Selected products has been deleted.!', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });
                selectAllCheckbox.value = false;
            },
        })
}

const appliedFilters = [
    { title: 'category', value: category },
    { title: 'search', value: search },
    { title: 'usage type', value: type },
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

const product_types = [
    { id: '1', name: 'sellable', },
    { id: '2', name: 'internal_use', },

]
</script>

<template>

    <Head :title="title" />

    <TitleContainer :title="title">
        <div class="flex items-center gap-2" v-if="productsDataLength > 0">
            <CreateBtnLink href="/products/create">New product</CreateBtnLink>
            <ActionDropdown :dataIds="productIds" :exportPDFRoute="route('products.export_pdf')"
                :exportExcelRoute="route('products.export_excel')" :withImportBtn="true"
                @open-import-modal="importModal = true" @delete-all-selected="deleteAllSelectedModal = true" />

        </div>

    </TitleContainer>

    <EmptyContainer :title="title" v-if="productsDataLength == 0">
        <CreateBtnLink href="/products/create">New product</CreateBtnLink>
    </EmptyContainer>

    <div class="flex-grow" v-if="productsDataLength > 0">
        <section class="col-span-12 overflow-hidden bg-base-100 shadow rounded-xl">
            <div class="card-body grow-0">
                <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">

                        <SelectDropdownFilter v-model="category" :url="url" :title="`category`"
                            :options="product_categories" />
                        <SelectDropdownFilter v-model="type" :url="url" :title="`type`" :options="product_types" />

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
                            <TableHead>Name</TableHead>
                            <TableHead>Price({{ $page.props.auth.user.currency }})</TableHead>
                            <TableHead>Unit</TableHead>
                            <TableHead>Category</TableHead>
                            <TableHead>Size</TableHead>
                            <TableHead>Usage Type</TableHead>
                            <TableHead>Visible</TableHead>
                        </TableRow>
                    </TableHeader>
                </template>
                <template #table-body>
                    <TableBody>
                        <TableRow v-for="product in products.data" :key="product.id">
                            <TableCell v-if="$page.props.auth.user.canDelete">
                                <input :value="product.id" v-model="productIds" type="checkbox"
                                    class="checkbox checkbox-sm">
                            </TableCell>
                            <TableCell>
                                <Link :href="`/products/${product.id}`" 
                                    class="text-blue-800 ">
                                    <div class="flex items-center gap-2">
                                    <Avatar :src="product.image" />
                                    <div class="flex flex-col font-semibold">
                                        {{ product.name }}
                                        <p class="text-xs opacity-50">
                                            {{ product.barcode }}
                                        </p>
                                    </div>
                                </div>
                                </Link>
                               
                            </TableCell>
                            <TableCell>{{ product.price }}</TableCell>
                            <TableCell>{{ product.unit }}</TableCell>
                            <TableCell>{{ product.category }}</TableCell>

                            <TableCell>{{ product.size }}</TableCell>
                            <TableCell>
                                <div class="badge badge-sm"
                                    :class="product.usage_type === 'sellable' ? 'badge-primary' : 'badge-neutral'">
                                    {{ product.usage_type }}</div>
                            </TableCell>
                            <TableCell>
                                <input @change="isVisible(product.id, $event.target)" type="checkbox"
                                    class="toggle toggle-sm toggle-success" :checked="product.visible" />
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <EditIconBtnLink :href="`/products/${product.id}/edit`" />
                                    <DeleteIcon @modal-show="deleteProductForm(product.id)" />
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="products.data == 0">
                            <TableCell :colspan="10" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>

        </section>
        <div class="flex justify-between item-center flex-col sm:flex-row gap-3 mt-5">
            <PaginationResultRange :data="products" />
            <PaginationControlList :url="url" />
            <Pagination :links="products.links" />
        </div>
    </div>
    <!-- delete modal -->
    <Modal :show="deleteModal" @close="deleteModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Delete products
            </h1>
            <p>Are you sure you want to delete this data? This action cannot be undone.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteModal = false">Cancel</SecondaryButton>
                    <DangerButton class="ms-3" :class="{ 'opacity-25': deleteForm.processing }"
                        :disabled="deleteForm.processing">
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
                Delete {{ productIds.length }} selected products
            </h1>
            <p>Are you sure you want to delete {{ productIds.length }} selected products? This action cannot be undone.
            </p>
            <form method="dialog" class="w-full" @submit.prevent="submitBulkDeleteForm">

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="deleteAllSelectedModal = false"
                        :disabled="deleteSelectedForm.processing">Cancel</SecondaryButton>
                    <DangerButton class="ms-3" :class="{ 'opacity-25': deleteSelectedForm.processing }"
                        :disabled="deleteSelectedForm.processing">
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
                Import Products
            </h1>
            <p>Please upload your product data using an Excel file.</p>
            <form method="dialog" class="w-full" @submit.prevent="submitImportProducts">

                <div class="mb-3">
                    <InputLabel value="Select an Excel file" />
                    <input type="file" class="file-input file-input-bordered file-input-sm w-full"
                        @input="importForm.import_file = $event.target.files[0]" accept=".xlsx, .xls" required />
                    <progress class="progress" v-if="importForm.progress" :value="importForm.progress.percentage"
                        max="100">
                        {{ importForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="importForm.errors.import_file" />

                    <div>
                        <small>Note: Before uploading, make sure you use this <a
                                :href="route('products.donwloadTemplate')" class="text-primary">template</a>.</small>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="importModal = false">Cancel</SecondaryButton>
                    <PrimaryButton class="ms-3" :class="{ 'opacity-25': importForm.processing }"
                        :disabled="importForm.processing">
                        <span v-if="importForm.processing" class="loading loading-spinner"></span>
                        Import Now
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
