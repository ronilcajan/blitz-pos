<script setup>
defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="text-sm leading-5 transition duration-150 ease-in-out"
    >
        <slot />
    </Link>
</template>
