<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductDropdownItems from './partials/ProductDropdownItems.vue';
import CounterInput from './partials/CounterInput.vue';
import { router, useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch, computed  } from 'vue';
import debounce from "lodash/debounce";

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    products: Object,
    search_products: Object,
    filter: Object
});

const purchases = reactive([]);
const barcode = ref(props.filter.barcode);
const search = ref(props.filter.search);
const createProductModal = ref(false);
const hideDropdownRef = ref('pending');

const productForm = useForm({
    name: '',
	barcode: '',
	unit: '',
	product_category_id : '',
    product_type: '',
	base_price : '',
    in_store : '',
    store_id : '',
});

const closeModal = () => {
    supplierForm.clearErrors()
    supplierForm.reset();
    productForm.clearErrors()
    createProductModal.value = false;
    productForm.reset();
};


watch(search, debounce(function (value) {
	router.get('/in_house/create',
	{ search: value },
	{ preserveState: true, replace:true, only: ['products'] }
   )
}, 500)) ;

const newPurchase = (product) => {
    const foundProduct = findProductById(product.id, purchases)
    if (foundProduct) {
        updateOrderQuantity(foundProduct)
        console.log(1)
    } else {
        const newOrder = createOrderFromProduct(product);
        addToOrders(newOrder);
    }
    hideDropdownRef.value.focus()
}

const submitProductForm = () => {
	productForm.post('/products',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			productForm.reset()
			useToast().success(`Product has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const findProductById = (product_id, purchases) => {
    return purchases.find(product => product.id === product_id);
}
const createOrderFromProduct = (product) => {
    return {
        id: product.id,
        product: product.name,
        details: product.size,
        qty: 1,
        unit: product.unit,
        stocks: product.stocks,
        price: parseFloat(product.price ?? 0.00).toFixed(2),
        image: product.image,
        get total() {
            return (this.qty * parseFloat(this.price)).toFixed(2);
        },
    };
}
const updateOrderQuantity = (purchase) => {
    purchase.qty += 1;
}

const deleteOrder = (order_id) => {
    purchases.splice(purchases.findIndex(order => order.id === order_id), 1);
}

const addToOrders = (products) => {
    purchases.push(products);
}

const calculateQty = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.qty), 0);
    return formatNumberWithCommas(subTotal);
})

const calculateTotal = computed(() => {
    const total = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);

    return formatNumberWithCommas(total);
})
const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const getCurrentDate = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

const transactionForm = useForm({
	transaction_date: getCurrentDate(),
	status: 'pending',
    total : calculateTotal,
    quantity : calculateQty,
    notes : '',
    items : [],
});

const submitPurchaseForm = () => {
    
    if(purchases.length === 0) {
        useToast().error('Please add product!', {
            position: 'top-right',
            duration: 3000,
            dismissible: true
        });
        return;
    }
    
    transactionForm.items = purchases;

	transactionForm.post('/in_house',
    {
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {

			useToast().success(`New transaction has been created!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
            purchases.length = 0;
		},
        onError: (errors) => {
            useToast().error(`Error! ${errors.error}`, {
                position: 'bottom-top',
                timeout: 3000,
                dismissible: true
            })
        }
	})
}
</script>

<template>
    <Head :title="title" />
    <TitleContainer :title="title">
    </TitleContainer>

    <form class="flex-grow" @submit.prevent="submitPurchaseForm">
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div class="flex justify-between gap-2 flex-col sm:flex-row">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Transaction details</span>
                            </h2>
                        </div>
                    </div>
                    <div class="flex gap-4 flex-col md:flex-row md:w-1/2">
                        <div class="w-full md:w-1/2">
                                <InputLabel for="date" class="label" value="Transaction Date" />
                                <TextInput v-model="transactionForm.transaction_date" type="date" class="input input-sm w-full" required/>
                        </div>
                    </div>
                   
                    <div class="dropdown rounded my-2">
                        <div class="flex justify-between gap-2">
                            <div class="w-full">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                        </svg>
                                    </div>
                                    <input placeholder="Search product name or barcode" v-model="search" class="input pl-8 input-bordered input-sm w-full"/>
                                    <button type="button" v-if="search" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="search = ''">
                                        <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                        </svg>
                                    </button>
                                    <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow mt-4 w-full">
                                        <li class="p-2 px-5 border-b">
                                            <button @click="createProductModal = true" type="button" class="font-semibold text-primary">
                                                + Create product
                                            </button>
                                        </li>
                                        <ProductDropdownItems
                                            v-for="product in products.data"
                                            :product="product"
                                            :key="product.id"
                                            @add-products="newPurchase(product)"
                                        />
                                    </ul>
                                </div>

                            </div>
                            <div class="hidden sm:flex">
                                <PrimaryButton class="btn-sm" type="button">Browse</PrimaryButton>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 flex-col sm:flex-row mb-2">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Used items</span>
                            </h2>
                        </div>
                    </div> 
                    <Table>
                        <template #table-header>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>#</TableHead>
                                    <TableHead>Product</TableHead>
                                    <TableHead>Quantity</TableHead>
                                    <TableHead class="text-right">Sub-total</TableHead>
                                </TableRow>
                            </TableHeader>
                        </template>
                        <template #table-body>
                            <TableBody>
                                <TableRow v-for="(purchase, index) in purchases" :key="purchase.id">
                                    <TableCell>
                                        {{ index + 1 }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex gap-2 grow flex-col md:flex-row">
                                            <img v-if="purchase.image" class="h-12 w-12 shrink-0 rounded-btn" width="56" height="56" :src="purchase.image" alt="Product">
                                            <div class="flex flex-col gap-1">
                                                <div class="text-sm">
                                                    {{ purchase.product }}
                                                </div>
                                                <div class="text-xs text-base-content/50">
                                                    Price: {{ purchase.price }}
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                            <CounterInput v-model="purchase.qty"
                                            :max="purchase.stocks" @increase-by="(n) => purchase.qty += n" @decreased-by="(n) => purchase.qty > 1 ? purchase.qty -= n : 0"/>
                                    </TableCell>
                                    <TableCell>
                                        <span class="mr-3">
                                            {{ $page.props.auth.user.currency }}
                                            {{ formatNumberWithCommas(purchase.total) }}</span>
                                            <DeleteIcon @click="deleteOrder(purchase.id)" />
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </template>
                    </Table>

                    <div class="flex gap-4 justify-between flex-col md:flex-row mt-5">
                        <div class="w-full md:w-1/2">
                            <InputLabel class="label" value="Notes" />
                            <textarea v-model="transactionForm.notes" class="textarea textarea-bordered w-full max-w-xs" placeholder="Type here" ></textarea>
                        </div>
                        <div class="flex w-full md:w-1/2 justify-end">
                            <div class="bg-base-200 w-full md:w-2/3 rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                <div class="flex justify-between mb-2">
                                    <span>Items:</span>
                                    <span>
                                        {{ calculateQty }}</span>
                                </div>
                                <div class="flex justify-between text-2xl font-semibold">
                                    <span>Total:</span>
                                    <span>
                                        {{ $page.props.auth.user.currency }}
                                        {{ calculateTotal }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full justify-center mb-10">
                        <InputLabel class="label" value="Status" />
                        <div class="join">
                            <input v-model="transactionForm.status" value="pending" class="join-item btn btn-sm" type="radio" name="options" aria-label="Pending" ref="hideDropdownRef" checked/>
                            <input v-model="transactionForm.status" value="completed" class="join-item btn btn-sm" type="radio" name="options" aria-label="Completed" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 flex-col lg:flex-row">
                        <div class="flex justify-end gap-3 flex-col md:flex-row">
                            <CancelButton href="/purchase" >Cancel</CancelButton>
                            <CreateSubmitBtn :disabled="transactionForm.processing || purchases.length == 0" v-model="transactionForm">Create</CreateSubmitBtn>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
    <Modal :show="createProductModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add new product
            </h1>
            <form method="dialog" class="w-full" @submit.prevent="submitProductForm">
                <div class="flex justify-between gap-2 flex-col lg:flex-row">
                    <h2 class="card-title grow text-sm mb-2">
                        <span class="uppercase">Product Information</span>
                    </h2>
                </div>
                <div>
                    <InputLabel for="name" value="Product name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="productForm.name"
                        required
                        placeholder="Enter product name"
                    />
                    <InputError class="mt-2" :message="productForm.errors.name" />
                </div>

                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Barcode" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="productForm.barcode"
                            placeholder="Enter product barcode"
                        />
                        <InputError class="mt-2" :message="productForm.errors.barcode" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="product_type" value="Product Type" />
                        <select v-model="productForm.product_type" class="select select-bordered w-full">
                            <option disabled value="">Select a product type</option>
                            <option>
                                sellable
                            </option>
                            <option>
                                consumable
                            </option>
                        </select>
                        <InputError class="mt-2" :message="productForm.errors.product_type" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="form-control">
                            <InputLabel for="name" value="Category" />
                            <select v-model="productForm.product_category_id" required class="select select-bordered w-full">
                                <option disabled selected value="">Select a product category</option>
                                <option v-for="category in categories" :value="category.id" :key="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        <InputError class="mt-2" :message="productForm.errors.product_category_id" />
                    </div>
                    <div class="form-control">
                        <div class="w-full">
                            <InputLabel for="name" value="Unit" />
                            <select class="select select-bordered w-full" v-model="productForm.unit" required>
                                <option disabled selected value="">Select a product unit</option>
                                <option v-for="unit in units" :value="unit.name" :key="unit.id">
                                    {{ unit.name }}
                                </option>
                            </select>

                        </div>
                        <InputError class="mt-2" :message="productForm.errors.unit" />

                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2 mt-3">
                    <div class="form-control">
                        <InputLabel value="Price" />
                        <TextInput
                            type="number"
                            class="block w-full"
                            v-model="productForm.base_price"
                            required
                            placeholder="Enter product price"
                        />
                        <InputError class="mt-2" :message="productForm.errors.base_price" />
                    </div>
                    <div class="form-control">
                        <InputLabel value="Stocks" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="productForm.in_store"

                            placeholder="Enter stocks"
                        />
                        <InputError class="mt-2" :message="productForm.errors.in_store" />
                    </div>
                </div>
                <div v-show="$page.props.auth.user.isSuperAdmin">
                    <InputLabel for="phone" value="Store" />
                    <select v-model="productForm.store_id" class="select select-bordered w-full">
                        <option disabled selected value="">Select a store</option>
                        <option v-for="store in stores" :value="store.id" :key="store.id">
                            {{ store.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="productForm.errors.store_id" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': productForm.processing }"
                        :disabled="productForm.processing"
                    >
                        <span v-if="productForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
