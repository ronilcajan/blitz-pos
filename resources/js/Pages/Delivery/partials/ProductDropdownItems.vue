<template>
    <li class="p-2 px-3 border-b cursor-pointer hover:bg-slate-100" @click="close">
        <div class="flex items-center justify-between gap-1" @click="$emit('addProducts')">
            <div class="flex items-center gap-1">
                <img v-if="product.image" class="h-12 w-12 shrink-0 rounded-btn" :src="product.image" alt="Product">
                <div class="flex flex-col">
                    <span class="text-sm ">{{ product.name + " - " + product.price }} </span>
                <span class="text-sm text-base-content/50">{{ product.barcode }}</span>
                <span class="text-sm text-base-content/50">{{ product.size }}</span>
                </div>

            </div>
            <div class="flex items-center gap-1">
                <span class="text-green-500">{{ product.stocks + " " + product.unit }}</span>
            </div>
        </div>
    </li>
</template>

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

// const isOpen = ref(false);
// const handleClick = (event) => {
//       if (!this.$el.contains(event.target) && !this.$el.querySelector('.dropdown-menu').contains(event.target)) {
//         this.isOpen = false;
//       }
//     }
</script>

<style scoped>
/* Your component-specific styles go here */
</style>
