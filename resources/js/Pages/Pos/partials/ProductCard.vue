<script setup>

const props = defineProps({
    product: Object,
});

const emit = defineEmits(['addProducts'])

const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<template>
    <div @click="$emit('addProducts')" class="cursor-pointer hover:bg-slate-100 card w-44 bg-base-100 shadow" style="height:250px">
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
                    {{ formatNumberWithCommas(product.stocks) }} {{ product.unit }}
                </div>
            </div>
            <div class="card-actions justify-end">
            <button class="btn btn-sm btn-primary mt-2">
                {{ $page.props.auth.user.currency }}
                {{ formatNumberWithCommas(product.price) }}</button>
            </div>
        </div>
    </div>
</template>
