<script setup>
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    product: Object,
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['addProducts','close'])

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});
const formatNumberWithCommas = (number) => {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>

<template>
    <li class="p-2 px-3 border-b cursor-pointer hover:bg-slate-100" @click="close">
        <div class="flex items-center justify-between gap-1" @click="$emit('addProducts')">
            <div class="flex items-center gap-1">
                <div class="avatar">
                    <div class="w-12 rounded-full" v-if="product.image">
                        <img :src="product.image" />
                    </div>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm ">{{ product.name + " - " + formatNumberWithCommas(product.price) }} </span>
                    <span class="text-xs text-base-content/50">{{ product.barcode }}</span>
                    <span class="text-xs text-base-content/50">{{ product.size }}</span>
                    <span class="text-green-500">{{ formatNumberWithCommas(product.stocks) + " " + product.unit }}</span>
                </div>

            </div>
        </div>
    </li>
</template>
