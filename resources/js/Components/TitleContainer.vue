<script setup>

const props = defineProps({
    title: String,
    required: true,
});

</script>

<template>
<div class="flex mb-2 gap-3 flex-col sm:justify-between sm:flex-row">
        <div class="grow">
            <h1 class="lg:text-2xl font-bold">
                {{ title }}
            </h1>
        </div>
        <div class="flex justify-end items-center mb-2 gap-3 flex-wrap">
            <slot />
        </div>
    </div>
</template>