<script setup>
const model = defineModel({
    type: Object,
    required: true,
});
</script>

<template>
    <SuccessButton type="submit"
        class="btn btn-sm"
        :class="{ 'opacity-25': model.processing }"
        :disabled="model.processing"
    >
        <slot />
    </SuccessButton>
</template>
