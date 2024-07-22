<script setup>

const model = defineModel({
    type: Object,
    required: true,
});
</script>

<template>
    <PrimaryButton type="submit"
        class="btn btn-sm"
        :class="{ 'opacity-25': model.processing }"
        :disabled="model.processing"
    >
        <svg class="w-4 h-4 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
        </svg>

        <slot />

    </PrimaryButton>
</template>
