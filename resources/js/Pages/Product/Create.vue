<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { ref, watch } from 'vue';
import axios from 'axios'

defineOptions({ layout: AuthenticatedLayout })
const page = usePage()

const props = defineProps({
    title: String,
    stores: Object,
    units: Object,
    categories: Object,
    // barcode: Object,
});

const createUnitModal = ref(false);
const createCategoryModal = ref(false);
const searchBarcodeModal = ref(false);
const barcode = ref({});
const isHide = ref(false);

const form = useForm({
	name: '',
	barcode: '',
	product_category_id : '',
	product_type : 'sellable',
	unit : '',
    brand: '',
	manufacturer: '',
	size : '',
	color : '',
	dimension : '',
    expiration_date: '',
    description: '',

    base_price : 0.00,
    markup_price : 0.00,
    sale_price : 0.00,
    discount: 0,
    discount_price:  0.00,
    manual_percentage: 'manual',

    sku: '',
    min_quantity: '5',
	in_store : 0,
    in_warehouse: 0,

    image: '',
    visible : 'published',
    store_id : page?.props?.auth?.user.store_id ?? 1,
});

const unitForm = useForm({name: ''});
const categoryForm = useForm({name: '',description: ''});
const supplierForm = useForm({
	name: '',
	contact_person: '',
	email: '',
	phone : '',
	address : '',
	logo : '',
    store_id : page?.props?.auth?.user.store_id,
});

const calculateSalePrice = () => {
    const { base_price, markup_price  } = form;
    form.sale_price = parseFloat(base_price + markup_price).toFixed(2);
    calculateDiscount();
}
const calculateDiscount = () => {
    const { sale_price, discount, manual_percentage  } = form;
    const value = manual_percentage;

    if (value === 'manual') {
        const discountedPrice = parseFloat(sale_price - discount).toFixed(2);
        form.discount_price = discountedPrice;
    } else {
        const discounted = (parseFloat(sale_price) * parseFloat(discount)) / 100;
        const totalPrice = parseFloat(sale_price) - discounted;
        form.discount_price = parseFloat(totalPrice).toFixed(2);
    }
}

watch(form.sale_price, value => {
    alert(3)
})

watch(isHide, value => {
    form.product_status = value ? 'hide' : 'published';
})

isHide.value = form.product_status === 'hide';

watch(barcode, value => {
    axios.get(`https://api.upcitemdb.com/prod/trial/lookup`,{
         params: {
                upc: value
            }
        })
        .then(response => {
        // Extract product title from the response
            console.log( response.data.items[0].title); // Assuming there's only one item in the response
      })
      .catch(error => {
        console.error('Error fetching product data:', error);
      });
})

const closeModal = () => {
    unitForm.clearErrors()
    createUnitModal.value = false;
    unitForm.reset();
    categoryForm.clearErrors()
    createCategoryModal.value = false;
    categoryForm.reset();
    supplierForm.clearErrors()
    createSupplierModal.value = false;
    supplierForm.reset();
};

const submitUnitForm = () => {
	unitForm.post('/product_units',{
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
        only: ['units'],
	})
}

const submitCategoryForm = () => {
	categoryForm.post('/product_categories',{
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
        only: ['categories'],
	})
}

const submitCreateForm = () => {
	form.post('/products',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			form.reset()
			useToast().success(`Product has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}
</script>

<template>
    <Head :title="title" />
    <form @submit.prevent="submitCreateForm" class="w-full" id="createProductForm">
    <div class="flex gap-5 flex-col md:flex-row">

        <div class="w-full md:w-2/3">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex justify-between gap-2 flex-col lg:flex-row">
                        <h2 class="card-title grow text-sm mb-5">
                            <span class="uppercase">General Information</span>
                        </h2>
                        <div class="flex justify-between gap-3 flex-col md:flex-row">
                            <SecondaryButton @click="searchBarcodeModal = true" class="btn tooltip tooltip-left btn-sm flex" data-tip="Scan products using barcode">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
                            Scan Barcode
                            </SecondaryButton>
                            <NavLink href="/products" class="btn btn-sm">
                                <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                </svg>
                                Cancel</NavLink>
                            <PrimaryButton form="createProductForm"
                                class="btn btn-sm"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing" class="loading loading-spinner"></span>
                                Create Product
                            </PrimaryButton>
                        </div>

                    </div>


                    <div>
                        <InputLabel for="name" value="Product Name" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.name"
                            required
                            placeholder="Enter product name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="Barcode" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.barcode"
                                required
                                placeholder="Enter barcode"
                            />
                            <InputError class="mt-2" :message="form.errors.barcode" />

                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="name" value="Category" />
                                    <select v-model="form. product_category_id" required class="select select-bordered w-full">
                                        <option disabled selected value="">Select a product category</option>
                                        <option v-for="category in categories" :value="category.id" :key="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>

                                </div>
                                <div>
                                    <button class="btn btn-square btn-outline btn-primary" type="button" @click="createCategoryModal = true">
                                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                                        </svg>
                                 </button>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.product_category_id" />
                        </div>
                    </div>

                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2 ">
                            <div class="form-control mb-4">
                                <InputLabel for="product_type" value="Product Type" />
                                <select v-model="form.product_type" class="select select-bordered w-full">
                                    <option disabled value="">Select a product type</option>
                                    <option>
                                        sellable
                                    </option>
                                    <option>
                                        consumable
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.product_type" />
                            </div>
                        </div>
                        <div class="w-full md:w-1/2 ">

                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="name" value="Unit" />
                                    <select v-model="form.unit" class="select select-bordered w-full" required>
                                        <option disabled selected value="">Select a product unit</option>
                                        <option v-for="unit in units" :value="unit.name" :key="unit.id">
                                            {{ unit.name }}
                                        </option>
                                    </select>

                                </div>
                                <div>
                                    <button class="btn btn-square btn-outline btn-primary" type="button" @click="createUnitModal = true">
                                        <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
                                        </svg>
                                 </button>
                                </div>
                            </div>
                            <InputError class="mt-2" :message="form.errors.unit" />
                        </div>
                    </div>

                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="Brand (optional)" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.brand"
                                placeholder="Enter product brand"
                            />
                            <InputError class="mt-2" :message="form.errors.brand" />
                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="Manufacturer (optional)" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.manufacturer"
                                placeholder="Enter product manufacturer"
                            />
                            <InputError class="mt-2" :message="form.errors.manufacturer" />
                        </div>
                    </div>

                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <div class="form-control">
                                <InputLabel for="name" value="Size or Weight (optional)" />
                                <TextInput
                                    type="text"
                                    class="block w-full"
                                    v-model="form.size"
                                    placeholder="Enter size or weight"
                                />
                                <InputError class="mt-2" :message="form.errors.size" />
                            </div>
                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="Color (optional)" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.color"
                                placeholder="red,blue,white"
                            />
                            <InputError class="mt-2" :message="form.errors.color" />
                        </div>



                    </div>

                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="Dimension (optional)" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.dimension"
                                placeholder="length, width, height"
                            />
                            <InputError class="mt-2" :message="form.errors.dimension" />
                        </div>

                        <div class="w-full md:w-1/2">
                            <div class="form-control">
                                <InputLabel for="name" value="Expiration Date (optional)" />
                                <TextInput
                                    type="date"
                                    class="block w-full"
                                    v-model="form.expiration_date"
                                />
                                <InputError class="mt-2" :message="form.errors.expiration_date" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" class="textarea w-full textarea-bordered" placeholder="Enter description"></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div>
                        <div class="mb-3" v-show="$page.props.auth.user.isSuperAdmin">
                            <InputLabel for="phone" value="Store" />
                            <select v-model="form.store_id" class="select select-bordered w-full">
                                <option disabled selected value="">Select a store</option>
                                <option v-for="store in stores" :value="store.id" :key="store.id">
                                    {{ store.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.store_id" />
                        </div>
                    </div>

                </div>
            </div>

            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="card-title grow text-sm mb-2">
                            <span class="uppercase">Pricing Details</span>
                        </h2>
                    <div>
                        <div class="flex items-center">
                            <InputLabel for="name" value="Base Price" />
                            <div class="dropdown">
                                <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                    <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                    <div tabindex="0" class="card-body">
                                        <h2 class="uppercase font-bold">You needed more info?</h2>
                                        <p>This is the original price of the product before any modifications or adjustments.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <NumberInput
                            type="number"
                            class="block w-full"
                            v-model="form.base_price"
                            required
                            min="0"
                            @change="calculateSalePrice"                     placeholder="Enter base price"
                        />
                        <InputError class="mt-2" :message="form.errors.base_price" />
                    </div>
                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center">
                                <InputLabel for="name" value="Markup Price" />
                                <div class="dropdown">
                                    <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                        <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                        <div tabindex="0" class="card-body">
                                            <h2 class="uppercase font-bold">You needed more info?</h2>
                                            <p>The markup price is the additional amount added to the base price to cover costs.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <NumberInput
                                type="number"
                                class="block w-full"
                                v-model="form.markup_price"
                                required
                                min="0"
                                @change="calculateSalePrice"               placeholder="Enter markup price"
                            />
                            <InputError class="mt-2" :message="form.errors.markup_price" />

                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center">
                            <InputLabel for="name" value="Sale Price" />
                            <div class="dropdown">
                                <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                    <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                    <div tabindex="0" class="card-body">
                                        <h2 class="uppercase font-bold">You needed more info?</h2>
                                        <p>The sale price that the customer pays before any discounts are applied.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <NumberInput
                            type="number"
                            class="block w-full"
                            v-model="form.sale_price"
                            required
                            min="0"
                            @change="calculateDiscount"
                            placeholder="Enter sale price"
                        />
                        <InputError class="mt-2" :message="form.errors.sale_price" />
                        </div>
                    </div>

                    <div class="flex gap-5 flex-col md:flex-row">
                        <div class="w-full md:w-1/2">
                            <div class="flex gap-2 items-center">
                                <InputLabel for="name" value="Discount: (if applicable)" />
                                <div>
                                    <input type="radio" aria-label="manual"
                                    class="btn btn-xs" @change="calculateDiscount" value="manual"
                                    v-model="form.manual_percentage" />
                                </div>
                                <div>
                                    <input type="radio" aria-label="percent(%)"
                                    class="btn btn-xs" @change="calculateDiscount" value="percentage"
                                    v-model="form.manual_percentage" />
                                </div>
                            </div>
                            <NumberInput
                                type="number"
                                class="block w-full"
                                v-model="form.discount"
                                @change="calculateDiscount"
                                placeholder="Enter discount"
                                min="0"
                            />
                            <InputError class="mt-2" :message="form.errors.discount" />

                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="Discount Price" />
                            <TextInput
                                type="number"
                                class="block w-full"
                                v-model="form.discount_price"
                                required
                                min="0"
                            />
                            <InputError class="mt-2" :message="form.errors.discount_price" />

                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="card-title grow text-sm mb-2">
                            <span class="uppercase">Manage Stocks</span>
                        </h2>
                    <div>
                        <InputLabel for="phone" value="Stock Keeping Unit (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.sku"
                            placeholder="Enter SKU"
                        />
                        <InputError class="mt-2" :message="form.errors.sku" />
                    </div>
                    <div class="form-control mb-4">
                        <InputLabel for="name" value="Minimum Stocks (alert level)" />
                        <select v-model="form.min_quantity" class="select select-bordered w-full">
                            <option>5</option>
                            <option>10</option>
                            <option>20</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.min_quantity" />
                    </div>
                    <div class="flex gap-5 flex-col md:flex-row">

                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="In Warehouse" />
                            <NumberInput
                                type="number"
                                class="block w-full"
                                v-model="form.in_warehouse"
                                placeholder="Enter quantity"
                            />
                            <InputError class="mt-2" :message="form.errors.in_warehouse" />
                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="In Store" />
                            <NumberInput
                                type="number"

        step="0.01"
        min="0"
                                class="block w-full"
                                v-model="form.in_store"
                                placeholder="Enter quantity"
                            />
                            <InputError class="mt-2" :message="form.errors.in_store" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/3">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title grow text-sm mb-5">
                        <span class="uppercase">Product Image</span>
                    </h2>

                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="200"  height="200"  viewBox="0 0 24 24"  fill="none"  stroke="#9b9797"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-package"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /><path d="M16 5.25l-8 4.5" /></svg>
                            </div>
                        </label>
                    </div>
                    <input accept="image/*" @input="form.image = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full" />

                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                {{ form.progress.percentage }}%
                            </progress>
                            <InputError class="mt-2" :message="form.errors.image" />
                </div>
            </div>

            <div class="card bg-base-100 shadow mt-5">
                <div class="card-body">
                    <h2 class="card-title grow text-sm mb-2">
                        <span class="uppercase">Product Status</span>
                    </h2>

                    <div>
                        <InputLabel for="phone" value="Product Visibility" />
                        <div class="text-sm px-4 py-3 my-2 border w-full rounded-lg">
                                {{ form.product_status }}
                            </div>
                        <div class="form-control mt-3 flex flex-row gap-3 items-center">
                            <input type="checkbox" id="hide"  v-model="isHide" class="checkbox checkbox-sm" />
                            <label for="hide">Hide this product</label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.phone" />

                    </div>


                </div>
            </div>
        </div>

     </div>
    </form>
    <!-- <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2">
                <h2 class="card-title grow text-sm">
                    <span class="uppercase">Products Details</span>
                </h2>
                <div>
                    <button @click="searchBarcodeModal = true" class="btn btn-primary tooltip tooltip-left" data-tip="Add products using barcode">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="22"  height="22"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg></button>
                </div>
            </div>
            <form @submit.prevent="submitCreateForm">
                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Barcode" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.barcode"
                            required
                            placeholder="Enter barcode"
                        />
                        <InputError class="mt-2" :message="form.errors.barcode" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="SKU (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.sku"
                            placeholder="Enter SKU"
                        />
                        <InputError class="mt-2" :message="form.errors.sku" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Product name" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.name"
                            required
                            placeholder="Enter product name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="w-full">
                            <InputLabel for="name" value="Category" />
                            <select v-model="form.product_category_id " class="select select-bordered w-full">
                                <option disabled selected value="">Select a product category</option>
                                <option v-for="category in categories" :value="category.id" :key="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.product_category_id" />
                        </div>
                        <div>
                            <button @click="createCategoryModal = true" type="button" class="btn btn-link tooltip tooltip-left m-0" data-tip="Create new category">
                                <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </button>
                        </div>

                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="flex items-end gap-2">
                        <div class="w-full">
                            <InputLabel for="name" value="Unit" />
                            <select v-model="form.unit" class="select select-bordered w-full">
                                <option disabled selected value="">Select a product unit</option>
                                <option v-for="unit in units" :value="unit.name" :key="unit.id">
                                    {{ unit.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.unit" />

                        </div>
                        <div>
                            <button @click="createUnitModal = true" type="button" class="btn btn-link tooltip tooltip-left" data-tip="Create new unit">
                                <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Product type" />
                        <select v-model="form.product_type" class="select select-bordered w-full">
                            <option disabled value="">Select a product type</option>
                            <option>
                                sellable
                            </option>
                            <option>
                                consumable
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.phone" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Size or weight (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.size"
                            placeholder="Enter size or weight"
                        />
                        <InputError class="mt-2" :message="form.errors.size" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Dimension (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.dimension"

                            placeholder="Enter dimension (length,height,width)"
                        />
                        <InputError class="mt-2" :message="form.errors.dimension" />
                    </div>
                </div>


                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Brand (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.brand"
                            placeholder="Enter product brand"
                        />
                        <InputError class="mt-2" :message="form.errors.brand" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Manufacturer (optional)" />
                        <TextInput
                            type="text"
                            class="block w-full"
                            v-model="form.manufacturer"
                            placeholder="Enter product manufacturer"
                        />
                        <InputError class="mt-2" :message="form.errors.manufacturer" />
                    </div>
                </div>


                <div class="mb-3" v-show="$page.props.auth.user.isSuperAdmin">
                    <InputLabel for="phone" value="Store" />
                    <select v-model="form.store_id" class="select select-bordered w-full">
                        <option disabled selected value="">Select a store</option>
                        <option v-for="store in stores" :value="store.id" :key="store.id">
                            {{ store.name }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.store_id" />
                </div>

                <div class="flex justify-between gap-2 mt-10">
                    <h2 class="card-title grow text-sm">
                        <a class="link-hover link">Stocks Details</a>
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="In Store (stocks display in store)" />
                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.in_store"
                            placeholder="Enter quantity"
                        />
                        <InputError class="mt-2" :message="form.errors.in_store" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="In Warehouse" />
                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.in_warehouse"

                            placeholder="Enter quantity"
                        />
                        <InputError class="mt-2" :message="form.errors.in_warehouse" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <InputLabel for="name" value="Min. stocks (alert level)" />
                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.min_quantity"
                            placeholder="Enter quantity"
                        />
                        <InputError class="mt-2" :message="form.errors.min_quantity" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Unit Price" />
                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.unit_price"
                            @change="retailPriceCalculate"
                            placeholder="Enter price from supplier"
                        />
                        <InputError class="mt-2" :message="form.errors.unit_price" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2 mb-3 lg:grid-cols-2">
                    <div class="form-control">
                        <div class="flex gap-3">
                            <InputLabel for="name" value="Mark-up" />

                            <div class="flex items-center gap-2">
                                <input type="radio" @change="retailPriceCalculate"  id="manual" value="manual" v-model="form.manual_percentage" checked>
                                <label for="manual">manual</label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="radio" @change="retailPriceCalculate" id="percentage" value="percentage" v-model="form.manual_percentage   ">
                                <label for="percentage">percentage</label>
                            </div>
                        </div>

                        <TextInput
                            type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.mark_up_price"
                            @change="retailPriceCalculate"
                            placeholder="Enter profit margin"
                        />
                        <InputError class="mt-2" :message="form.errors.mark_up_price" />
                    </div>
                    <div class="form-control">
                        <InputLabel for="phone" value="Retail Price (unit price * mark-up price)" />
                        <TextInput
                        type="number"
                            step="0.01"
                            min="0"
                            class="block w-full"
                            v-model="form.retail_price"
                            placeholder="Price to be sold to consumer"
                        />
                        <InputError class="mt-2" :message="form.errors.retail_price" />
                    </div>
                </div>
                <div class="mb-3">
                    <InputLabel value="Product image" />
                    <input accept="image/*" @input="form.image = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="form.errors.image" />
                </div>

                <div class="mb-3">
                    <InputLabel value="Description" />
                    <textarea v-model="form.description" class="textarea w-full textarea-bordered" placeholder="Enter product description"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mt-6 flex justify-end">
                    <NavLink href="/products" class="btn">Cancel</NavLink>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>

            </form>
        </div>
    </section> -->

    <Modal maxWidth="sm" :show="searchBarcodeModal" @close="searchBarcodeModal = false">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Search Barcode
            </h1>
            <div class="flex justify-center">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="150"  height="150"  viewBox="0 0 24 24"  fill="none"  stroke="#9e9e9e"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
            </div>
            <div class="mb-3">
                <!-- <InputLabel for="name" value="Scan or enter barcode" /> -->
                <TextInput
                    type="text"
                    class="block w-full"
                    v-model="barcode"
                    required
                    placeholder="Enter barcode"
                />
                <InputError class="mt-2" :message="unitForm.errors.name" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton class="btn" @click="searchBarcodeModal = false">Close</SecondaryButton>
            </div>
        </div>
    </Modal>

    <Modal :show="createUnitModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new product unit
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitUnitForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Product unit" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="unitForm.name"
                        required
                        placeholder="product unit"
                    />
                    <InputError class="mt-2" :message="unitForm.errors.name" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': unitForm.processing }"
                        :disabled="unitForm.processing"
                    >
                        <span v-if="unitForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

    <Modal :show="createCategoryModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new category
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitCategoryForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Category name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="categoryForm.name"
                        required
                        placeholder="category name"
                    />
                    <InputError class="mt-2" :message="categoryForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Description" />
                    <textarea v-model="categoryForm.description" class="textarea w-full textarea-bordered" placeholder="Description"></textarea>
                    <InputError class="mt-2" :message="categoryForm.errors.description" />
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="btn" @click="closeModal">Cancel</SecondaryButton>
                    <PrimaryButton
                        class="ms-3"
                        :class="{ 'opacity-25': categoryForm.processing }"
                        :disabled="categoryForm.processing"
                    >
                        <span v-if="categoryForm.processing" class="loading loading-spinner"></span>
                        Create
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
