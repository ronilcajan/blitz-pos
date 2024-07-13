<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { ref, watch } from 'vue';

defineOptions({ layout: AuthenticatedLayout })
const page = usePage()

const props = defineProps({
    title: String,
    stores: Object,
    units: Object,
    categories: Object,
});

const createUnitModal = ref(false);
const createCategoryModal = ref(false);
const isHide = ref('published');

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
    visible : 'published',
    image: '',

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

watch(isHide, value => {
    form.visible = value ? 'hide' : 'published';
})
const closeModal = () => {
    unitForm.clearErrors()
    createUnitModal.value = false;
    unitForm.reset();
    categoryForm.clearErrors()
    createCategoryModal.value = false;
    categoryForm.reset();
    supplierForm.clearErrors()
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
            image_preview.value = '';
		},
	})
}
const image_preview = ref([]);
const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    image_preview.value = URL.createObjectURL(file);
}
</script>

<template>
    <Head :title="title" />
    <form @submit.prevent="submitCreateForm" class="w-full">
    <div class="flex flex-col gap-5 md:flex-row">

        <div class="w-full md:w-2/3">
            <div class="shadow card bg-base-100">
                <div class="card-body">
                    <div class="flex flex-col justify-between gap-2 lg:flex-row">
                        <h2 class="mb-5 text-sm card-title grow">
                            <span class="uppercase">General Information</span>
                        </h2>
                        <div class="flex flex-col justify-end gap-3 md:flex-row">
                            <NavLink href="/products" class="btn btn-sm">
                                <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
                                </svg>
                                Cancel</NavLink>
                            <PrimaryButton type="submit"
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

                    <div class="flex flex-col gap-5 md:flex-row">
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
                        <div class="w-full md:w-1/2">
                            <div class="flex items-end gap-2">
                                <div class="w-full">
                                    <InputLabel for="name" value="Category" />
                                    <select v-model="form. product_category_id" required class="w-full select select-bordered">
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

                    <div class="flex flex-col gap-5 md:flex-row">
                        <div class="w-full md:w-1/2 ">
                            <div class="mb-4 form-control">
                                <InputLabel for="product_type" value="Product Type" />
                                <select v-model="form.product_type" class="w-full select select-bordered">
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
                                    <select v-model="form.unit" class="w-full select select-bordered" required>
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

                    <div class="flex flex-col gap-5 md:flex-row">
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

                    <div class="flex flex-col gap-5 md:flex-row">
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
                        <textarea v-model="form.description" class="w-full textarea textarea-bordered" placeholder="Enter description"></textarea>
                        <InputError class="mt-2" :message="form.errors.description" />
                    </div>
                    <div>
                        <div class="mb-3" v-show="$page.props.auth.user.isSuperAdmin">
                            <InputLabel for="phone" value="Store" />
                            <select v-model="form.store_id" class="w-full select select-bordered">
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

            <div class="mt-5 shadow card bg-base-100">
                <div class="card-body">
                    <h2 class="mb-2 text-sm card-title grow">
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
                                        <h2 class="font-bold uppercase">You needed more info?</h2>
                                        <p>This is the original price of the product before any modifications or adjustments.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <NumberInput
                            class="block w-full"
                            v-model="form.base_price"
                            required
                            min="0"
                            @change="calculateSalePrice"                     placeholder="Enter base price"
                        />
                        <InputError class="mt-2" :message="form.errors.base_price" />
                    </div>
                    <div class="flex flex-col gap-5 md:flex-row">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center">
                                <InputLabel for="name" value="Markup Price" />
                                <div class="dropdown">
                                    <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                        <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                        <div tabindex="0" class="card-body">
                                            <h2 class="font-bold uppercase">You needed more info?</h2>
                                            <p>The markup price is the additional amount added to the base price to cover costs.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <NumberInput
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
                                        <h2 class="font-bold uppercase">You needed more info?</h2>
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

                    <div class="flex flex-col gap-5 md:flex-row">
                        <div class="w-full md:w-1/2">
                            <div class="flex items-center gap-2">
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

            <div class="mt-5 shadow card bg-base-100">
                <div class="card-body">
                    <h2 class="mb-2 text-sm card-title grow">
                        <span class="uppercase">Manage Stocks</span>
                    </h2>
                    <div>
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
                    <div class="flex flex-col gap-5 md:flex-row">
                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="Stock Keeping Unit (optional)" />
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.sku"
                                placeholder="Enter SKU"
                            />
                            <InputError class="mt-2" :message="form.errors.sku" />
                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="Minimum Stocks (alert level)" />
                        <select v-model="form.min_quantity" class="w-full select select-bordered">
                            <option>5</option>
                            <option>10</option>
                            <option>20</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.min_quantity" />
                        </div>
                        </div>
                    <div class="flex flex-col gap-5 md:flex-row">

                        <div class="w-full md:w-1/2">
                            <InputLabel for="phone" value="In Warehouse" />
                            <NumberInput
                                class="block w-full"
                                v-model="form.in_warehouse"
                                placeholder="Enter quantity"
                            />
                            <InputError class="mt-2" :message="form.errors.in_warehouse" />
                        </div>
                        <div class="w-full md:w-1/2">
                            <InputLabel for="name" value="In Store" />
                            <NumberInput
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
            <div class="shadow card bg-base-100">
                <div class="card-body grow-0 ">
                    <h2 class="mb-5 text-sm card-title grow">
                        <span class="uppercase">Product Image</span>
                    </h2>
                    <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-primary bg-gray px-4 py-4 dark:bg-meta-4 sm:py-7.5 justify-center">
                        <input type="file" @input="form.image = $event.target.files[0]" accept="image/*" class="absolute inset-0 z-50 w-full h-full p-0 m-0 outline-none opacity-0 cursor-pointer" @change="onFileChange">

                        <ImagePreview v-model="image_preview" />
                    </div>
                    <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                            {{ form.progress.percentage }}%
                        </progress>
                </div>
            </div>

            <div class="mt-5 shadow card bg-base-100">
                <div class="card-body">
                    <h2 class="mb-2 text-sm card-title grow">
                        <span class="uppercase">Product Status</span>
                    </h2>

                    <div>
                        <InputLabel for="phone" value="Product Visibility" />
                        <div class="w-full px-4 py-3 my-2 text-sm border rounded-lg">
                                {{ form.visible }}
                            </div>
                        <div class="flex flex-row items-center gap-3 mt-3 form-control">
                            <input type="checkbox" id="hide" v-model="isHide" class="checkbox checkbox-sm" />
                            <label for="hide">Hide this product</label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.phone" />

                    </div>


                </div>
            </div>
        </div>

     </div>
    </form>

    <Modal :show="createUnitModal" @close="closeModal">
        <div class="p-6">
            <h1 class="mb-4 text-xl font-medium">
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
                <div class="flex justify-end mt-6">
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
            <h1 class="mb-4 text-xl font-medium">
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
                    <textarea v-model="categoryForm.description" class="w-full textarea textarea-bordered" placeholder="Description"></textarea>
                    <InputError class="mt-2" :message="categoryForm.errors.description" />
                </div>
                <div class="flex justify-end mt-6">
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
