<script setup>

const props = defineProps({
    filters: Array,
});
</script>

<template>
    <div class="flex gap-2 flex-wrap">
        <template v-for="(filter, index) in filters" :key="index">
            <button v-if="filter.value.value" @click="$emit('clear-filters', filter)" class="btn btn-xs btn-outline">
            Clear: {{ filter.title }}: {{ filter.value }}
            </button>
        </template>
    </div>
 
</template>