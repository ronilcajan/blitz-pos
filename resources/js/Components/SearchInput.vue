<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'
import debounce from "lodash/debounce";

const props = defineProps({
    url: String,
});

const model = defineModel({
    type: String,
    default: '',
});

watch(model, debounce(function (value) {
	router.get(props.url,
	{ search: value },
	{ preserveState: true, replace:true, preserveScroll: true})
}, 500));

</script>

<template>
    <div>
        <label for="simple-search" class="sr-only">Search</label>
        <div class="relative sm:w-60 w-full">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                </svg>
            </div>
            <input placeholder="Type here" v-model="model" class="input pl-8 input-bordered input-sm w-full"/>
            <button type="button" v-if="model" class="absolute inset-y-0 end-0 flex items-center pe-3" @click="model = ''">
                <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                </svg>
            </button>
        </div>
    </div>
</template>
