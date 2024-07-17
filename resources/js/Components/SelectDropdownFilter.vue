<script setup>
import { watch } from 'vue';
import { router } from '@inertiajs/vue3'

const props = defineProps({
    title: String,
    url: String,
    options: Object,
});

const model = defineModel({
    type: String,
    required: true
})

watch(model, value => {
    const newQuery = { ...route().params, [props.title]: value };
    router.visit(props.url, {
        method: 'get',
        data: newQuery,
        preserveState: true, replace:true, preserveScroll: true,
    });
});

</script>

<template>
    <div class="w-full">
        <select v-model="model" class="select select-bordered select-sm w-full">
            <option selected value="">Filter by {{ title }}</option>
            <option v-for="data in options" :value="data.name" :key="data.id">
                {{ data.name }}
            </option>
        </select>
    </div>

</template>