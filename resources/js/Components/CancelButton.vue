<script setup>
defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>
<template>
    <NavLink :href="href" class="btn btn-sm">
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12l4-4m-4 4 4 4"/>
        </svg>
        <slot/>
    </NavLink>
</template>
