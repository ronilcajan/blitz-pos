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
    <div @click="$emit('addProducts')" class="cursor-pointer hover:bg-slate-100 card w-40 bg-base-100 shadow" style="height:260px">
        <!-- <figure class="h-24"> -->
        <div class="mt-1">
            <img class="object-scale-down h-24 w-96" v-if="product.image" :src="product.image" alt="Shoes" loading="lazy" />
        </div>

        <!-- </figure> -->
        <div class="p-3">
            <h1 class="font-bold text-sm">
                {{ product.name }}
            </h1>
            <div class="flex flex-col text-xs">
                <div>{{ product.barcode }}</div>
                {{ product.size }}
                <div>
                    {{ formatNumberWithCommas(product.stocks) }} {{ product.unit }}
                </div>
            </div>
            <div class="card-actions">
            <button class="btn btn-xs btn-primary mt-2 text-xs">
                {{ $page.props.auth.user.currency }}
                {{ formatNumberWithCommas(product.price) }}</button>
            </div>
        </div>
    </div>
</template>
