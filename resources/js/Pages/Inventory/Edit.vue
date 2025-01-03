<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { ref, watch } from 'vue';
import { values } from 'lodash';

defineOptions({ layout: AuthenticatedLayout })
const page = usePage()

const props = defineProps({  
    title: String,
    stores: Object,
    units: Object,
    categories: Object,
    suppliers: Object,
    product: Object
});

const createUnitModal = ref(false);
const createCategoryModal = ref(false);
const createSupplierModal = ref(false);

const form = useForm({
	id: props.product.product_id,
	name: props.product.name,
	barcode: props.product.barcode,
	sku : props.product.sku,
	brand : props.product.brand,
	size : props.product.size,
    brand: props.product.brand,
	dimension: props.product.dimension,
	unit : props.product.unit,
	product_type : props.product.product_type,
	manufacturer : props.product.manufacturer,
    description: props.product.description,
	image: '',
	product_category_id : props.product.product_category_id,

    product_supplier_id : props.product.id,
    unit_price : props.product.unit_price,
    mark_up_price: props.product.mark_up_price,
    retail_price: props.product.retail_price,
    min_quantity: props.product.min_quantity,
    manual_percentage: props.product.manual_percentage,
	in_store : props.product.in_store,
    in_warehouse: props.product.in_warehouse,
	supplier_id  : props.product.supplier_id,
    store_id : props.product.store_id,
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


const retailPriceCalculate = () => {
    if(form.manual_percentage === 'manual'){
        form.retail_price = parseFloat(form.unit_price) + parseFloat(form.mark_up_price) + 100;
    }else{
        const markupAmount = (form.unit_price * form.mark_up_price) / 100;
        const totalPrice = form.unit_price + markupAmount;
        form.retail_price = totalPrice.toFixed(2);
    }
}

watch(form.unit_price, values)

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
	})
}


const submitSupplierForm = () => {
	supplierForm.post('/suppliers',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			closeModal()
			useToast().success(`Supplier has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}

const submitUpdateForm = () => {
	form.post('/inventory/update',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success(`Products has been created successfully!`, {
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

    <section class="col-span-12 overflow-hidden bg-base-100 shadow-sm rounded-xl">
        <div class="card-body grow-0">
            <div class="flex justify-between gap-2">
                <h2 class="card-title grow text-sm">
                    <a class="link-hover link">Products Details</a>
                </h2>
            </div>
            <form @submit.prevent="submitUpdateForm">
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
                            placeholder="Enter sku"
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
                            <button @click="createCategoryModal = true" type="button" class="btn btn-primary tooltip  tooltip-left" data-tip="Create new category">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
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
                            <InputError class="mt-2" :message="form.errors.email" />   

                        </div>
                        <div>
                            <button @click="createUnitModal = true" type="button" class="btn btn-primary tooltip  tooltip-left" data-tip="Create new unit">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
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

                <div class="mb-3">
                    <div class="flex items-end gap-2">
                        <div class="w-full">
                            <InputLabel for="phone" value="Supplier" />
                            <select v-model="form.supplier_id" class="select select-bordered w-full">
                                <option disabled selected value="">Select a supplier</option>
                                <option v-for="supplier in suppliers" :value="supplier.id" :key="supplier.id">
                                    {{ supplier.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.supplier_id" />
                        </div>
                        <div>
                            <button @click="createSupplierModal = true" type="button" class="btn btn-primary tooltip  tooltip-left" data-tip="Create new supplier">
                                <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                            </button>
                        </div>
                     
                    </div>
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
                                <input type="radio" @change="retailPriceCalculate" id="manual" value="manual" v-model="form.manual_percentage">
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
                    <div class="relative rounded-full" v-if="props.product.image">
                        <img width="60" class="rounded-md" :src="props.product.image" alt="logo">
                    </div>
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
                    <NavLink href="/inventory" class="btn">Cancel</NavLink>
                    <SuccessButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="loading loading-spinner"></span>
                        Save changes
                    </SuccessButton>
                </div>

            </form>
        </div>
    </section>

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

    <Modal :show="createSupplierModal" @close="closeModal">
        <div class="p-6">
            <h1 class="text-xl mb-4 font-medium">
                Create new supplier
            </h1>

            <form method="dialog" class="w-full" @submit.prevent="submitSupplierForm">
                <div class="mb-3">
                    <InputLabel for="name" value="Supplier name" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="supplierForm.name"
                        required
                        placeholder="Enter name"
                    />
                    <InputError class="mt-2" :message="supplierForm.errors.name" />
                </div>
                <div class="mb-3">
                    <InputLabel for="name" value="Contact person" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="supplierForm.contact_person"
                        placeholder="Enter contact person"
                    />
                    <InputError class="mt-2" :message="supplierForm.errors.contact_person" />
                </div>
                <div class="mb-3">
                    <InputLabel for="name" value="Email address" />
                    <TextInput
                        type="email"
                        class="block w-full"
                        v-model="supplierForm.email"
                        required
                        placeholder="Enter email addres"
                    />
                    <InputError class="mt-2" :message="supplierForm.errors.email" />
                </div>
                <div class="mb-3">
                    <InputLabel for="name" value="Phone number" />
                    <TextInput
                        type="text"
                        class="block w-full"
                        v-model="supplierForm.phone"
                        placeholder="Enter phone number"
                    />
                    <InputError class="mt-2" :message="supplierForm.errors.phone" />
                </div>
                <div class="mb-3">
                    <InputLabel value="Supplier avatar" />
                    <input accept="image/*" @input="supplierForm.logo = $event.target.files[0]" type="file" class="file-input file-input-bordered file-input-sm w-full max-w-xs" />
                    <progress v-if="supplierForm.progress" :value="supplierForm.progress.percentage" class="progress" max="100">
                        {{ supplierForm.progress.percentage }}%
                    </progress>
                    <InputError class="mt-2" :message="supplierForm.errors.logo" />
                </div>

                <div class="mb-3">
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
</template>
