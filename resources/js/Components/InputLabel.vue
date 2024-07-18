<script setup>
defineProps({
    value: {
        type: String,
    },
});



</script>

<template>
    <!-- <label class="label">
        <span v-if="value" class="label-text">{{ value }}</span>
        <span v-else><slot /></span>
    </label> -->
    <span class="font-semibold text-sm" v-if="value">
        {{ value }}
    </span>
    <span class="font-semibold text-sm" v-else>
            <slot />
    </span>
</template>
