<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toast-notification';
import {ref} from 'vue';
defineOptions({ layout: AuthenticatedLayout })

const props = defineProps({
    title: String,
    product: Object,
    sales: Object,
    price_activity: Object
});

const page = usePage();

const url = route('products.show', props.product.id);

const product_types = [
    { id: '1', name: 'sellable', },
    { id: '2', name: 'internal_use', },

]

const formatDateForDisplay = (dateString) => {
    const date = new Date(dateString);

    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

const formatNumberWithCommas = (number) => {
    if (!number) return
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


const isVisible = (event) => {
    router.patch(route('products.change_status', props.product.id), 
        {
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
            }
        })
}
import VueBarcode from "@chenfengyuan/vue-barcode";
import html2canvas from 'html2canvas-pro';


const barCodeDiv = ref();
const downloadBarCode = () => {
      html2canvas(barCodeDiv.value).then((canvas) => {
        var barcodeImgTag = document.createElement("a");
        document.body.appendChild(barcodeImgTag);
        barcodeImgTag.download = "Barcode.jpg";
        barcodeImgTag.href = canvas.toDataURL();
        barcodeImgTag.target = "_blank";
        barcodeImgTag.click();
      });
    }
</script>

<template>

    <Head :title="title" />

    <TitleContainer :title="title">
        <button class="btn btn-sm btn-success ">
            <EditIconBtn class="flex items-center gap-1" 
            :href="`/products/${product.id}/edit`" :title="`Edit ${product.name}`">
            <span>Edit</span>
            </EditIconBtn>
        </button>
       
    </TitleContainer>


    <div class="flex-grow">


        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 sm:gap-4">
            <div class="shadow card bg-base-100 rounded-sm w-full mb-3">
                    <div class="card-body">
                        <div class="carousel w-full">
                            <img width="300" v-if="product.images.length == 0"
                                :src="$page.props?.product_image"
                                class="w-full" />

                            <template v-for="(image, index) in product.images" :key="index">
                            <div :id="`item${index}`" class="carousel-item w-full">
                                <img width="300"
                                :src="image.image"
                                class="w-full" />
                            </div>
                            </template>
                        </div>
                        <div class="flex w-full justify-center gap-2 py-2">
                            <div class="flex justify-center gap-2 flex-wrap">
                                <template v-for="(image, index) in product.images" :key="index">
                                    <a :href="`#item${index}`" class=" btn-link border border-gray-100 p-2">  
                                        <img width="50" :src="image.image"/>
                                    </a>
                                </template>
                            </div>
                           
                        </div>
                    </div>
            </div>

            <div class="shadow card bg-base-100 rounded-sm col-span-2 mb-3">
                <div class="card-body">
                    <div class="flex justify-between items-center">
                        <h2 class="text-sm card-title grow">
                            <span class="text-4xl font-bold">
                                {{ product?.name }}
                            </span>
                        </h2>

                        <input @change="isVisible($event.target)" type="checkbox"
                        class="toggle toggle-sm toggle-success tooltip tooltip-left" data-tip="Show/hide products on the site" :checked="product.visible === 'published' ? true : false" />
                    </div>
                    
                    <p class="p-0">Barcode: {{ product?.barcode }}</p>

                    <div class="flex flex-col gap-5 mt-5">
                        <p class="text-sm font-semibold">
                            Category:
                            <span class="badge badge-primary">{{ product?.category?.name }}</span>
                        </p>
                        
                        <div class="flex gap-4 items-baseline flex-wrap">
                            
                            <span class="font-semibold text-5xl text-primary">
                                {{ $page.props.auth.user.currency }} 
                                {{ formatNumberWithCommas(product?.price?.sale_price) }}
                            </span>
                            <span class="text-2xl text-gray-500 line-through" v-if="discount_type === 'percentage'">
                                {{ $page.props.auth.user.currency }} 
                                {{ formatNumberWithCommas(((product.price?.discount_rate/100) * product?.price?.sale_price).toFixed(2)) }}
                            </span>
                            <span class="font-semibold text-2xl text-orange-500" v-if="product?.price?.discount_type === 'percentage'">
                                {{ parseInt(product?.price?.discount_rate) }}% off
                            </span>
                            <span class="font-semibold text-2xl text-orange-500" v-else>
                                {{ $page.props.auth.user.currency }} 
                                {{ product?.price?.discount_rate }} Discount
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="font-semibold">
                                Displayed in store: {{ formatNumberWithCommas(product?.stock?.in_store) }} {{ product?.unit }} 
                            </span>
                            <span v-if="product?.stock?.min_quantity > product?.stock?.in_store" class="text-accent font-semibold">
                                Out of stock
                            </span>
                            <span class="text-success font-semibold" v-else>
                                In stock
                            </span>

                            <span class="font-semibold">
                                Stocks in warehouse: {{ formatNumberWithCommas(product?.stock?.in_warehouse) }} {{ product?.unit }}
                            </span>
                            <span v-if="product?.stock?.min_quantity > product?.stock?.in_warehouse" class="text-accent font-semibold">
                                Out of stock
                            </span>
                            <span class="text-success font-semibold" v-else>
                                In stock
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-semibold">Description:</span>
                            <span class="">{{ product?.description }}
                            </span>
                        </div>
                    </div>    
                </div>
            </div>

            <div class="rounded-sm mb-3">
                <div role="tablist" class="tabs tabs-lifted tabs-md justify-self-start">
                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        class="tab font-semibold text-primary text-xs"
                        aria-label="Attributes"
                        checked="checked" />
                    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                        
                        <div class="flex flex-col">
                            <p class="text-xs">
                                Size
                            </p>
                            <span class="text-xl font-semibold">{{ product?.size }}</span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Color
                            </p>
                            <span class="text-xl font-semibold">{{ product?.color }}</span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Dimension
                            </p>
                            <span class="text-xl font-semibold">{{ product?.dimension }}</span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Product Type
                            </p>
                            <span class="text-xl font-semibold">{{ product?.product_type }}</span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Brand
                            </p>
                            <span class="text-xl font-semibold">{{ product?.brand }}</span>
                        </div>
                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Expiration Date
                            </p>
                            <span class="text-xl font-semibold">{{ formatDateForDisplay(product?.expiration_date) }}</span>
                        </div>
                    
                        
                    </div>

                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        class="tab font-semibold text-primary text-xs"
                        aria-label="Pricing"
                        checked="checked" />
                    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                        
                        <div class="flex flex-col">
                            <p class="text-xs">
                                Base Price
                            </p>
                            <span class="text-xl font-semibold">
                                {{ $page.props.auth.user.currency }}
                                {{ formatNumberWithCommas(product?.price?.base_price) }}
                                </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Markup Price
                            </p>
                            <span class="text-xl font-semibold text-primary">
                                    {{ $page.props.auth.user.currency }}
                                    {{ formatNumberWithCommas(product?.price?.markup_price) }}
                            
                            </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Discount Rate
                            </p>
                            <span class="text-xl font-semibold text-red-500" v-if="product?.price?.discount_type === 'percentage'">
                                {{ parseInt(product?.price?.discount_rate) }} %
                            </span>
                            <span class="text-xl font-semibold text-red-500" v-else>
                                {{ $page.props.auth.user.currency }}
                                {{ formatNumberWithCommas(product?.price?.discount_rate) }}
                            </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Tax Rate
                            </p>
                            <span class="text-xl font-semibold text-green-500" v-if="product?.price?.tax_type === 
                            'percentage'">
                                {{ parseInt(product?.price?.tax_rate) }} %
                            </span>
                            <span class="text-xl font-semibold text-green-500" v-else>
                                {{ $page.props.auth.user.currency }}
                                {{ formatNumberWithCommas(product?.price?.tax_rate) }}
                            </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Sale Price
                            </p>
                            <span class="text-2xl font-semibold">
                                {{ $page.props.auth.user.currency }}
                                {{ formatNumberWithCommas(product?.price?.sale_price)}}
                            </span>
                        </div>
                    </div>

                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        class="tab font-semibold text-primary text-xs"
                        aria-label="Inventory"
                        checked="checked" />
                    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                        
                        <div class="flex flex-col">
                            <p class="text-xs">
                                SKU
                            </p>
                            <span class="text-xl font-semibold">
                                {{ product?.stock?.sku }}
                                </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Alert Level
                            </p>
                            <span class="text-xl font-semibold">
                                {{ formatNumberWithCommas(product?.stock?.min_quantity) }} {{ product?.unit }}
                            </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Warehouse
                            </p>
                            <span class="text-xl font-semibold">
                                {{ formatNumberWithCommas(product?.stock?.in_warehouse) }} {{ product?.unit }}
                            </span>
                        </div>

                        <div class="flex flex-col mt-3">
                            <p class="text-xs">
                                Store
                            </p>
                            <span class="text-xl font-semibold">
                                {{ formatNumberWithCommas(product?.stock?.in_store) }} {{ product?.unit }}
                            </span>
                        </div>

                    
                    </div>

                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        class="tab font-semibold text-primary text-xs"
                        aria-label="Barcode"
                        checked="checked" />
                    <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
                        
                        <div class="flex justify-start">
                            <button @click="downloadBarCode" class="btn btn-link btn-xs">Download Barcode</button>
                        </div>
                        <div ref="barCodeDiv" id="barCodeDiv" class="flex justify-start flex-col border py-3 px-4 w-52">
                            <div class="w-full flex justify-center items-center text-md font-bold">
                                {{ $page.props.auth.user.store_name }}
                                </div>
                            <div class="flex justify-between items-center text-xs">
                                <span>{{ product?.name }}</span>
                                <span>{{ formatNumberWithCommas(product.price?.sale_price) }}</span>
                            </div>
                        
                            <vue-barcode
                                ref="BarImg"
                                v-if="product.barcode"
                                tag="img"
                                :value="product.barcode"
                                :options="{ displayValue: true, lineColor: '#2B2B2C' }"
                            />
                        </div>  
                    </div>
                </div>
                </div>
            <div class="card bg-base-100 rounded-sm col-span-2 mb-3">
                <div class="card-body">
                    <div class="text-md font-semibold">
                        Sales History
                    </div>
                    <Table>
                        <template #table-header>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Invoice</TableHead>
                                    <TableHead>Qty</TableHead>
                                    <TableHead>Price({{ $page.props.auth.user.currency }})</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Customer</TableHead>
                                    <TableHead>Date</TableHead>
                                </TableRow>
                            </TableHeader>
                        </template>
                        <template #table-body>
                            <TableBody>
                                <TableRow v-for="sale in sales.data" :key="sale.id">
                                    <TableCell>{{ sale.sale.tx_no }}</TableCell>
                                    
                                    <TableCell>{{ formatNumberWithCommas(sale.quantity) }}</TableCell>
                                    <TableCell>{{ formatNumberWithCommas(sale.price) }}</TableCell>
                                    <TableCell>
                                        <div class="badge badge-sm"
                                            :class="sale.sale.status === 'complete' ? 'badge-primary' : 'badge-red-600'">
                                            {{ sale.sale.status }}</div>
                                    </TableCell>
                                    <TableCell>{{ sale.sale?.customer ?? 'Walk-in'  }}</TableCell>
                                    <TableCell>{{ formatDateForDisplay(sale.created_at) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="sales == 0">
                                    <TableCell :colspan="6" class="text-center">
                                        No sales yet for this products!
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </template>
                    </Table>
                </div>
            </div>

            <div class="card bg-base-100 rounded-sm mb-3">
                <div class="card-body">
                    <div class="text-md font-semibold">
                        Price History
                    </div>
                    <Table>
                        <template #table-header>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date </TableHead>
                                    <TableHead>Old Price({{ $page.props.auth.user.currency }}) </TableHead>
                                    <TableHead>New Price({{ $page.props.auth.user.currency }})</TableHead>
                                </TableRow>
                            </TableHeader>
                        </template>
                        <template #table-body>
                            <TableBody>
                                <template v-for="activity in price_activity.data" :key="activity.id">
                                    <TableRow v-if="activity.properties.attributes.sale_price">
                                        <TableCell>
                                            {{ formatDateForDisplay(activity.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(activity.properties.old.sale_price) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(activity.properties.attributes.sale_price) }}
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-if="price_activity == 0">
                                    <TableCell :colspan="6" class="text-center">
                                        No price history for this products!
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </template>
                    </Table>
                </div>
            </div>

            <!-- <div class="card bg-base-100 rounded-sm mb-3 col-span-2">
                <div class="card-body">
                    <div class="text-md font-semibold">
                        Stocks History
                    </div>
                    <Table>
                        <template #table-header>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date </TableHead>
                                    <TableHead>Old Price({{ $page.props.auth.user.currency }}) </TableHead>
                                    <TableHead>New Price({{ $page.props.auth.user.currency }})</TableHead>
                                </TableRow>
                            </TableHeader>
                        </template>
                        <template #table-body>
                            <TableBody>
                                <template v-for="activity in price_activity.data" :key="activity.id">
                                    <TableRow v-if="activity.properties.attributes.sale_price">
                                        <TableCell>
                                            {{ formatDateForDisplay(activity.created_at) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(activity.properties.old.sale_price) }}
                                        </TableCell>
                                        <TableCell>
                                            {{ formatNumberWithCommas(activity.properties.attributes.sale_price) }}
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-if="price_activity == 0">
                                    <TableCell :colspan="6" class="text-center">
                                        No price history for this products!
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </template>
                    </Table>
                </div>
            </div> -->
        </div>
    </div>
    
</template>
