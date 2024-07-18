<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import { reactive, ref, watch, computed } from 'vue';
import axios from 'axios';
import debounce from "lodash/debounce";
import { StreamQrcodeBarcodeReader } from 'vue3-barcode-qrcode-reader'


defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    units: Object,
    categories: Object,
    product: Object
});

const createUnitModal = ref(false);
const createCategoryModal = ref(false);
const barcodeScanModel = ref(false);
const isHide = ref(props.product.visible);
const barcode = ref(props.product.barcode);
const barcodeScan = ref('');

const form = useForm({
    id: props.product.id,
	name: props.product.name,
	barcode: barcode.value,
	product_category_id : props.product.product_category_id,
	usage_type : props.product.product_type,
	unit : props.product.unit,
    brand: props.product.brand,
	size : props.product.size,
	color : props.product.color,
	dimension : props.product.dimension,
    expiration_date: formatDateForInput(props.product.expiration_date),
    description: props.product.description,
    visible : props.product.visible,
    image: [],

    base_price :props.product.price?.base_price,
    markup_price : props.product.price?.markup_price,
    sale_price : props.product.price?.sale_price,
    discount_rate: props.product.price?.discount_rate,
    discount_type: props.product.price?.discount_type,
    tax_rate: props.product.price?.tax_rate,
    tax_type: props.product.price?.tax_type,
    sku: props.product.stock?.sku,
    min_quantity: props.product.stock?.min_quantity ?? 5,
	in_store : props.product.stock?.in_store,
    in_warehouse: props.product.stock?.in_warehouse,
});

function formatDateForInput(dateString) {
    const date = new Date(dateString);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}


console.log(props.product);

const unitForm = useForm({name: ''});
const categoryForm = useForm({name: '',description: ''});

const addMarkUpPrice = () => {
    const { base_price, markup_price  } = form;
    const total = parseFloat(markup_price) + parseFloat(base_price);
    return total;
}

const calculateDicount = () => {
    if (form.discount_type === 'none') {
        form.discount_rate = 0;
        return 0;
    };

    if (form.discount_type === 'percentage') {
        return parseFloat(addMarkUpPrice())
            * parseFloat(form.discount_rate) / 100;
    }

    return form.discount_rate;

}

const calculateTaxAmount = () => {
    if (form.tax_type === 'none') {
        form.tax_rate = 0;
        return 0;
    };

    const discountedPrice = addMarkUpPrice() - calculateDicount();

    if (form.tax_type === 'percentage') {
        return discountedPrice
            * parseFloat(form.tax_rate) / 100;
    }

    return form.tax_rate;
}


const calculateSalePrice = () => {
    const markup = addMarkUpPrice();
    form.sale_price = (markup).toFixed(2);
}

const calculateSalePriceWithTax = () => {
    const markup = addMarkUpPrice();
    const discount = calculateDicount();
    const total = (markup - discount) + calculateTaxAmount();
    form.sale_price = (total).toFixed(2);
}

const calculateSalePriceWithDiscount = () => {
    const total = addMarkUpPrice() - calculateDicount();
    form.sale_price = (total).toFixed(2);
}


const changeProductVisibility = computed(() => {
    isHide.value = props.product.visible === 'hide';
})


const closeModal = () => {
    unitForm.clearErrors()
    createUnitModal.value = false;
    unitForm.reset();
    categoryForm.clearErrors()
    createCategoryModal.value = false;
    categoryForm.reset();
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

const submitUpdateForm = () => {
	form.post('/products/update',{
		replace: true,
		preserveScroll: true,
  		onSuccess: () => {
			useToast().success(`Product has been created successfully!`, {
				position: 'top-right',
				duration: 3000,
				dismissible: true
			});
		},
	})
}
const imagePreviews = ref([]);
const onFileChange = (e) => {
    const files = Array.from(e.target.files);


    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            imagePreviews.value.push(URL.createObjectURL(file));
        } else {
            alert('Please select image files only');
        }
    }
    form.image.push(e.target.files[0]);
}
const removeImage = (index) => {
    imagePreviews.value.splice(index, 1);
    form.image.splice(index, 1);
};

const generateBarcode = () => {
    const min = 100000000000; // Minimum 12-digit number
    const max = 999999999999; // Maximum 12-digit number
    barcode.value = Math.floor(min + Math.random() * (max - min + 1)).toString();
    form.barcode = barcode.value;
}
const isLoading = ref(false)
function onLoading(loading) {
  isLoading.value = loading
}

watch(isHide, value => {
    form.visible = value ? 'hide' : 'published';
})

watch(barcodeScan, debounce(function (value) {
    searchProductUsingBarcode(value);
}, 500))

watch(barcode, debounce(function (value) {
    checkBarcodeUniqueness(value);
}, 500))

const products = reactive({
    barcode: '',
    title: '',
    description: '',
    image: '',
    size: '',
});

const product_exists = ref('');
const searchProductUsingBarcode = async (barcode) => {
    try {
        const response = await axios.post(route('product.get-products'), {
            barcode
        });

        const items = response.data;
        product_exists.value = ``;
        if(items.status === 'active'){
            products.barcode = items.code;
            products.title = items.company;
            products.description = items.description;
            products.image = items.image_url;
            products.size = items.size;
        }else{
            clearProductData();
            product_exists.value = 'Product not found! Just create your product manually.'
        }

    } catch (error) {
        clearProductData();
        console.error('Error getting products data:', error);
    }
}
const clearProductData = () => {
    products.barcode = '';
    products.title = '';
    products.description = '';
    products.image = '';
    products.size = '';
};

const barcode_msg = ref('');
const checkBarcodeUniqueness = async (barcode) => {
    let isChecking = true;
    let isUnique = false;

    try {
        const response = await axios.post(route('product.check-barcode'),{
            barcode
        });

        isUnique = response.data.isUnique;

        barcode_msg.value = !isUnique ? 'This barcode already exists. Please enter another one.' : null;

    } catch (error) {
        console.error('Error checking barcode uniqueness:', error);
    } finally {
        isChecking = false;
    }
}
</script>

<template>
    <Head :title="title" />
    <form class="flex-grow" @submit.prevent="submitUpdateForm">
        <TitleContainer :title="title">
            <CancelButton href="/products" >Cancel</CancelButton>
            <SaveSubmitBtn v-model="form" >Save changes</SaveSubmitBtn>
        </TitleContainer>



        <div class="flex flex-col gap-5 md:flex-row">

            <div class="w-full md:w-2/3">
                <div class="shadow card bg-base-100">
                    <div class="card-body">
                        <!-- <div class="flex justify-end">
                            <button @click="barcodeScanModel = true" class="btn btn-xs btn-ghost btn-outline tooltip" data-tip="Scan Barcode" type="button">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="20"  height="20"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
                            </button>
                        </div> -->
                        <div class="form-control">
                            <span class="text-lg font-bold">
                                Product Title</span>
                            <TextInput
                                type="text"
                                class="block w-full mt-2"
                                v-model="form.name"
                                required
                                placeholder="Enter product title here"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                        <div class="form-control mt-6">
                            <span class="text-lg font-bold mb-3">
                                Product Description</span>
                            <textarea v-model="form.description" class="w-full textarea textarea-bordered textarea-lg p-4" placeholder="Enter description"></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div class="form-control mt-6">
                            <span class="text-lg font-bold mb-3">
                                Display images
                            </span>
                            <div>
                                <div class="flex flex-wrap gap-3 mb-3" v-if="imagePreviews.length">
                                    <div v-for="(image, index) in imagePreviews" class="relative w-24 h-24 p-3 rounded" :key="index">
                                        <img :src="image" class="w-full h-full object-cover rounded" alt="Image Preview">
                                        <button type="button"
                                            @click="removeImage(index)"
                                            class="absolute top-0 right-0 text-red-500 hover:text-red-700"
                                            >
                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="currentColor"  class="icon icon-tabler icons-tabler-filled icon-tabler-square-rounded-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 2l.324 .001l.318 .004l.616 .017l.299 .013l.579 .034l.553 .046c4.785 .464 6.732 2.411 7.196 7.196l.046 .553l.034 .579c.005 .098 .01 .198 .013 .299l.017 .616l.005 .642l-.005 .642l-.017 .616l-.013 .299l-.034 .579l-.046 .553c-.464 4.785 -2.411 6.732 -7.196 7.196l-.553 .046l-.579 .034c-.098 .005 -.198 .01 -.299 .013l-.616 .017l-.642 .005l-.642 -.005l-.616 -.017l-.299 -.013l-.579 -.034l-.553 -.046c-4.785 -.464 -6.732 -2.411 -7.196 -7.196l-.046 -.553l-.034 -.579a28.058 28.058 0 0 1 -.013 -.299l-.017 -.616c-.003 -.21 -.005 -.424 -.005 -.642l.001 -.324l.004 -.318l.017 -.616l.013 -.299l.034 -.579l.046 -.553c.464 -4.785 2.411 -6.732 7.196 -7.196l.553 -.046l.579 -.034c.098 -.005 .198 -.01 .299 -.013l.616 -.017c.21 -.003 .424 -.005 .642 -.005zm-1.489 7.14a1 1 0 0 0 -1.218 1.567l1.292 1.293l-1.292 1.293l-.083 .094a1 1 0 0 0 1.497 1.32l1.293 -1.292l1.293 1.292l.094 .083a1 1 0 0 0 1.32 -1.497l-1.292 -1.293l1.292 -1.293l.083 -.094a1 1 0 0 0 -1.497 -1.32l-1.293 1.292l-1.293 -1.292l-.094 -.083z" fill="currentColor" stroke-width="0" /></svg>
                                        </button>
                                    </div>

                                </div>
                                <div class="flex relative mb-5.5 w-full cursor-pointer appearance-none rounded border border-dashed border-gray-300 bg-gray px-4 py-4 sm:py-7.5 justify-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <span class="flex items-center justify-center w-10 h-10 bg-white border rounded-full border-stroke dark:border-strokedark dark:bg-boxdark">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1.99967 9.33337C2.36786 9.33337 2.66634 9.63185 2.66634 10V12.6667C2.66634 12.8435 2.73658 13.0131 2.8616 13.1381C2.98663 13.2631 3.1562 13.3334 3.33301 13.3334H12.6663C12.8431 13.3334 13.0127 13.2631 13.1377 13.1381C13.2628 13.0131 13.333 12.8435 13.333 12.6667V10C13.333 9.63185 13.6315 9.33337 13.9997 9.33337C14.3679 9.33337 14.6663 9.63185 14.6663 10V12.6667C14.6663 13.1971 14.4556 13.7058 14.0806 14.0809C13.7055 14.456 13.1968 14.6667 12.6663 14.6667H3.33301C2.80257 14.6667 2.29387 14.456 1.91879 14.0809C1.54372 13.7058 1.33301 13.1971 1.33301 12.6667V10C1.33301 9.63185 1.63148 9.33337 1.99967 9.33337Z" fill="#3C50E0"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5286 1.52864C7.78894 1.26829 8.21106 1.26829 8.4714 1.52864L11.8047 4.86197C12.0651 5.12232 12.0651 5.54443 11.8047 5.80478C11.5444 6.06513 11.1223 6.06513 10.8619 5.80478L8 2.94285L5.13807 5.80478C4.87772 6.06513 4.45561 6.06513 4.19526 5.80478C3.93491 5.54443 3.93491 5.12232 4.19526 4.86197L7.5286 1.52864Z" fill="#3C50E0"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99967 1.33337C8.36786 1.33337 8.66634 1.63185 8.66634 2.00004V10C8.66634 10.3682 8.36786 10.6667 7.99967 10.6667C7.63148 10.6667 7.33301 10.3682 7.33301 10V2.00004C7.33301 1.63185 7.63148 1.33337 7.99967 1.33337Z" fill="#3C50E0"></path>
                                        </svg>
                                        </span>
                                        <p class="text-sm font-medium">
                                        <span class="text-primary">Click to upload</span>
                                        or drag and drop
                                        </p>
                                        <p class="mt-1.5 text-sm font-medium">
                                        SVG, PNG, JPG or GIF
                                        </p>
                                    </div>

                                    <div>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="absolute inset-0 z-50 w-full h-full p-0 m-0 outline-none opacity-0 cursor-pointer"
                                            @change="onFileChange"
                                            multiple
                                        >

                                    </div>
                                </div>
                                <progress v-if="form.progress" :value="form.progress.percentage" class="progress" max="100">
                                        {{ form.progress.percentage }}%
                                    </progress>
                                </div>

                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>
                </div>

                <div class="mt-5 shadow card bg-base-100">
                    <div class="card-body">
                        <h2 class="mb-2 text-sm card-title grow">
                                <span class="uppercase">Pricing Details</span>
                            </h2>

                        <div class="flex flex-col gap-5 md:flex-row">
                            <div class="w-full md:w-1/2">
                                <div class="flex items-center">
                                    <span class="font-semibold text-sm">Base/Supplier Price</span>

                                    <div class="dropdown">
                                        <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                            <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-52 shadow">
                                            <div tabindex="0" class="card-body">
                                                <h2 class="font-bold uppercase text-xs">You needed more info?</h2>
                                                <p>This is the original price of the product before any modifications or adjustments.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <NumberInput
                                    class="block w-full mt-2"
                                    v-model="form.base_price"
                                    required
                                    min="0"
                                    @change="calculateSalePrice"                     placeholder="Enter base price"
                                />
                                <InputError class="mt-2" :message="form.errors.base_price" />
                            </div>
                            <div class="w-full md:w-1/2">
                                <div class="flex items-center">
                                    <span class="font-semibold text-sm">Markup Price</span>
                                    <div class="dropdown">
                                        <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                            <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                            <div tabindex="0" class="card-body">
                                                <h2 class="font-bold uppercase text-xs">You needed more info?</h2>
                                                <p>The markup price is the additional amount added to the base price to cover costs.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <NumberInput
                                    class="block w-full mt-2"
                                    v-model="form.markup_price"
                                    required
                                    min="0"
                                    @change="calculateSalePrice"               placeholder="Enter markup price"
                                />
                                <InputError class="mt-2" :message="form.errors.markup_price" />

                            </div>
                        </div>

                        <div class="flex flex-col gap-5 md:flex-row">
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">Discount Type</span>
                                <select name="discount_type" v-model="form.discount_type" class="select select-bordered w-full mt-2" @change="calculateSalePriceWithDiscount">
                                    <option value="none">none</option>
                                    <option value="flat">flat</option>
                                    <option value="percentage">percentage</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.discount_type" />

                            </div>
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">
                                    Discount Rate
                                    <span class="text-red-500" v-if="form.discount_type !== 'none'">
                                        (-{{ calculateDicount() }})
                                    </span>
                                </span>
                                <NumberInput
                                    class="block w-full mt-2"
                                    v-model="form.discount_rate"
                                    required
                                    min="0"
                                    step="0.0001"
                                    @change="calculateSalePriceWithDiscount"
                                    :readonly="form.discount_type === 'none'"
                                />
                                <InputError class="mt-2" :message="form.errors.discount_rate" />

                            </div>
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">Tax Type </span>
                                <select name="tax_type" v-model="form.tax_type" class="select select-bordered w-full mt-2" @change="calculateSalePriceWithTax">
                                    <option value="none">none</option>
                                    <option value="flat">flat</option>
                                    <option value="percentage">percentage</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.tax_type" />

                            </div>
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">
                                    Tax Rate
                                    <span class="text-primary" v-if="form.tax_type !== 'none'">
                                        (+{{ calculateTaxAmount() }})
                                    </span>
                                </span>
                                <NumberInput
                                    class="block w-full mt-2"
                                    v-model="form.tax_rate"
                                    required
                                    min="0"
                                    step="0.0001"
                                    @change="calculateSalePriceWithTax"
                                    :readonly="form.tax_type === 'none'"

                                />
                                <InputError class="mt-2" :message="form.errors.tax_rate" />

                            </div>
                        </div>

                        <div class="flex flex-col gap-5 md:flex-row">

                            <div class="w-full">
                                <div class="flex items-center">
                                    <span class="font-semibold text-sm">Sale Price</span>

                                    <div class="dropdown">
                                        <div tabindex="0" role="button" class="btn btn-circle btn-ghost btn-xs text-info">
                                            <svg tabindex="0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div tabindex="0" class="card compact dropdown-content z-[1] bg-base-100 rounded-box w-64 shadow">
                                            <div tabindex="0" class="card-body">
                                                <h2 class="font-bold uppercase text-xs">You needed more info?</h2>
                                                <p>The sale price that the customer pays.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <NumberInput
                                    type="number"
                                    class="block w-full mt-2"
                                    v-model="form.sale_price"
                                    required
                                    min="0"
                                    readonly
                                    placeholder="Enter sale price"
                                />
                                <InputError class="mt-2" :message="form.errors.sale_price" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 shadow card bg-base-100">
                    <div class="card-body">
                        <h2 class="mb-2 text-sm card-title grow">
                            <span class="text-lg font-bold">
                                Manage Inventory</span>
                        </h2>
                        <div>
                            <div class="w-full flex justify-start items-center gap-1">
                                <span class="font-semibold text-sm">Barcode</span>

                                <button @click="generateBarcode" class="btn btn-xs btn-ghost tooltip btn-outline" data-tip="Generate Barcode" type="button">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="18"  height="18"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tallymark-4"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5l0 14" /><path d="M10 5l0 14" /><path d="M14 5l0 14" /><path d="M18 5l0 14" /></svg>
                                </button>


                            </div>
                                <TextInput
                                    type="text"
                                    class="block w-full mt-2"
                                    v-model="barcode"
                                    required
                                    placeholder="Enter barcode or scan"
                                />
                                <InputError class="mt-2" :message="form.errors.barcode || barcode_msg" />
                        </div>
                        <div class="flex flex-col gap-5 md:flex-row mt-2">
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">Stock Keeping Unit (optional)</span>
                                <TextInput
                                    type="text"
                                    class="block w-full mt-2"
                                    v-model="form.sku"
                                    placeholder="Enter SKU"
                                />
                                <InputError class="mt-2" :message="form.errors.sku" />
                            </div>
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">Minimum Stocks (alert level)</span>
                                <select v-model="form.min_quantity" class="w-full select select-bordered mt-2">
                                    <option>5</option>
                                    <option>10</option>
                                    <option>20</option>
                                    <option>50</option>
                                    <option>100</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.min_quantity" />
                            </div>
                        </div>
                        <div class="flex flex-col gap-5 md:flex-row mt-2">

                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">In Warehouse</span>
                                <NumberInput
                                    class="block w-full mt-2"
                                    v-model="form.in_warehouse"
                                    placeholder="Enter quantity"
                                />
                                <InputError class="mt-2" :message="form.errors.in_warehouse" />
                            </div>
                            <div class="w-full md:w-1/2">
                                <span class="font-semibold text-sm">In Store</span>
                                <NumberInput
                                    step="0.01"
                                    min="0"
                                    class="block w-full mt-2"
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
                        <h2 class="text-sm card-title grow">
                            <span class="text-lg font-bold">
                                Organize</span>
                        </h2>
                        <div class="w-full">
                            <div class="flex flex-row items-center gap-2 mb-2">
                                <span class="font-semibold text-sm">Category</span>
                                <button as="button" class="btn btn-xs btn-primary btn-link" type="button" @click="createCategoryModal = true">Add new category</button>
                            </div>
                            <select v-model="form. product_category_id" required class="w-full select select-bordered">
                                <option disabled selected value="">Select a product category</option>
                                <option v-for="category in categories" :value="category.id" :key="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.product_category_id" />
                        </div>
                        <div class="w-full mt-4">
                            <div class="flex flex-row items-center gap-2 mb-2">
                                <span class="font-semibold text-sm">Units</span>
                                <button as="button" class="btn btn-xs btn-primary btn-link" type="button" @click="createUnitModal = true">Add new unit</button>
                            </div>
                            <select v-model="form.unit" class="w-full select select-bordered" required>
                                <option disabled selected value="">Select a product unit</option>
                                <option v-for="unit in units" :value="unit.name" :key="unit.id">
                                    {{ unit.name }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.unit" />
                        </div>

                        <div class="mt-4 form-control ">
                            <span class="font-semibold text-sm mb-2">Usage types</span>
                            <select v-model="form.usage_type" class="w-full select select-bordered">
                                <option disabled value="">Select a product type</option>
                                <option>
                                    sellable
                                </option>
                                <option>
                                    internal_use
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.usage_type" />
                        </div>

                        <div class="form-control mt-4">
                            <span class="font-semibold text-sm mb-2">Brand (optional)</span>
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.brand"
                                placeholder="Enter product brand"
                            />
                            <InputError class="mt-2" :message="form.errors.brand" />
                        </div>

                        <div class="form-control">
                            <span class="font-semibold text-sm mb-2">Expiration Date (optional)</span>
                            <TextInput
                                type="date"
                                class="block w-full"
                                v-model="form.expiration_date"
                            />
                            <InputError class="mt-2" :message="form.errors.expiration_date" />
                        </div>
                    </div>
                </div>

                <div class="shadow card bg-base-100 mt-5 ">
                    <div class="card-body grow-0 ">
                        <h2 class="text-sm card-title grow">
                            <span class="text-lg font-bold">
                                Variants</span>
                        </h2>
                        <div class="form-control mt-2">
                            <span class="font-semibold text-sm mb-2">Size or Weight (optional)</span>
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.size"
                                placeholder="Enter size or weight"
                            />
                            <InputError class="mt-2" :message="form.errors.size" />
                        </div>
                        <div class="form-control mt-2">
                            <span class="font-semibold text-sm mb-2">Color (optional)</span>
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.color"
                                placeholder="red,blue,white"
                            />
                            <InputError class="mt-2" :message="form.errors.color" />
                        </div>


                        <div class="form-control mt-2">
                            <span class="font-semibold text-sm mb-2">Dimension (optional)</span>
                            <TextInput
                                type="text"
                                class="block w-full"
                                v-model="form.dimension"
                                placeholder="length, width, height"
                            />
                            <InputError class="mt-2" :message="form.errors.dimension" />
                        </div>
                    </div>
                </div>

                <div class="mt-5 shadow card bg-base-100">
                    <div class="card-body">
                        <h2 class="mb-2 text-sm card-title grow">
                            <span class="text-lg font-bold">
                                Product Status</span>
                        </h2>
                        <div>
                            <span class="font-semibold text-sm mb-2">Product Visibility</span>
                            <div class="w-full px-4 py-3 my-2 text-sm border rounded-lg">
                                    {{ form.visible }}
                                </div>
                            <div class="flex flex-row items-center gap-3 mt-3 form-control text-sm">
                                <input type="checkbox" id="hide" :checked="changeProductVisibility" v-model="isHide" class="checkbox checkbox-sm" />
                                <label for="hide">Hide this product</label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.phone" />

                        </div>


                    </div>
                </div>
            </div>

        </div>
    </form>

    <Modal :show="createUnitModal" @close="createUnitModal = false">
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

    <Modal :show="createCategoryModal" @close="createCategoryModal = false">
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

    <Modal :show="barcodeScanModel" @close="barcodeScanModel = false" maxWidth="md">
        <div class="p-6 text-center">
            <h1 class="mb-4 text-xl font-medium">
                Search products using barcode
            </h1>
            <div class="flex justify-center border border-dashed border-primary px-4 py-4 rounded-lg">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="200"  height="200"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="1"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7v-1a2 2 0 0 1 2 -2h2" /><path d="M4 17v1a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v1" /><path d="M16 20h2a2 2 0 0 0 2 -2v-1" /><path d="M5 12l14 0" /></svg>
            </div>

            <TextInput class="w-full mt-6" v-model="barcodeScan" placeholder="Enter barcode"/>
            <InputError class="mt-2" :message="product_exists" />
            <div v-if="products.barcode" class="border border-gray-300 p-2 mt-3 cursor-pointer hover:bg-slate-50 rounded-md" >
                <div class="flex justify-start gap-2">
                    <div class="flex justify-start" v-if="products.image">
                        <img width="40" class="rounded-md" :src="products.image ?? ''" alt="logo">
                    </div>
                    <div class="flex flex-col text-left">
                        <p class="font-medium">
                            {{ products.title }}
                        </p>
                        <span class="font-medium text-sm">
                            {{ products.barcode }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <div class="flex justify-end mt-6">
                    <SecondaryButton class="btn" @click="barcodeScanModel = false">Close</SecondaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>
