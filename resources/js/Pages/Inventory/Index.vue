<script setup>
import { computed, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import debounce from "lodash/debounce";
import { useToast } from 'vue-toast-notification';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
	products: Object,
    stores: Object,
    product_categories: Object,
    suppliers: Object,
	filter: Object
});

const page = usePage();
let search = ref(props.filter.search);
let category = ref('');
let supplier = ref('');

const canDelete = page.props.auth.user.canDelete

const deleteModal = ref(false);
const deleteAllSelectedModal = ref(false);
const importModal = ref(false);
const stockTransferModal = ref(false);
const transferStocksSelectedModal = ref(false);

let productIds = ref([]);
const maxQuantity = ref(0);
let selectAllCheckbox = ref(false);

const deleteForm = useForm({id: ''});
const deleteSelectedForm = useForm({products_id: []});
const importForm = useForm({import_file: ''});
const stocksForm = useForm({id: '', qty: 1});
const stocksSelectedForm = useForm({products_id: [], qty: 1});


const deleteProductForm = (product_id) => {
	deleteModal.value = true;
	deleteForm.id = product_id
}

const closeModal = () => {
	deleteForm.clearErrors()
    deleteModal.value = false;
    deleteAllSelectedModal.value = false;
    deleteForm.reset();
};

const stocksTransfer = (product) => {
    stocksForm.id = product.id;
    let cleanedQty = product.in_warehouse.toString().replace(/,/g, '').replace(/\.00$/, '');
    maxQuantity.value  = cleanedQty
    stockTransferModal.value = true;
}

const selectAll = () => {
	if (selectAllCheckbox.value) {
        productIds.value = props.products.data.map(product => product.id);
    } else {
        productIds.value = [];
    }
}

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
	})
}

const submitStockTranserSelectedProducts = () => {
    let transfer_stocks = stocksSelectedForm.qty;
    stocksSelectedForm.products_id = productIds.value;
	stocksSelectedForm.post(`/inventory/stocks/bulk_update/`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			transferStocksSelectedModal.value = false;
            stocksSelectedForm.reset();
			useToast().success(`Success! ${transfer_stocks} stock(s) have been transferred from each selected product to the store.`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});

            productIds.value = [];
            selectAllCheckbox.value = false;
		},
        onError: errors => {
            useToast().error(`Error! ${errors.error}`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        },
        only: ['products']
	})
}

const submitStockTranser = () => {
    let transfer_stocks = stocksForm.qty;
	stocksForm.patch(`/inventory/stocks/update/${stocksForm.id}`,{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			stockTransferModal.value = false;
            stocksForm.reset();
			useToast().success(`Success! ${transfer_stocks} stock(s) have been transferred to the store.`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['products']
	})
}
const submitImportProducts = () => {
    importForm.post(route('products.import'),
    {
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            importModal.value=false
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
const submitBulkDeleteForm = () => {
    deleteSelectedForm.products_id = productIds.value;
    deleteSelectedForm.post(route('products.bulkDelete'),
    {
        forceFormData: true,
        replace: true,
        preserveScroll: true,
        onSuccess: () => {
            deleteSelectedForm.reset();
            deleteAllSelectedModal.value = false;
            useToast().success('Multiple products has been deleted successfully!', {
                position: 'top-right',
                duration: 3000,
                dismissible: true
            });
            productIds.value = [];
            selectAllCheckbox.value = false;
        },
    })
}

watch(category, value => {
	router.get('/inventory',
	{ category: value },
	{ preserveState: true, replace:true })
})

watch(supplier, value => {
	router.get('/inventory',
	{ supplier: value },
	{ preserveState: true, replace:true })
})

let type = ref('');
const url = '/inventory';
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

const productsDataLength = computed(() => {
    if(route().params){
        return props.products.data.length + 1
    }
    return props.products.data.length
})

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

        <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm rounded-xl">
            <div class="card-body grow-0">
                <div class="flex justify-between gap-2 flex-col-reverse sm:flex-row">
                    <div class="flex gap-2 flex-col sm:flex-row">

                        <SelectDropdownFilter v-if="product_categories.length" v-model="category" :url="url" :title="`category`"
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
                            <TableHead>Size</TableHead>
                            <TableHead>Price({{ $page.props.auth.user.currency }})</TableHead>
                            <TableHead>Stocks</TableHead>
                            <TableHead>In Warehouse</TableHead>
                            <TableHead>In Store</TableHead>
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
                            <TableCell>{{ product.size }}</TableCell>
                            <TableCell>{{ product.stocks }} {{ product.price }}

                            </TableCell>
                            <TableCell> 
                                {{ product.in_warehouse }} {{ product.unit }}
                                </TableCell>

                            <TableCell> 
                                {{ product.in_store }} {{ product.unit }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <button v-if="product.in_warehouse" @click="stocksTransfer(product)" class=" hover:text-primary" title="In warehouse stocks to store">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package-export"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12v9" /><path d="M12 12l-8 -4.5" /><path d="M15 18h7" /><path d="M19 15l3 3l-3 3" /></svg>
                                    </button>
                                    <EditIconBtnLink :href="`/products/${product.id}/edit`" />
                                    <DeleteIcon @modal-show="deleteProductForm(product.id)" />
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="products.data == 0">
                            <TableCell :colspan="6" class="text-center">
                                No {{ title.toLocaleLowerCase() }} found!
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </template>
            </Table>
        </section>
    </div>
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
                Delete {{ productIds.length }} selected products
            </h1>
            <p>Are you sure you want to delete {{ productIds.length }} selected products? This action cannot be undone.</p>
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
    <Modal :show="stockTransferModal" @close="stockTransferModal = false" maxWidth="md">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Stocks Transfer
            </h1>
            <form method="dialog" class="w-full" @submit.prevent="submitStockTranser">

                <div class="mb-3">
                    <InputLabel value="Enter quantity" />
                    <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="stocksForm.qty"
                            :max="maxQuantity"
                            :placeholder="`Enter a quantity up to ${maxQuantity} for transfer`"
                        />
                    <InputError class="mt-2" :message="stocksForm.errors.qty" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="stockTransferModal = false">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': stocksForm.processing }"
                        :disabled="stocksForm.processing"
                    >
                        <span v-if="stocksForm.processing" class="loading loading-spinner"></span>
                        Transfer Now
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

    <Modal :show="transferStocksSelectedModal" @close="transferStocksSelectedModal = false" maxWidth="md">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Transfer Stocks for Selected Products
            </h1>
            <form method="dialog" class="w-full" @submit.prevent="submitStockTranserSelectedProducts">

                <div class="mb-3">
                    <InputLabel value="Enter quantity" />
                    <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="stocksSelectedForm.qty"
                            placeholder="Enter quantity for transfer"
                        />
                    <InputError class="mt-2" :message="stocksSelectedForm.errors.qty" />

                    <div>
                        <small>Note: This modal allows you to transfer stock quantities for multiple selected products. If a product has zero stock in the warehouse, it will be skipped. If you enter a quantity greater than the warehouse stock, all available stock for that product will be transferred.</small>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="transferStocksSelectedModal = false">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': stocksSelectedForm.processing }"
                        :disabled="stocksSelectedForm.processing"
                    >
                        <span v-if="stocksSelectedForm.processing" class="loading loading-spinner"></span>
                        Transfer Now
                    </PrimaryButton>
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
