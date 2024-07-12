<script setup>
import { reactive, ref, watch, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import POSLayout from '@/Layouts/POSLayout.vue';
import SearchBar from './partials/SearchBar.vue';
import ProductDropdownItems from './partials/ProductDropdownItems.vue';
import ProductCard from './partials/ProductCard.vue';
import PurchaseCard from './partials/PurchaseCard.vue';
import PaymentButtons from './partials/PaymentButtons.vue';
import DraftOrdersDropdown from './partials/DraftOrdersDropdown.vue';
import debounce from "lodash/debounce";

defineOptions({ layout: POSLayout })

const props = defineProps({
    title: String,
    store: Object,
    stores: Object,
	customers: Object,
    orders: Object,
    products: Object,
    categories: Object,
    units: Object,
	filter: Object,
    sales_id: Number
});

const page = usePage();
const purchases = reactive([]);
const search = ref(props.filter.search);
const datetime = ref('');
const discount = ref(0);
const tax = ref(0);
const createProductModal = ref(false);
const createCustomerModal = ref(false);
const addDiscountModal = ref(false);
const addTaxModal = ref(false);
const cancelPurchaseModal = ref(false);
const reviewPurchaseModal = ref(false);
const confirmPurchaseModal = ref(false);
const hideDropdownRef = ref('pending');

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
    customerForm.reset()
};

watch(search, debounce(function (value) {
	router.get('/pos',
	{ search: value },
	{ preserveState: true, replace:true, only: ['products'] }
   )
}, 500)) ;

const newPurchase = (product) => {
    if(product.price === 0){
        useToast().error(`Product price not set. Please update the price!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        return;
    }
    const foundProduct = findProductById(product.id, purchases)
    if (foundProduct) {
        updateOrderQuantity(foundProduct)
    } else {
        const newOrder = createOrderFromProduct(product);
        addPurchase(newOrder);
    }
    hideDropdownRef.value.focus()
}
const findProductById = (product_id, search_products) => {
    return search_products.find(product => product.id === product_id);
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
    purchase.qty++;
}
const deleteOrder = (order_id) => {
    purchases.splice(purchases.findIndex(order => order.id === order_id), 1);
}

const addPurchase = (products) => {
    purchases.push(products);
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

const purchaseForm = useForm({
    customer_id: '',
	transaction_date: updateDateTime(),
	quantity : 0,
    sub_total: 0,
	discount : discount.value,
    tax : tax.value,
    total : 0,
    payment_tender : 0,
    payment_changed : 0,
    payment_method : 'cash',
    referrence : '',
    notes : '',
    print : true,
    items : [],
});

const calculateSubTotal = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    return subTotal;
})
const calculateQty = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.qty), 0);
    return subTotal;
})

const calculateTotal = computed(() => {
    const subTotal = purchases.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    const discountValue = parseFloat(purchaseForm.discount);
    const taxValue = parseFloat(purchaseForm.tax);
    const total = (subTotal + taxValue) - (discountValue);
    return total.toFixed(2);
})

const checkDiscount = () => {
    if(purchaseForm.discount > purchaseForm.total){
        useToast().warning(`Warning! Discount is greater than total payments.`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
		});
        return;
    }
    if(purchaseForm.discount < 0){
        useToast().warning(`Warning! Discount is less than 0.`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        return;
    }
    if(purchaseForm.discount == ''){
        purchaseForm.discount = 0;
    }

    purchaseForm.total = calculateTotal
}

const checkTax = () => {
    if(purchaseForm.tax < 0){
        useToast().warning(`Warning! Tax is less than 0.`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        return;
    }
    if(purchaseForm.tax == ''){
        purchaseForm.tax = 0;
    }

    purchaseForm.total = calculateTotal
}

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

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

watch(purchases,  (newItems) => {
    purchaseForm.quantity = calculateQty.value;
    purchaseForm.sub_total = calculateSubTotal.value;
    purchaseForm.total = calculateTotal.value;
});

const setPaymentAmount = (amount) => {

    if (typeof purchaseForm.payment_tender === 'undefined' || isNaN(purchaseForm.payment_tender)) {
        purchaseForm.payment_tender = 0;
    }else {
        purchaseForm.payment_tender = parseFloat(purchaseForm.payment_tender);
    }

    if (amount === 'reset') {
        purchaseForm.payment_tender = 0;
        purchaseForm.payment_changed = 0;
        return purchaseForm.payment_tender;
    }

    let parsedAmount = parseFloat(amount);

    if (isNaN(parsedAmount)) {
        throw new Error('Invalid amount');
    }

    purchaseForm.payment_tender += parsedAmount;


    if(checkPayment(purchaseForm.payment_tender)){
        useToast().error(`Insufficient payment!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        return;
    }

    paymentChanged();

    return purchaseForm.payment_tender.toFixed(2);

};
const setPaymentAmount2 = (amount) => {
    if (typeof purchaseForm.payment_tender === 'undefined' || isNaN(purchaseForm.payment_tender)) {
        purchaseForm.payment_tender = 0;
    }else {
        purchaseForm.payment_tender = parseFloat(purchaseForm.payment_tender);
    }

    if (amount === 'reset') {
        purchaseForm.payment_tender = 0;
        purchaseForm.payment_changed = 0;
        return purchaseForm.payment_tender;
    }

    let parsedAmount = parseFloat(amount);

    if (isNaN(parsedAmount)) {
        throw new Error('Invalid amount');
    }

    if(checkPayment(purchaseForm.payment_tender)){
        useToast().error(`Insufficient payment!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        return;
    }

    paymentChanged()
    return purchaseForm.payment_tender.toFixed(2);

};
const checkPayment = (payment_tender) => {
      return parseFloat(purchaseForm.total) > payment_tender;
}

const paymentChanged = () => {
      return purchaseForm.payment_changed = Math.abs(parseFloat(purchaseForm.total) - purchaseForm.payment_tender).toFixed(2);
}
const submitPurchase = () => {
    purchaseForm.items = purchases.map(purchase => ({
                            product_id: purchase.id,
                            price: purchase.price,
                            qty: purchase.qty
                        }));

	purchaseForm.post('/pos',{
		replace: true,
		preserveScroll: true,
        onSuccess: async (response) => {

            if(!purchaseForm.print){
                confirmPurchaseModal.value = false; // Close the modal
                purchaseForm.clearErrors(); // Clear any validation errors
                purchaseForm.reset(); // Reset the form fields to their initial values
                purchases.length = 0;

                useToast().success('Success! Sale has been recorded successfully.', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });

                return;
            }

            try {

                const response1 = await fetch(`sales/${response.props.sales_id}`);
                if (!response1.ok) {
                    throw new Error('Network response was not ok');
                }

                const blob = await response1.blob();
                const url = window.URL.createObjectURL(blob);
                const newWindow = window.open(url, '_blank');

                if (newWindow) {
                    newWindow.onload = () => {
                        newWindow.print();
                    };
                }

                confirmPurchaseModal.value = false; // Close the modal
                purchaseForm.clearErrors(); // Clear any validation errors
                purchaseForm.reset(); // Reset the form fields to their initial values
                purchases.length = 0;

                useToast().success('Success! Sale has been recorded successfully.', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });

                router.reload({ only: ['users'] })

            } catch (error) {
                    useToast().error(`Errors! ${error.message}`, {
                        position: 'top-right',
                        duration: 3000,
                        dismissible: true
                    });
            }
		},
        onError: errors => {
            useToast().error(`Errors! ${errors.error}`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
        },

        only: ['sales_id']
	})
}
</script>

<template>
    <Head :title="title" />
    <div class="grid gap-4 md:grid-cols-5">
        <div class="col-span-5 md:col-span-3">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-3 md:p-5">
                    <div class="flex justify-between gap-2">
                        <div class="w-full dropdown">
                            <label for="simple-search" class="sr-only">Search</label>
                            <div class="relative w-full">
                                <SearchBar v-model="search" />
                                <ul tabindex="0" class="dropdown-content md:hidden z-[1] bg-base-100 shadow mt-4 w-full">
                                    <ProductDropdownItems
                                        v-for="product in products.data"
                                        :product="product"
                                        :key="product.id"
                                        @add-products="newPurchase(product)"
                                    />
                                </ul>
                            </div>

                        </div>
                        <div class="flex gap-1">
                            <PrimaryButton class="btn-circle btn-sm" @click="createProductModal = true" title="Add products">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="20" height="20" viewBox="0 0 24 24"  fill="none" stroke="currentColor"  stroke-width="2" stroke-linecap="round"  stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                            </PrimaryButton>
                            <!-- <PrimaryButton class="btn-circle btn-sm" title="Barcode scan">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="20" height="20" viewBox="0 0 24 24"  fill="none" stroke="currentColor"  stroke-width="2" stroke-linecap="round"  stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
                            </PrimaryButton> -->
                        </div>
                    </div>

                    <h1 class="font-bold mt-3 hidden md:flex">PRODUCTS:</h1>
                    <!-- products -->
                    <div class="hidden md:flex flex-wrap gap-3 pb-5 justify-start mt-4 overflow-x-auto overflow-y-auto " style="height:620px">
                        <ProductCard
                            v-for="product in products.data"
                            :product="product"
                            :key="product.id"
                            @add-products="newPurchase(product)"
                        />

                        <div class="w-full flex justify-center items-center border border-gray-100 rounded bg-base-200" v-if="products.data.length === 0">
                            <div class="text-center text-gray-400">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="250"  height="250"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package-off"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8.812 4.793l3.188 -1.793l8 4.5v8.5m-2.282 1.784l-5.718 3.216l-8 -4.5v-9l2.223 -1.25" /><path d="M14.543 10.57l5.457 -3.07" /><path d="M12 12v9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-4.35 2.447m-2.564 1.442l-1.086 .611" /><path d="M3 3l18 18" /></svg>                                <p class="text-gray-500">No products found!</p>

                            </div>
                        </div>

                    </div>
                    <div class="hidden md:flex">
                        <Pagination :links="products.links" />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-5 md:col-span-2">
            <div class="card bg-base-100 shadow-sm rounded">
                <div class="card-body p-3 md:p-5">
                    <div class="w-full flex gap-1">
                        <form class="w-full" @submit.prevent="submitPurchase" id="purchaseForm">
                            <select v-model="purchaseForm.customer_id" ref="hideDropdownRef" class="select select-bordered select-sm w-full">
                                <option selected value="">Select customer</option>
                                <option v-for="customer in customers" :value="customer.id" :key="customer.id">
                                    {{ customer.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="purchaseForm.errors.customer_id" />

                        </form>
                        <PrimaryButton @click="createCustomerModal = true"  class="btn-circle btn-sm" title="Create customer">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20" viewBox="0 0 24 24"  fill="none"  stroke="currentColor" stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        </PrimaryButton>
                        <!-- <DraftOrdersDropdown :orders="orders"/> -->

                    </div>
                    <h1 class="font-bold mt-3">ITEMS:</h1>
                    <div style="height:440px" class="mt-2 overflow-x-auto overflow-y-auto ">

                        <PurchaseCard
                            v-for="(item, index) in purchases"
                            :item="item"
                            :key="index"
                            @delete-item="deleteOrder(item.id)"
                        />

                        <div class="w-full h-full flex justify-center items-center border border-gray-100 rounded bg-base-200" v-if="purchases.length === 0">
                            <div class="text-center text-gray-400">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="250"  height="250"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-list-details"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 5h8" /><path d="M13 9h5" /><path d="M13 15h8" /><path d="M13 19h5" /><path d="M3 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M3 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /></svg>
                            </div>
                        </div>
                    </div>
                    <div class="border p-3 rounded border-gray-100 bg-base-200">
                        <div class="flex justify-between  uppercase">
                            <div>Items</div>
                            <div>{{ formatNumberWithCommas(purchaseForm.quantity) }}</div>
                        </div>
                        <div class="flex justify-between  uppercase">
                            <div>Sub-total</div>
                            <div>{{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.sub_total) }}</div>
                        </div>
                        <!-- <div class="flex justify-between  uppercase">
                            <div>
                                <button class="font-semibold text-red-500" type="button" @click="addTaxModal = true">
                                    TAX(+/-):
                                </button>
                            </div>
                            <div>
                                <span class="text-red-500">
                                {{  $page.props.auth.user.currency + " " + purchaseForm.tax }}
                                </span>
                                </div>
                        </div> -->
                        <div class="flex justify-between uppercase">
                            <div>
                                <button class="font-semibold uppercase text-red-500" type="button" @click="addDiscountModal = true">
                                    Discount(+/-):
                                </button>
                            </div>
                            <div>
                                <span class="text-red-500">
                                {{  $page.props.auth.user.currency + " " + purchaseForm.discount }}
                                </span>
                                </div>
                        </div>
                        <div class="flex justify-between uppercase">
                            <div class="text-2xl font-bold">Grand Total</div>
                            <div class="text-2xl font-bold">
                                {{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.total) }}
                            </div>
                        </div>
                    </div>

                    <div class="flex p-2 gap-3 justify-between flex-col-reverse xl:flex-row">
                        <div class="w-full text-xs">
                            <div>
                                Store: {{ $page.props.auth.user.store_name }}
                            </div>
                            <div>
                                Date: <span>{{ datetime }}</span>
                            </div>
                            <div>
                                Served by: {{ $page.props.auth.user.name }}
                            </div>
                            <div>
                                Powered by: {{ $page.props.app_name }}
                            </div>
                        </div>
                        <div class="flex gap-3 justify-center lg:justify-end">
                            <DangerButton class="btn btn-lg"
                            :disabled="purchases.length == 0"
                            @click="cancelPurchaseModal = true">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor"     stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/>
                                </svg>
                                RESET
                            </DangerButton>
                            <PrimaryButton class="btn btn-lg"
                            :disabled="purchases.length == 0" @click="reviewPurchaseModal=true">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>
                                </svg>PAY NOW
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Modal :show="createProductModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add New Product
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
                            <option disabledd value="">Select a product type</option>
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
                Add New Customer
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
    <Modal :show="addDiscountModal" @close="addDiscountModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add Discounts
            </h1>
            <div>
                <InputLabel for="name" value="Discount" />
                <NumberInput
                    type="number"
                    class="block w-full"
                    v-model="purchaseForm.discount"
                    required
                    placeholder="Enter supplier name"
                    @input="checkDiscount"
                />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="addDiscountModal = false"
                    class="ms-3"
                >
                    Close
                </SecondaryButton>
                <DangerButton @click="purchaseForm.discount = 0;"
                    class="ms-3"
                >
                    Reset
                </DangerButton>
                <PrimaryButton @click="addDiscountModal = false"
                    class="ms-3"
                >
                    Add discount
                </PrimaryButton>
            </div>
        </div>
    </Modal>
    <Modal :show="addTaxModal" @close="addTaxModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add TAX
            </h1>
            <div>
                <InputLabel for="name" value="TAX" />
                <NumberInput
                    type="number"
                    class="block w-full"
                    v-model="purchaseForm.tax"
                    required
                    placeholder="Enter supplier name"
                    @input="checkTax"
                />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="addTaxModal = false"
                    class="ms-3"
                >
                    Close
                </SecondaryButton>
                <DangerButton @click="purchaseForm.tax = 0;"
                    class="ms-3"
                >
                    Reset
                </DangerButton>
                <PrimaryButton @click="addTaxModal = false"
                    class="ms-3"
                >
                    Add tax
                </PrimaryButton>
            </div>
        </div>
    </Modal>
    <Modal :show="cancelPurchaseModal" @close="cancelPurchaseModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Cancel Purchase
            </h1>
            <p>Are you sure you want to cancel this purchase?</p>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton class="btn" @click="cancelPurchaseModal = false">
                    Close
                </SecondaryButton>
                <DangerButton :disabled="purchases.length == 0" @click="purchases.length = 0; cancelPurchaseModal = false">
                    Cancel Purchase
                </DangerButton>
            </div>
        </div>
    </Modal>
    <Modal :show="reviewPurchaseModal" @close="reviewPurchaseModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-3 font-medium">
                Review Purchase
            </h1>
            <p>Please review the items and proceed.</p>
            <div v-for="(item, index) in purchases"
                :item="item"
                :key="index" class="flex gap-3 justify-between text-sm border p-2 rounded items-end mt-1">
                <p>
                    <span class="font-bold">{{ item.product }} </span>
                    - {{  $page.props.auth.user.currency + " "+ formatNumberWithCommas(item.price) }} X {{ item.qty }} {{ item.unit }}</p>
                <p class="font-bold text-primary">{{  $page.props.auth.user.currency + " "+ formatNumberWithCommas(item.total) }}</p>

            </div>
            <div class="border p-3 mt-2 rounded border-gray-100 bg-base-200">
                <div class="flex justify-between  uppercase">
                    <div>Items</div>
                    <div>{{ purchaseForm.quantity }}</div>
                </div>
                <div class="flex justify-between  uppercase">
                    <div>Sub-total</div>
                    <div>{{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.sub_total) }}</div>
                </div>
                <!-- <div class="flex justify-between  uppercase">
                    <div>
                        <div>TAX</div>
                    </div>
                    <div>
                        <span class="text-red-500">
                        {{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.tax) }}
                        </span>
                        </div>
                </div> -->
                <div class="flex justify-between  uppercase">
                    <div>
                        <div>Discount</div>
                    </div>
                    <div>
                        <span class="text-red-500">
                        {{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.discount) }}
                        </span>
                        </div>
                </div>
                <div class="flex justify-between uppercase">
                    <div class="text-2xl font-bold">Amount DUE</div>
                    <div class="text-2xl font-bold">
                        {{  $page.props.auth.user.currency + " " + formatNumberWithCommas(purchaseForm.total) }}
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton class="btn"
                    @click="reviewPurchaseModal = false;">Cancel</SecondaryButton>
                <PrimaryButton
                    @click="reviewPurchaseModal=false;confirmPurchaseModal=true"
                    :disabled="purchases.length == 0">
                    Proceed
                </PrimaryButton>
            </div>
        </div>
    </Modal>
    <Modal :show="confirmPurchaseModal" @close="confirmPurchaseModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-3 font-medium">
                Confirm Purchase
            </h1>
            <p>Please enter payments and pay.</p>
            <select v-model="purchaseForm.customer_id" ref="hideDropdownRef" class="select select-bordered select-sm w-full my-2">
                <option selected value="">Select customer</option>
                <option v-for="customer in customers" :value="customer.id" :key="customer.id">
                    {{ customer.name }}
                </option>
            </select>
            <InputError class="mt-2" :message="purchaseForm.errors.customer_id" />
            <div class="border p-3 mt-2 rounded border-gray-100 bg-base-200">
                <div class="flex justify-between  uppercase">
                    <div>Items</div>
                    <div>{{ calculateQty }}</div>
                </div>
                <div class="flex justify-between uppercase">
                    <div class="text-2xl font-bold">Amount DUE</div>
                    <div class="text-2xl font-bold">
                        {{  $page.props.auth.user.currency + " " + formatNumberWithCommas(calculateTotal) }}
                    </div>
                </div>
            </div>
            <div class="mt-2">
                <InputLabel value="Payment Tender" />
                <input type="number" value="0" class="border w-full border-gray-300 rounded text-4xl px-2 py-4 text-right"
                v-model="purchaseForm.payment_tender" min="0" step="0.01" @input="setPaymentAmount2(purchaseForm.payment_tender)"/>
                <PaymentButtons :currency="$page.props.auth.user.currency" @set-payment="setPaymentAmount" />

                <InputLabel value="Payment Changed" />
                <input type="number" value="0" class="border w-full border-gray-300 rounded text-2xl px-2 py-2 text-right"
                v-model="purchaseForm.payment_changed" readonly>
                <InputError class="mt-2" :message="purchaseForm.errors.payment_changed" />
            </div>
            <div class="mt-2">
                <InputLabel value="Payment Method" />
                <div class="flex gap-3 flex-wrap">
                    <label class="flex items-center gap-1">
                        <input type="radio" name="payment_method" class="radio radio-sm" v-model="purchaseForm.payment_method" value="cash" checked />
                        Cash
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="payment_method" class="radio radio-sm" v-model="purchaseForm.payment_method" value="card/bank transfer" />
                        Card/Bank Transfer
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="payment_method" class="radio radio-sm" v-model="purchaseForm.payment_method" value="e-wallet"/>
                        E-wallet
                    </label>
                </div>
                <InputError class="mt-2" :message="purchaseForm.errors.payment_method" />
                <div class="mt-3" v-if="purchaseForm.payment_method != 'cash'">
                    <InputLabel value="Reference No." />
                    <TextInput
                        type="email"
                        class="block w-full"
                        v-model="purchaseForm.referrence"
                        required
                        placeholder="Enter reference number"
                    />
                </div>

                <div class="mt-3">
                    <InputLabel value="Notes (optional)" />
                    <textarea v-model="purchaseForm.notes" class="textarea w-full textarea-bordered" placeholder="Enter something regarding this purchase"></textarea>
                    <InputError class="mt-2" :message="purchaseForm.errors.notes" />
                </div>
                <div class="">
                    <label class="cursor-pointer flex gap-1 items-center">
                        <input v-model="purchaseForm.print" type="checkbox" checked="checked" class="checkbox checkbox-xs" />
                        <span class="label-text">Auto-print sales acknowledgement</span>
                    </label>
                </div>

            </div>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton class="btn" @click="confirmPurchaseModal = false; purchaseForm.payment_tender = 0; purchaseForm.payment_changed = 0;">Cancel</SecondaryButton>
                <PrimaryButton form="purchaseForm"
                :class="{ 'opacity-25': purchaseForm.processing }"
                :disabled="checkPayment(purchaseForm.payment_tender)"
                :only="['products']">
                    <span v-if="purchaseForm.processing" class="loading loading-spinner"></span>
                    Confirm
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
