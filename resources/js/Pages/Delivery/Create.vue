<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductDropdownItems from './partials/ProductDropdownItems.vue';
import CounterInput from './partials/CounterInput.vue';
import SearchBar from './partials/SearchBar.vue';
import { router, useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch,watchEffect, computed  } from 'vue';
import debounce from "lodash/debounce";

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    categories: Object,
    suppliers: Object,
    stores: Object,
    units: Object,
    products: Object,
    orders: Object,
    order: Object,
    purchase: Object,
    search_products: Object,
    filter: Object
});

let deliveries = reactive([]);
const barcode = ref(props.filter.barcode);
const search = ref(props.filter.search);
const discount = ref(0);
const order_id = ref('');
const createSupplierModal = ref(false);
const createProductModal = ref(false);
const addDiscountModal = ref(false);
const hideDropdownRef = ref('pending');

const supplierForm = useForm({
    name: '',
	contact_person: '',
	email: '',
	phone : '',
	address : '',
    store_id : '',
});
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
    createSupplierModal.value = false;
    supplierForm.reset();
    addDiscountModal.value = false;
    productForm.clearErrors()
    createProductModal.value = false;
    productForm.reset();
};


watch(search, debounce(function (value) {
	router.get('/deliveries/create',
	{ search: value },
	{ preserveState: true, replace:true, only: ['products'] }
   )
}, 500)) ;

watch(order_id, debounce(function (value) {
    if (!order_id.value) {
        return;
    }
	router.get('/deliveries/create',
	{ order_id: value },
	{ preserveState: true, replace:true, only: ['order','purchase'],
        onSuccess: () => {
            deliveries.length = 0;
            selectedOrder(props.order)
            deliveryForm.purchase_id = props.purchase.id;
            deliveryForm.supplier_id = props.purchase.supplier_id;
            discount.value = parseFloat(props.purchase.discount);

        }
     }
   )
}, 500)) ;

watchEffect(async () => {
    router.get('/deliveries/create',
	{ barcode: barcode.value },
	{ preserveState: true, replace:true,
        onSuccess: () => {
            if (!barcode.value) {
                return;
            }
            if (!props.search_products) {
                useToast().error('Product not found!', {
                    position: 'top-right',
                    duration: 3000,
                    dismissible: true
                });
                return;
            }
            const foundProduct = findProductById(props.search_products.id, purchase)
            if (foundProduct) {
                updateOrderQuantity(foundProduct)
            } else {
                const newOrder = createOrderFromProduct(props.search_products);
                addToDelivery(newOrder);
            }

            barcode.value = '';
        }, })
})

const newPurchase = (product) => {
    const foundProduct = findProductById(product.id, deliveries)
    if (foundProduct) {
        updateOrderQuantity(foundProduct)
    } else {
        const newOrder = createOrderFromProduct(product);
        addToDelivery(newOrder);
    }
    hideDropdownRef.value.focus()
}

const submitSupplierForm = () => {
	supplierForm.post('/suppliers',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			supplierForm.reset()
			useToast().success(`Supplier has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
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
    deliveries.splice(deliveries.findIndex(order => order.id === order_id), 1);
}

const addToDelivery = (products) => {
    deliveries.push(products);
}

const calculateSubTotal = computed(() => {
    const subTotal = deliveries.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    return formatNumberWithCommas(subTotal);
})
const calculateQty = computed(() => {
    const subTotal = deliveries.reduce((acc, order) => acc + parseFloat(order.qty), 0);
    return formatNumberWithCommas(subTotal);
})
const calculateDiscount = computed(() => {
    return formatNumberWithCommas(discount.value.toFixed(2));
})
const calculateTotal = computed(() => {
const subTotal = deliveries.reduce((acc, order) => acc + parseFloat(order.total), 0).toFixed(2);
    const discountValue = parseFloat(discount.value);
    const total = subTotal - discountValue;
    return formatNumberWithCommas(total.toFixed(2));
})

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

const getCurrentDate = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
    const dd = String(today.getDate()).padStart(2, '0');
    return `${Y}-${mm}-${dd}`;
}

const deliveryForm = useForm({
    supplier_id: '',
	transaction_date: getCurrentDate(),
    purchase_id : '',
	status: '',
	quantity : calculateQty,
    sub_total: calculateSubTotal,
	discount : calculateDiscount,
    total : calculateTotal,
    notes : '',
    store_id : '',
    items : [],
});

const submitDeliveryForm = () => {
    if(deliveries.length === 0) {
        useToast().error('Please add product to purchase!', {
            position: 'top-right',
            duration: 3000,
            dismissible: true
        });
        return;
    }
    deliveryForm.items = deliveries;
	deliveryForm.post('/deliveries',
    {
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			deliveryForm.reset()
			useToast().success(`Purchase has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
            deliveries.length = 0;
            order_id.value = '';
            discount.value = 0;
		},
	})
}

const selectedOrder = (items) =>{
    items.map(item => {
        const product = {
            id: item.id,
            product: item.name,
            details: item.size,
            qty: parseFloat(item.qty),
            unit: item.unit,
            stocks: parseFloat(item.stocks),
            price: parseFloat(item.price).toFixed(2),
            image: item.image,
            get total() {
                return (this.qty * parseFloat(this.price)).toFixed(2);
            },
        };
        addToDelivery(product);
    });
}

</script>

<template>
    <Head :title="title" />
    <form @submit.prevent="submitDeliveryForm">
    <div class="flex gap-3 flex-col md:flex-row">
        <div class="w-full">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body grow-0">
                    <div class="flex justify-between gap-2 flex-col sm:flex-row">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Delivery details</span>
                            </h2>
                        </div>
                    </div>
                    <div class="flex gap-4 flex-col md:flex-row md:w-1/2">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="date" class="label" value="Purchase Orders"/>
                                    <select v-model="order_id" class="select select-bordered w-full select-sm">
                                        <option value="">Select order</option>
                                        <option v-for="order in orders" :key="order.id" :value="order.id">
                                            {{ order.tx_no }}</option>
                                    </select>

                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="date" class="label" value="Supplier"  />
                                    <select v-model="deliveryForm.supplier_id" class="select select-bordered w-full select-sm" id="supplier" required>
                                        <option value="">Select supplier</option>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.name }}</option>
                                    </select>

                                </div>
                                <div>
                                    <button class="btn btn-square btn-outline btn-primary btn-sm" type="button" @click="createSupplierModal = true">
                                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                                        </svg>
                                 </button>
                                </div>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2">
                                <InputLabel for="date" class="label" value="Transaction Date" />
                                <TextInput v-model="deliveryForm.transaction_date" type="date" class="input input-sm w-full" required/>
                        </div>
                    </div>
                    <div class="flex justify-between gap-2 flex-col sm:flex-row mb-2 mt-4">
                        <div>
                            <h2 class="card-title grow text-sm">
                                <span class="uppercase">Delivery items</span>
                            </h2>
                        </div>
                    </div>
                    <div class="dropdown mb-5 p-3 bg-base-300 rounded">
                        <div class="flex justify-between gap-2">
                            <div class="w-full">
                                <label for="simple-search" class="sr-only">Search</label>
                                <div class="relative w-full">
                                    <SearchBar v-model="search" />
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
                    <div class="overflow-x-auto border-y-2">
                        <table class="table table-sm">
                            <thead class="uppercase">
                                <tr>
                                    <th>
                                        <div class="font-bold">#</div>
                                    </th>
                                    <th>
                                        <div class="font-bold">Products</div>
                                    </th>
                                    <th class="text-right">
                                        <div class="font-bold mr-7">Quantity</div>
                                    </th>
                                    <th class="text-left">
                                        <div class="font-bold">Price</div>
                                    </th>
                                    <th class="text-right">
                                        <div class="font-bold mr-10">Sub-total</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(purchase, index) in deliveries" :key="purchase.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>
                                        <div class="flex gap-2 grow flex-col md:flex-row">
                                            <img v-if="purchase.image" class="h-12 w-12 shrink-0 rounded-btn" width="56" height="56" :src="purchase.image" alt="Product">
                                            <div class="flex flex-col gap-1">
                                                <div class="text-sm">
                                                    {{ purchase.product }}
                                                </div>
                                                <div class="text-xs text-base-content/50" v-if="purchase.stocks > 10">
                                                    In stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                                <div class="text-xs text-red-500" v-if="purchase.stocks === 0">
                                                    Out of stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                                <div class="text-xs text-yellow-500" v-if="purchase.stocks > 1 && purchase.stocks < 10">
                                                    Low stocks: {{ purchase.stocks + ' ' + purchase.unit }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex justify-end">
                                            <CounterInput v-model="purchase.qty" @increase-by="(n) => purchase.qty += n" @decreased-by="(n) => purchase.qty > 1 ? purchase.qty -= n : 0"/>
                                        <span class="pl-5 pt-2">x</span>
                                        </div>

                                    </td>
                                    <td class="text-left">
                                        <div class="flex justify-start">
                                        <NumberInput type="number" min="1" v-model="purchase.price" class="input input-sm w-24" />
                                        <span class="ml-1 pt-2">{{ purchase.unit }}</span>
                                    </div>

                                    </td>
                                    <td class="sm:table-cell text-right">
                                        <span class="mr-3">
                                            {{ $page.props.auth.user.currency }}
                                            {{ formatNumberWithCommas(purchase.total) }}</span>
                                            <button type="button" @click="deleteOrder(purchase.id)" class="text-orange-900 hover:text-orange-600">
                                                <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M13.7535 2.47502H11.5879V1.9969C11.5879 1.15315 10.9129 0.478149 10.0691 0.478149H7.90352C7.05977 0.478149 6.38477 1.15315 6.38477 1.9969V2.47502H4.21914C3.40352 2.47502 2.72852 3.15002 2.72852 3.96565V4.8094C2.72852 5.42815 3.09414 5.9344 3.62852 6.1594L4.07852 15.4688C4.13477 16.6219 5.09102 17.5219 6.24414 17.5219H11.7004C12.8535 17.5219 13.8098 16.6219 13.866 15.4688L14.3441 6.13127C14.8785 5.90627 15.2441 5.3719 15.2441 4.78127V3.93752C15.2441 3.15002 14.5691 2.47502 13.7535 2.47502ZM7.67852 1.9969C7.67852 1.85627 7.79102 1.74377 7.93164 1.74377H10.0973C10.2379 1.74377 10.3504 1.85627 10.3504 1.9969V2.47502H7.70664V1.9969H7.67852ZM4.02227 3.96565C4.02227 3.85315 4.10664 3.74065 4.24727 3.74065H13.7535C13.866 3.74065 13.9785 3.82502 13.9785 3.96565V4.8094C13.9785 4.9219 13.8941 5.0344 13.7535 5.0344H4.24727C4.13477 5.0344 4.02227 4.95002 4.02227 4.8094V3.96565ZM11.7285 16.2563H6.27227C5.79414 16.2563 5.40039 15.8906 5.37227 15.3844L4.95039 6.2719H13.0785L12.6566 15.3844C12.6004 15.8625 12.2066 16.2563 11.7285 16.2563Z" fill=""></path>
                                                    <path d="M9.00039 9.11255C8.66289 9.11255 8.35352 9.3938 8.35352 9.75942V13.3313C8.35352 13.6688 8.63477 13.9782 9.00039 13.9782C9.33789 13.9782 9.64727 13.6969 9.64727 13.3313V9.75942C9.64727 9.3938 9.33789 9.11255 9.00039 9.11255Z" fill=""></path>
                                                    <path d="M11.2502 9.67504C10.8846 9.64692 10.6033 9.90004 10.5752 10.2657L10.4064 12.7407C10.3783 13.0782 10.6314 13.3875 10.9971 13.4157C11.0252 13.4157 11.0252 13.4157 11.0533 13.4157C11.3908 13.4157 11.6721 13.1625 11.6721 12.825L11.8408 10.35C11.8408 9.98442 11.5877 9.70317 11.2502 9.67504Z" fill=""></path>
                                                    <path d="M6.72245 9.67504C6.38495 9.70317 6.1037 10.0125 6.13182 10.35L6.3287 12.825C6.35683 13.1625 6.63808 13.4157 6.94745 13.4157C6.97558 13.4157 6.97558 13.4157 7.0037 13.4157C7.3412 13.3875 7.62245 13.0782 7.59433 12.7407L7.39745 10.2657C7.39745 9.90004 7.08808 9.64692 6.72245 9.67504Z" fill=""></path>
                                                </svg>
                                            </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-4 justify-between flex-col md:flex-row mt-5">
                        <div class="w-full md:w-1/2">
                            <InputLabel class="label" value="Notes" />
                            <textarea v-model="deliveryForm.notes" class="textarea textarea-bordered w-full max-w-xs" placeholder="Type here" ></textarea>
                        </div>
                        <div class="flex w-full md:w-1/2 justify-end">
                            <div class="bg-base-200 w-full md:w-2/3 rounded-lg p-4 px-5 shadow-sm border border-base-400">
                                <div class="flex justify-between mb-2">
                                    <span>Items:</span>
                                    <span>{{ calculateQty }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>
                                        {{ $page.props.auth.user.currency }}
                                        {{ calculateSubTotal }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <button class="font-semibold text-primary" type="button" @click="addDiscountModal = true">
                                        Discount(+/-):
                                            </button>
                                    <span class="text-red-500">
                                        {{ $page.props.auth.user.currency }}
                                        {{ calculateDiscount }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold">
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
                        <div class="join flex-wrap">
                            <input v-model="deliveryForm.status" value="pending" class="join-item btn btn-sm" type="radio" name="options" aria-label="Pending" ref="hideDropdownRef" checked/>
                            <input v-model="deliveryForm.status" value="completed" class="join-item btn btn-sm" type="radio" name="options" aria-label="Completed" />
                            <input v-model="deliveryForm.status" value="cancelled" class="join-item btn btn-sm" type="radio" name="options" aria-label="Cancelled" />
                            <input v-model="deliveryForm.status" value="partial" class="join-item btn btn-sm" type="radio" name="options" aria-label="Partial" />
                            <input v-model="deliveryForm.status" value="full" class="join-item btn btn-sm" type="radio" name="options" aria-label="Full" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 flex-col lg:flex-row">
                            <div class="flex justify-end gap-3 flex-col md:flex-row">
                                <NavLink href="/purchase" class="btn btn-sm">
                                    <svg class="w-5 h-5 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                    </svg>
                                    Cancel</NavLink>
                                <PrimaryButton type="submit"
                                    class="btn btn-sm"
                                    :class="{ 'opacity-25': deliveryForm.processing }"
                                    :disabled="deliveryForm.processing || deliveries.length==0"
                                >
                                <span v-if="deliveryForm.processing" class="loading loading-spinner"></span>
                                    Create Delivery
                                </PrimaryButton>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
    </form>
    <Modal :show="createSupplierModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new supplier
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitSupplierForm">
                <div class="flex justify-between gap-2 flex-col lg:flex-row">
                            <h2 class="card-title grow text-sm mb-5">
                                <span class="uppercase">Supplier Information</span>
                            </h2>
                        </div>
                        <div>
                            <InputLabel for="name" value="Supplier name" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="supplierForm.name"
                                required
                                placeholder="Enter supplier name"
                            />
                            <InputError class="mt-2" :message="supplierForm.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Contact person" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="supplierForm.contact_person"
                                required
                                placeholder="Enter supplier contact person"
                            />
                            <InputError class="mt-2" :message="supplierForm.errors.name" />
                        </div>

                        <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                            <div class="form-control">
                                <InputLabel for="name" value="Email address" />
                                <TextInput
                                    type="email"
                                    class="block w-full"
                                    v-model="supplierForm.email"
                                    required
                                    placeholder="Enter email address"
                                />
                                <InputError class="mt-2" :message="supplierForm.errors.email" />
                            </div>
                            <div class="form-control">
                                <InputLabel for="phone" value="Phone number" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="supplierForm.phone"

                                    placeholder="Enter phone number"
                                />
                                <InputError class="mt-2" :message="supplierForm.errors.phone" />
                            </div>
                        </div>

                        <div v-show="$page.props.auth.user.isSuperAdmin">
                            <InputLabel for="phone" value="Store" />
                            <select v-model="supplierForm.store_id" class="select select-bordered w-full">
                                <option disabled selected value="">Select a store</option>
                                <option v-for="store in stores" :value="store.id" :key="store.id">
                                    {{ store.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="supplierForm.errors.store_id" />
                        </div>


                        <div >
                            <InputLabel value="Address" />
                            <textarea v-model="supplierForm.address" class="textarea w-full textarea-bordered" placeholder="Address"></textarea>
                            <InputError class="mt-2" :message="supplierForm.errors.address" />
                        </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': supplierForm.processing }"
                        :disabled="supplierForm.processing"
                    >
                        <span v-if="supplierForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

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
    <Modal :show="addDiscountModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add discounts
            </h1>
            <div>
                <InputLabel for="name" value="Discount" />
                <NumberInput
                    type="number"
                    class="block w-full"
                    v-model="discount"
                    required
                    placeholder="Enter supplier name"
                />
            </div>
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="closeModal"
                    class="ms-3"
                >
                    Save
                </PrimaryButton>
            </div>
        </div>
    </Modal>
    <Modal :show="addDiscountModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Add discounts
            </h1>
            <div>
                <InputLabel for="name" value="Discount" />
                <NumberInput
                    type="number"
                    class="block w-full"
                    v-model="discount"
                    required
                    placeholder="Enter supplier name"
                />
            </div>
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="closeModal"
                    class="ms-3"
                >
                    Save
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
