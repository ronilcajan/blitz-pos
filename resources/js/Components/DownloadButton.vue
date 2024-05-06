<script setup>
import { ref } from 'vue';

defineProps({
    href: {
        type: String,
        required: true,
    }
});

const spinner = ref(false);

const handleSpinner = () => {
    spinner.value = true;
    setTimeout(() => {
        spinner.value = false;
    }, 2000);
};

</script>
<template>
  <a :href="href" class="btn btn-sm btn-outline" target="_blank" :disable="spinner" @click="handleSpinner">
        <svg v-show="!spinner" class="w-4 h-4 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V4M7 14H5a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1h-2m-1-5-4 5-4-5m9 8h.01"/>
        </svg>
        <span v-show="spinner" class="loading loading-spinner loading-sm"></span>
        <slot/>
    </a>
</template>
