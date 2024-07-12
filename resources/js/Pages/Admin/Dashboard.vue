<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout })


const props = defineProps({
    title: String
})
</script>

<template>
    <Head :title="title" />
</template>
