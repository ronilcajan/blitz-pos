<script setup>
import { reactive, ref,watch } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import POSLayout from '@/Layouts/POSLayout.vue';
import CounterInput from './partials/CounterInput.vue';
import SearchBar from './partials/SearchBar.vue';
import debounce from "lodash/debounce";

defineOptions({ layout: POSLayout })

const props = defineProps({
    title: String,
    store: Object,
    stores: Object,
	customers: Object,
    products: Object,
    categories: Object,
    units: Object,
	filter: Object
});

const page = usePage();
const purchase = reactive([]);
const customer = ref('');
const search = ref(props.filter.search);
const datetime = ref('');
const createProductModal = ref(false);
const createCustomerModal = ref(false);

const productForm = useForm({
    name: '',
	barcode: '',
	unit: '',
	product_category_id : '',
    product_type: '',
	base_price : '',
    in_store : '',
    store_id : page.props.auth.user.store_id,
});

const customerForm = useForm({
    name: '',
	email: '',
	phone : '',
	address : '',
    store_id : page.props.auth.user.store_id,
});

const closeModal = () => {
    createProductModal.value = false;
    productForm.clearErrors()
    productForm.reset();

    createCustomerModal.value = false;
    customerForm.clearErrors()
    customerForm.reset();
};

watch(search, debounce(function (value) {
	router.get('/pos',
	{ search: value },
	{ preserveState: true, replace:true, only: ['products'] }
   )
}, 500)) ;

const submitProductForm = () => {
	productForm.post('/products',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success(`Product has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['products']
	})
}
const submitCustomerForm = () => {
	customerForm.post('/customers',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal();
			useToast().success(`Customer has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
        only: ['customers']
	})
}
const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
const updateDateTime = () => {
    // Get the current date and time
    let date = new Date();

    // Options for formatting the date string
    let options = {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
    };
    // Format the date string
    let formatted_date = date.toLocaleDateString('en-US', options).replace(',', '');
    datetime.value = formatted_date;
}
setInterval(updateDateTime, 1000);
updateDateTime();
</script>

<template>
    <Head :title="title" />
    <div class="grid gap-4 md:grid-cols-5">
        <div class="col-span-5 md:col-span-3">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-3 md:p-5">
                    <div class="flex justify-between gap-2">
                        <div class="w-full">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <SearchBar v-model="search" />
                                <ul tabindex="0" class="dropdown-content z-[1] bg-base-100 shadow mt-4 w-full">
                                    <ProductDropdownItems
                                        v-for="product in products.data"
                                        :product="product"
                                        :key="product.id"
                                        @add-products="newPurchase(product)"
                                    />
                                </ul>
                            </div>

                        </div>
                <div class="flex gap-2">
                    <PrimaryButton @click="createProductModal = true" class="btn-sm tooltip" data-tip="Add products" type="button">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </PrimaryButton>
                    <PrimaryButton class="btn-sm tooltip" data-tip="Barcode scan" type="button">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
                    </PrimaryButton>
                </div>
                    </div>
                    <!-- products -->
                    <div class="hidden md:flex flex-wrap gap-3 pb-5 justify-start mt-4 overflow-x-auto overflow-y-auto" style="height:750px">
                        <div v-for="product in products.data" :key="product.id" class="card w-44 bg-base-100 shadow" style="height:250px">
                            <figure class="h-24">
                                <img class="object-fill" v-if="product.image" :src="product.image" alt="Shoes" loading="lazy" />
                            </figure>
                            <div class="p-3">
                                <h1 class="font-bold">
                                    {{ product.name }}
                                </h1>
                                <div class="flex flex-col text-sm">
                                    <div>{{ product.barcode }}</div>
                                    {{ product.size }}
                                    <div>
                                        {{ product.stocks }} {{ product.unit }}
                                    </div>
                                </div>
                                <div class="card-actions justify-end">
                                <button class="btn btn-sm btn-primary mt-2">
                                    {{ $page.props.auth.user.currency }}
                                    {{ formatNumberWithCommas(product.price) }}</button>
                                </div>
                            </div>
                        </div>


                    </div>
                    <Pagination :links="products.links" />
                </div>
            </div>
        </div>
        <div class="col-span-5 md:col-span-2">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-3 md:p-5">
                    <div class="w-full flex gap-2">
                        <select v-model="customer" class="select select-bordered select-sm w-full">
                            <option selected value="">Select customer</option>
                            <option v-for="customer in customers" :value="customer.name" :key="customer.id">
                                {{ customer.name }}
                            </option>
                        </select>
                        <PrimaryButton @click="createCustomerModal = true" class="btn-sm tooltip" data-tip="Add products" type="button">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                    </PrimaryButton>
                    </div>
                    <div style="height:530px" class="mt-2 overflow-x-auto overflow-y-auto">

                        <div class="border border-gray-100 p-2 rounded flex justify-between items-center">
                            <div class="flex gap-3 justify-center items-center">
                                <div class="avatar">
                                    <div class="w-16 rounded-full">
                                        <img src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.jpg" />
                                    </div>
                                </div>
                                <div class="">
                                    <div class="font-bold">
                                    Product 1 sdfdsf sdfsdf sdfs fsfsdfsdf
                                    </div>
                                    <div class="text-sm">
                                    Size 2
                                    </div>
                                    <div class="text-sm">
                                    100.00/box
                                    </div>
                                </div>
                            </div>
                            <div class="font-bold hidden lg:flex justify-center items-center gap-2">
                                <CounterInput v-model="purchase.qty" @increase-by="(n) => purchase.qty += n" @decreased-by="(n) => purchase.qty > 1 ? purchase.qty -= n : 0"/>
                            </div>
                            <div class="flex flex-col items-end ">
                                <p class="font-bold mb-2 text-right">PHP 2,3423.00</p>

                                <div class="flex gap-2 justify-center align-bottom">
                                    <div class="lg:hidden">
                                        <CounterInput v-model="purchase.qty" @increase-by="(n) => purchase.qty += n" @decreased-by="(n) => purchase.qty > 1 ? purchase.qty -= n : 0"/>

                                    </div>
                                    <DeleteIcon @click="deleteOrder(purchase.id)"/>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="border p-3 rounded border-gray-100 bg-base-200">
                        <div class="flex justify-between  uppercase">
                            <div>Items</div>
                            <div>2</div>
                        </div>
                        <div class="flex justify-between  uppercase">
                            <div>Sub-total</div>
                            <div>P2,000.00</div>
                        </div>
                        <div class="flex justify-between  uppercase">
                            <div>Discount</div>
                            <div>P2,000.00</div>
                        </div>
                        <div class="flex justify-between uppercase">
                            <div class="text-2xl font-bold">Grand Total</div>
                            <div class="text-2xl font-bold">P2,000.00</div>
                        </div>
                    </div>

                    <div class="flex p-2 gap-3 justify-between">
                        <div class="w-full text-xs">
                            <div>
                                Store: {{ $page.props.auth.user.store_name }}
                            </div>
                            <div>
                                Date: <span>{{ datetime }}</span>
                            </div>
                            <div>
                                User: {{ $page.props.auth.user.name }}
                            </div>
                            <div>
                                Powered by: POSblend.
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button class="btn lg:btn-lg">Cancel</button>
                            <button class="btn lg:btn-lg btn-primary">Finish</button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
    <Modal :show="createCustomerModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add new customer
            </h1>
            <form method="dialog" class="w-full" @submit.prevent="submitCustomerForm">
                <div class="flex justify-between gap-2 flex-col lg:flex-row">
                    <h2 class="card-title grow text-sm mb-2">
                        <span class="uppercase">Customer Information</span>
                    </h2>
                </div>
                <div class="mb-3">
                    <InputLabel for="name" value="Customer name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="customerForm.name"
                        required
                        placeholder="Enter name"
                    />
                    <InputError class="mt-2" :message="customerForm.errors.name" />
                </div>

                <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Email address" />
                        <TextInput
                            type="email"
                            class="block w-full"
                            v-model="customerForm.email"
                            required
                            placeholder="Enter email address"
                        />
                        <InputError class="mt-2" :message="customerForm.errors.email" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Phone number" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="customerForm.phone"

                            placeholder="Enter phone number"
                        />
                        <InputError class="mt-2" :message="customerForm.errors.phone" />
                    </div>
                </div>
                <div class="mb-3">
                    <InputLabel value="Address" />
                    <textarea v-model="customerForm.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                    <InputError class="mt-2" :message="customerForm.errors.address" />
                </div>
                <div v-show="$page.props.auth.user.isSuperAdmin">
                    <InputLabel for="phone" value="Store" />
                    <select v-model="customerForm.store_id" class="select select-bordered w-full">
                        <option disabled selected value="">Select a store</option>
                        <option v-for="store in stores" :value="store.id" :key="store.id">
                            {{ store.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="customerForm.errors.store_id" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': customerForm.processing }"
                        :disabled="customerForm.processing"
                    >
                        <span v-if="customerForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
