<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="label">
        <span v-if="value" class="label-text">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
