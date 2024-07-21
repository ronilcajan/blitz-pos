<script setup>
</script>

<template>
    <button
        class="btn btn-sm btn-primary text-sm leading-5 transition duration-150 ease-in-out">

        <svg class="w-4 h-4 " aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5"/>
        </svg>
        
        <slot />

    </button>
</template>
