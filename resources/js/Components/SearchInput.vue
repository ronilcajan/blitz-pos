<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'
import debounce from "lodash/debounce";

const props = defineProps({
    url: String,
});

const model = defineModel({
    type: String,
});

watch(model, debounce(function (value) {
    const newQuery = { ...route().params, search: value }; //maintain url params
    router.visit(props.url, {
        method: 'get',
        data: newQuery,
        preserveState: true, replace:true, preserveScroll: true
    })

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
            <input placeholder="Type here" 
            v-model="model" class="input pl-8 input-bordered input-sm w-full"                 
            id="simple-search" 
            type="search" />
        </div>
    </div>
</template>