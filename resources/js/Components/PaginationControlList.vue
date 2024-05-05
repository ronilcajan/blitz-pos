<script setup>

import { watch } from 'vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    url: String,
});

const model = defineModel({
    type: Number,
    required: true,
});

watch(model, value => {
	router.get(props.url,
	{ per_page: value },
	{ preserveState: true, replace:true, preserveScroll: true })
})

</script>

<template>
     <div class="flex justify-center">
        <div class="join">
            <div class="indicator">
                <button class="btn btn-sm join-item btn-base-300">Show</button>
            </div>
            <select v-model="model"  class="select select-sm max-w-xs join-item">
                <option class="text-body">10</option>
                <option class="text-body">25</option>
                <option class="text-body">50</option>
                <option class="text-body">100</option>
                <option class="text-body">All</option>
            </select>
        </div>
    </div>
</template>
